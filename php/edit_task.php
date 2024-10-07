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

// Dateien zu dieser Aufgabe abrufen
$stmt = $pdo->prepare('SELECT * FROM files WHERE task_id = ?');
$stmt->execute([$task_id]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dateien in Bilder und Dokumente trennen
$images = [];
$documents = [];
foreach ($files as $file) {
    $extension = pathinfo($file['filename'], PATHINFO_EXTENSION);
    if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
        $images[] = $file; // Bilddateien
    } else {
        $documents[] = $file; // Dokumentdateien
    }
}

// Formularverarbeitung beim Abschicken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Aufgabe löschen
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
        $stmt->execute([$task_id]);

        // Nach dem Löschen zur Übersicht weiterleiten
        header('Location: overview.php');
        exit();
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

        // Dateiupload verarbeiten
        if (!empty($_FILES['files']['name'][0])) {
            $upload_dir = '../uploads/'; // Verzeichnis, in dem die Dateien gespeichert werden
            foreach ($_FILES['files']['name'] as $key => $name) {
                $file_tmp = $_FILES['files']['tmp_name'][$key];
                $file_name = time() . '_' . $name; // Dateiname mit Timestamp
                $file_path = $upload_dir . $file_name; // Pfad zur Datei
                move_uploaded_file($file_tmp, $file_path);

                // Datei in der Datenbank speichern (Nutzung der Tabelle 'files')
                $stmt = $pdo->prepare('INSERT INTO files (task_id, filename, filepath, uploaded_at) VALUES (?, ?, ?, NOW())');
                $stmt->execute([$task_id, $file_name, $file_path]);
            }

            // Nach dem Dateiupload Seite aktualisieren
            echo "<script>window.location.href = window.location.href;</script>";
            exit();
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
    <style>
        .full-image {
            width: 100%;
            height: auto;
        }
        .thumbnail {
            cursor: pointer;
        }
        .large-image {
            width: 80%;
            height: auto;
            margin: 0 auto;
            display: block;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <!-- Bootstrap Card hinzugefügt -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Aufgabe bearbeiten</h2>
            </div>
            <div class="card-body">
                <!-- Fehleranzeige -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Erfolgsanzeige -->
                <?php if (isset($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>

                <!-- Formular zur Bearbeitung der Aufgabeninformationen -->
                <form method="POST" enctype="multipart/form-data">
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

                    <!-- Dateiupload -->
                    <div class="mb-3">
                        <label for="file" class="form-label">Dateien hochladen</label>
                        <input type="file" class="form-control" id="file" name="files[]" multiple>
                    </div>

                    <!-- Speichern Button -->
                    <button type="submit" class="btn btn-primary">Speichern</button>

                    <!-- Lösch-Button -->
                    <button type="submit" name="delete" class="btn btn-danger">Löschen</button>

                    <!-- Neuer Button "Dateien Hochladen" -->
                    <button type="submit" name="upload_files" class="btn btn-primary">Dateien Hochladen</button>

                    <!-- Zurück zur Übersicht Button -->
                    <a href="overview.php" class="btn btn-secondary">Zurück zur Übersicht</a>
                </form>
            </div>
        </div>

        <!-- Galerie für Bilder -->
        <?php if (!empty($images)): ?>
            <div class="mt-4">
                <h3>Galerie</h3>
                <div class="row">
                    <?php foreach ($images as $image): ?>
                        <div class="col-md-3">
                            <img src="<?php echo $image['filepath']; ?>" class="img-fluid img-thumbnail mb-3 thumbnail"
                                 alt="<?php echo $image['filename']; ?>"
                                 onclick="enlargeImage('<?php echo $image['filepath']; ?>')">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Liste der Dokumente -->
        <?php if (!empty($documents)): ?>
            <div class="mt-4">
                <h3>Dokumente</h3>
                <ul class="list-group">
                    <?php foreach ($documents as $document): ?>
                        <li class="list-group-item">
                            <a href="<?php echo $document['filepath']; ?>" target="_blank"><?php echo $document['filename']; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Übersicht über hochgeladene Dateien -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Hochgeladene Dateien</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($files)): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Dateiname</th>
                                <th>Pfad</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($file['filename']); ?></td>
                                    <td><a href="<?php echo htmlspecialchars($file['filepath']); ?>" target="_blank">Datei anzeigen</a></td>
                                    <td>
                                        <!-- Weitere Aktionen wie Datei löschen könnten hier hinzugefügt werden -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Keine Dateien hochgeladen.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal für Bilderanzeige -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Bild anzeigen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="modalImage" src="" class="img-fluid" alt="Bild">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Funktion zum Vergrößern des Bildes in einem Modal
        function enlargeImage(imageSrc) {
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageSrc;
            modalImage.style.width = "auto"; // Originalgröße
            modalImage.style.height = "auto"; // Originalgröße
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            imageModal.show();
        }
    </script>
</body>

</html>
