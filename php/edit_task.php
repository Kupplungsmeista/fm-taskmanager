<?php
session_start();
require '../config/db.php';

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Überprüfen, ob die Aufgaben-ID vorhanden ist
if (!isset($_GET['id'])) {
    header('Location: overview.php');
    exit();
}

$task_id = $_GET['id'];

// Laden der bestehenden Aufgabe
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    header('Location: overview.php');
    exit();
}

// Monteure abrufen
$stmt = $pdo->prepare('SELECT * FROM monteurs');
$stmt->execute();
$monteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Objekte abrufen
$stmt = $pdo->prepare('SELECT * FROM objekte');
$stmt->execute();
$objekte = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Funktion zur Ermittlung des Einsatzortes
$einsatzort = htmlspecialchars($task['objekt']);
if ($task['einheit'] !== '-') {
    $einsatzort .= ' + ' . htmlspecialchars($task['einheit']);
}

// Hochgeladene Dateien abrufen
function getFiles($pdo, $task_id) {
    $stmt = $pdo->prepare('SELECT * FROM files WHERE task_id = ?');
    $stmt->execute([$task_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$files = getFiles($pdo, $task_id);

// Formularverarbeitung beim Abschicken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Aufgabe löschen
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
        $stmt->execute([$task_id]);

        // Nach dem Löschen zur Übersicht weiterleiten
        header('Location: overview.php');
        exit();
    } elseif (isset($_POST['upload_files'])) {
        // Mehrfach-Dateiupload verarbeiten
        if (isset($_FILES['files'])) {
            $uploadDir = '../uploads/';
            $fileCount = count($_FILES['files']['name']); // Anzahl der Dateien ermitteln

            for ($i = 0; $i < $fileCount; $i++) {
                $fileName = basename($_FILES['files']['name'][$i]);
                $filePath = $uploadDir . $fileName;
                $fileError = $_FILES['files']['error'][$i];

                // Überprüfen, ob die Datei bereits existiert
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM files WHERE task_id = ? AND filename = ?');
                $stmt->execute([$task_id, $fileName]);
                $fileExists = $stmt->fetchColumn();

                // Datei nur hochladen, wenn sie noch nicht existiert
                if ($fileExists == 0 && $fileError === UPLOAD_ERR_OK) {
                    if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $filePath)) {
                        // Dateiinformationen in der Datenbank speichern
                        $stmt = $pdo->prepare('INSERT INTO files (task_id, filename, filepath, uploaded_at) VALUES (?, ?, ?, NOW())');
                        $stmt->execute([$task_id, $fileName, $filePath]);
                        $message = 'Dateien erfolgreich hochgeladen.';
                    } else {
                        $error = 'Fehler beim Verschieben der Datei: ' . $fileName;
                        break; // Hochladen stoppen, wenn ein Fehler auftritt
                    }
                } elseif ($fileExists > 0) {
                    $message = 'Die Datei ' . $fileName . ' wurde bereits hochgeladen.';
                } else {
                    $error = 'Fehler beim Hochladen der Datei: ' . $fileName . ' (Fehlercode: ' . $fileError . ')';
                    break; // Hochladen stoppen, wenn ein Fehler auftritt
                }
            }
        }
    } elseif (isset($_POST['delete_image'])) {
        // Bild löschen
        $file_id = $_POST['file_id'];
        $stmt = $pdo->prepare('SELECT filepath FROM files WHERE id = ?');
        $stmt->execute([$file_id]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($file) {
            // Datei von der Festplatte löschen
            $filePath = $file['filepath'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Dateieintrag aus der Datenbank löschen
            $stmt = $pdo->prepare('DELETE FROM files WHERE id = ?');
            $stmt->execute([$file_id]);

            // Dateien erneut abrufen
            $files = getFiles($pdo, $task_id);

            echo json_encode(['success' => true, 'files' => $files]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Bild nicht gefunden']);
            exit();
        }
    } else {
        // Aufgabe bearbeiten
        $title = $_POST['title'];
        $description = $_POST['description'];
        $objekt = $_POST['objekt'];
        $einheit = $_POST['einheit'];
        $priority = $_POST['priority'];
        $status = $_POST['status'];
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : NULL;
        $monteur_id = !empty($_POST['monteur_id']) ? $_POST['monteur_id'] : null;

        // Validierung, ob ein Objekt ausgewählt ist
        if ($objekt === 'none') {
            $error = "Bitte wähle ein Objekt aus.";
        } else {
            // Update der Aufgabe
            $stmt = $pdo->prepare('UPDATE tasks SET title = ?, description = ?, objekt = ?, einheit = ?, priority = ?, status = ?, due_date = ?, monteur_id = ? WHERE id = ?');
            $stmt->execute([$title, $description, $objekt, $einheit, $priority, $status, $due_date, $monteur_id, $task_id]);
            $message = 'Aufgabe erfolgreich gespeichert.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Aufgabe bearbeiten</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</head>

<body>
    <div class="container mt-4">
        <h2>Aufgabe bearbeiten</h2>

        <!-- Fehleranzeige -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Erfolgsanzeige -->
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Formular zur Bearbeitung der Aufgabeninformationen -->
        <form id="uploadForm" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Titel</label>
                <input type="text" class="form-control" id="title" name="title"
                    value="<?php echo htmlspecialchars($task['title']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Beschreibung</label>
                <textarea class="form-control" id="description" name="description" rows="3"
                    required><?php echo htmlspecialchars($task['description']); ?></textarea>
            </div>

            <!-- Objekt Dropdown-Menü -->
            <div class="mb-3">
                <label for="objekt" class="form-label">Objekt</label>
                <select class="form-control" id="objekt" name="objekt" required>
                    <option value="none">Kein Objekt ausgewählt</option>
                    <?php foreach ($objekte as $objekt): ?>
                        <option value="<?php echo htmlspecialchars($objekt['name']); ?>" <?php if ($task['objekt'] == $objekt['name']) {
                               echo 'selected';
                           } ?>>
                            <?php echo htmlspecialchars($objekt['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="einheit" class="form-label">Einheit</label>
                <input type="text" class="form-control" id="einheit" name="einheit"
                    value="<?php echo htmlspecialchars($task['einheit']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="priority" class="form-label">Priorität</label>
                <select class="form-control" id="priority" name="priority" required>
                    <option value="Niedrig" <?php if ($task['priority'] === 'Niedrig') {
                        echo 'selected';
                    } ?>>Niedrig</option>
                    <option value="Mittel" <?php if ($task['priority'] === 'Mittel') {
                        echo 'selected';
                    } ?>>Mittel</option>
                    <option value="Hoch" <?php if ($task['priority'] === 'Hoch') {
                        echo 'selected';
                    } ?>>Hoch</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Ausstehend" <?php if ($task['status'] === 'Ausstehend') {
                        echo 'selected';
                    } ?>>Ausstehend</option>
                    <option value="In Bearbeitung" <?php if ($task['status'] === 'In Bearbeitung') {
                        echo 'selected';
                    } ?>>In Bearbeitung</option>
                    <option value="Erledigt" <?php if ($task['status'] === 'Erledigt') {
                        echo 'selected';
                    } ?>>Erledigt</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="due_date" class="form-label">Fälligkeitsdatum</label>
                <input type="date" class="form-control" id="due_date" name="due_date"
                    value="<?php echo htmlspecialchars($task['due_date']); ?>">
            </div>

            <!-- Monteur-Auswahl -->
            <div class="mb-3">
                <label for="monteur_id" class="form-label">Monteur</label>
                <select class="form-control" id="monteur_id" name="monteur_id">
                    <option value="">Kein Monteur ausgewählt</option>
                    <?php foreach ($monteurs as $monteur): ?>
                        <option value="<?php echo $monteur['id']; ?>" <?php if ($task['monteur_id'] == $monteur['id']) {
                               echo 'selected';
                           } ?>>
                            <?php echo htmlspecialchars($monteur['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Mehrere Dateien hochladen -->
            <div class="mb-3">
                <label for="files" class="form-label">Dateien hochladen (Bilder oder Dokumente)</label>
                <input type="file" class="form-control" id="files" name="files[]" multiple
                    accept=".jpg,.jpeg,.png,.pdf,.docx,.doc,.txt">
            </div>

            <!-- Upload-Button -->
            <button type="submit" name="upload_files" class="btn btn-primary">Dateien hochladen</button>

            <!-- Speichern Button -->
            <button type="submit" class="btn btn-primary">Speichern</button>

            <!-- Lösch-Button -->
            <button type="submit" name="delete" class="btn btn-danger">Löschen</button>

            <!-- Zurück zur Übersicht Button -->
            <a href="overview.php" class="btn btn-secondary">Zurück zur Übersicht</a>
        </form>

        <!-- Galerie mit hochgeladenen Bildern -->
        <h3 class="mt-4">Galerie</h3>
        <div class="row" id="gallery">
            <?php foreach ($files as $index => $file): ?>
                <?php if (in_array(pathinfo($file['filename'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])): ?>
                    <div class="col-md-3 position-relative" id="file-<?php echo $file['id']; ?>">
                        <!-- Bild löschen -->
                        <button class="btn btn-sm btn-danger position-absolute top-0 end-0"
                            onclick="deleteImage(<?php echo $file['id']; ?>)">X</button>
                        <!-- Bild anzeigen -->
                        <img src="<?php echo $file['filepath']; ?>" class="img-fluid img-thumbnail"
                            alt="<?php echo $file['filename']; ?>" data-bs-toggle="modal" data-bs-target="#imageModal"
                            onclick="openImage('<?php echo $file['filepath']; ?>')">
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Liste der hochgeladenen Dokumente -->
        <h3 class="mt-4">Dokumente</h3>
        <ul id="document-list">
            <?php foreach ($files as $file): ?>
                <?php if (in_array(pathinfo($file['filename'], PATHINFO_EXTENSION), ['pdf', 'docx', 'doc', 'txt'])): ?>
                    <li>
                        <a href="<?php echo $file['filepath']; ?>" target="_blank"><?php echo $file['filename']; ?></a>
                        <button class="btn btn-sm btn-danger" onclick="deleteImage(<?php echo $file['id']; ?>)">Löschen</button>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>

        <!-- Modal zum Anzeigen der Bilder -->
        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Bild ansehen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="modalImage" src="" class="img-fluid" alt="Bild">
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        // JavaScript für die Lightbox-Galerie
        var images = <?php echo json_encode(array_column($files, 'filepath')); ?>;
        var currentIndex = 0;

        function openImage(filepath) {
            document.getElementById('modalImage').src = filepath;
        }

        // Bild löschen mit AJAX
        function deleteImage(fileId) {
            if (confirm('Möchtest du dieses Bild wirklich löschen?')) {
                $.ajax({
                    url: '', // Diese Datei erneut aufrufen
                    type: 'POST',
                    data: {
                        delete_image: true,
                        file_id: fileId
                    },
                    success: function (response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                            $('#file-' + fileId).remove(); // Bild oder Dokument wird entfernt
                            // Aktualisiere die Dateiliste
                            updateFiles();
                        } else {
                            alert(result.message);
                        }
                    }
                });
            }
        }

        function updateFiles() {
            // Seite aktualisieren, um Dateien neu zu laden
            location.reload();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
