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
        $objekt = $_POST['objekt']; // Das ausgewählte Objekt wird hier erfasst
        $einheit = $_POST['einheit'];
        $priority = $_POST['priority'];
        $status = $_POST['status'];
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : NULL; // Optionales Feld für Fälligkeitsdatum
        $monteur_id = !empty($_POST['monteur_id']) ? $_POST['monteur_id'] : null;

        // Validierung, um sicherzustellen, dass ein Objekt ausgewählt ist
        if ($objekt === 'none') {
            $error = "Bitte wähle ein Objekt aus.";
        } else {
            // Update SQL-Abfrage, um das ausgewählte Objekt in der Tabelle "tasks" zu speichern
            $stmt = $pdo->prepare('UPDATE tasks SET title = ?, description = ?, objekt = ?, einheit = ?, priority = ?, status = ?, due_date = ?, monteur_id = ? WHERE id = ?');
            $stmt->execute([$title, $description, $objekt, $einheit, $priority, $status, $due_date, $monteur_id, $task_id]);

            // Nach dem Speichern zur Übersicht weiterleiten
            header('Location: overview.php');
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
    <script>
        function copyToClipboard() {
            var copyText = document.getElementById("copyField");
            copyText.select();
            copyText.setSelectionRange(0, 99999); // Für mobile Geräte
            document.execCommand("copy");
            alert("Inhalt kopiert: " + copyText.value);
        }
    </script>
</head>

<body>
    <div class="container mt-4">
        <h2>Aufgabe bearbeiten</h2>

        <!-- Fehleranzeige -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formular zur Bearbeitung der Aufgabeninformationen -->
        <form method="POST">
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
                    <option value="none">Kein Objekt ausgewählt</option> <!-- Standardoption -->
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

            <!-- Speichern Button -->
            <button type="submit" class="btn btn-primary">Speichern</button>

            <!-- Lösch-Button -->
            <button type="submit" name="delete" class="btn btn-danger">Löschen</button>

            <!-- Zurück zur Übersicht Button -->
            <a href="overview.php" class="btn btn-secondary">Zurück zur Übersicht</a>
        </form>

        <!-- Copy-Paste Feld -->
        <div class="mt-4">
            <h5>Copy-Paste Feld</h5>
            <textarea class="form-control" id="copyField" rows="5">
Titel: <?php echo htmlspecialchars($task['title']); ?>


Beschreibung: <?php echo htmlspecialchars($task['description']); ?>


Einsatzort: <?php echo $einsatzort; ?>
            </textarea>
            <button class="btn btn-outline-primary mt-2" onclick="copyToClipboard()">Inhalt kopieren</button>
        </div>
    </div>
</body>

</html>
