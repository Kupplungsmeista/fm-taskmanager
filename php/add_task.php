<?php
session_start();
require '../config/db.php';

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Benutzerinformationen abrufen
$user_id = $_SESSION['user_id'];

// Monteure aus der Datenbank abrufen
$stmt = $pdo->prepare('SELECT * FROM monteurs');
$stmt->execute();
$monteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Objekte aus der Datenbank abrufen
$stmt = $pdo->prepare('SELECT * FROM objekte');
$stmt->execute();
$objekte = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formularverarbeitung beim Abschicken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $objekt = $_POST['objekt'];
    $einheit = $_POST['einheit'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : '-'; // Wenn kein Datum, dann "-"
    $monteur_id = !empty($_POST['monteur_id']) ? $_POST['monteur_id'] : NULL; // Optionales Feld für Monteur

    // Validierung, um sicherzustellen, dass ein Objekt ausgewählt ist
    if ($objekt === 'none') {
        $error = "Bitte wähle ein Objekt aus.";
    } else {
        // Aktuelles Datum für das Eintragungsdatum
        $created_at = date('Y-m-d H:i:s');

        // Einfügen der Aufgabe in die Datenbank
        $stmt = $pdo->prepare('INSERT INTO tasks (title, description, objekt, einheit, priority, status, due_date, created_at, created_by, monteur_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $objekt, $einheit, $priority, $status, $due_date, $created_at, $user_id, $monteur_id]);

        // Task-ID erhalten, um Dateien zu speichern
        $task_id = $pdo->lastInsertId();

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
        }

        // Weiterleitung zur Übersicht nach dem Speichern
        header('Location: overview.php');
        exit();
    }
}

// Hochgeladene Dateien abrufen, wenn sie existieren
$uploaded_files = [];
$stmt = $pdo->prepare('SELECT * FROM files');
$stmt->execute();
$uploaded_files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Neue Aufgabe hinzufügen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <!-- Bootstrap Card hinzugefügt -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Neue Aufgabe hinzufügen</h2>
            </div>
            <div class="card-body">
                <!-- Fehleranzeige -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Formular zur Eingabe der Aufgabeninformationen -->
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Titel</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Beschreibung</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>

                    <!-- Objekt Dropdown-Menü -->
                    <div class="mb-3">
                        <label for="objekt" class="form-label">Objekt</label>
                        <select class="form-control" id="objekt" name="objekt" required>
                            <option value="none">Kein Objekt ausgewählt</option> <!-- Standardoption -->
                            <?php foreach ($objekte as $objekt): ?>
                                <option value="<?php echo htmlspecialchars($objekt['name']); ?>"><?php echo htmlspecialchars($objekt['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="einheit" class="form-label">Einheit</label>
                        <input type="text" class="form-control" id="einheit" name="einheit" required>
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label">Priorität</label>
                        <select class="form-control" id="priority" name="priority" required>
                            <option value="Niedrig">Niedrig</option>
                            <option value="Mittel">Mittel</option>
                            <option value="Hoch">Hoch</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="Ausstehend">Ausstehend</option>
                            <option value="In Bearbeitung">In Bearbeitung</option>
                            <option value="Erledigt">Erledigt</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="due_date" class="form-label">Fälligkeitsdatum</label>
                        <input type="date" class="form-control" id="due_date" name="due_date">
                    </div>

                    <!-- Monteur-Auswahl -->
                    <div class="mb-3">
                        <label for="monteur_id" class="form-label">Monteur</label>
                        <select class="form-control" id="monteur_id" name="monteur_id">
                            <option value="">Kein Monteur ausgewählt</option>
                            <?php foreach ($monteurs as $monteur): ?>
                                <option value="<?php echo $monteur['id']; ?>"><?php echo htmlspecialchars($monteur['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dateiupload -->
                    <div class="mb-3">
                        <label for="file" class="form-label">Dateien hochladen</label>
                        <input type="file" class="form-control" id="file" name="files[]" multiple>
                    </div>

                    <!-- Speichern Button -->
                    <button type="submit" class="btn btn-success">Speichern</button>
                    
                    <!-- Neuer Button "Dateien Hochladen" -->
                    <button type="submit" name="upload_files" class="btn btn-primary">Dateien Hochladen</button>

                    <!-- Zurück zur Übersicht Button -->
                    <a href="overview.php" class="btn btn-secondary">Zurück zur Übersicht</a>
                </form>
            </div>
        </div>

        <!-- Übersicht über hochgeladene Dateien -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Hochgeladene Dateien</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($uploaded_files)): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Dateiname</th>
                                <th>Pfad</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($uploaded_files as $file): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($file['filename']); ?></td>
                                    <td><a href="<?php echo htmlspecialchars($file['filepath']); ?>" target="_blank">Datei anzeigen</a></td>
                                    <td>
                                        <!-- Hier können weitere Aktionen hinzugefügt werden, wie z.B. Datei löschen -->
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
</body>
</html>
