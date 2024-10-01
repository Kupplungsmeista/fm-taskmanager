<?php
session_start();
require '../config/db.php';

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
        $due_date = $_POST['due_date'];

        // Update SQL-Abfrage
        $stmt = $pdo->prepare('UPDATE tasks SET title = ?, description = ?, objekt = ?, einheit = ?, priority = ?, status = ?, due_date = ? WHERE id = ?');
        $stmt->execute([$title, $description, $objekt, $einheit, $priority, $status, $due_date, $task_id]);

        // Nach dem Speichern zur Übersicht weiterleiten
        header('Location: overview.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Aufgabe bearbeiten</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Aufgabe bearbeiten</h2>

        <!-- Formular zur Bearbeitung der Aufgabeninformationen -->
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Titel</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Beschreibung</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($task['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="objekt" class="form-label">Objekt</label>
                <input type="text" class="form-control" id="objekt" name="objekt" value="<?php echo htmlspecialchars($task['objekt']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="einheit" class="form-label">Einheit</label>
                <input type="text" class="form-control" id="einheit" name="einheit" value="<?php echo htmlspecialchars($task['einheit']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="priority" class="form-label">Priorität</label>
                <select class="form-control" id="priority" name="priority" required>
                    <option value="Niedrig" <?php if ($task['priority'] === 'Niedrig') echo 'selected'; ?>>Niedrig</option>
                    <option value="Mittel" <?php if ($task['priority'] === 'Mittel') echo 'selected'; ?>>Mittel</option>
                    <option value="Hoch" <?php if ($task['priority'] === 'Hoch') echo 'selected'; ?>>Hoch</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Ausstehend" <?php if ($task['status'] === 'Ausstehend') echo 'selected'; ?>>Ausstehend</option>
                    <option value="In Bearbeitung" <?php if ($task['status'] === 'In Bearbeitung') echo 'selected'; ?>>In Bearbeitung</option>
                    <option value="Erledigt" <?php if ($task['status'] === 'Erledigt') echo 'selected'; ?>>Erledigt</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="due_date" class="form-label">Fälligkeitsdatum</label>
                <input type="date" class="form-control" id="due_date" name="due_date" value="<?php echo htmlspecialchars($task['due_date']); ?>" required>
            </div>

            <!-- Speichern Button -->
            <button type="submit" class="btn btn-primary">Speichern</button>

            <!-- Lösch-Button -->
            <button type="submit" name="delete" class="btn btn-danger">Löschen</button>

            <!-- Zurück zur Übersicht Button -->
            <a href="overview.php" class="btn btn-secondary">Zurück zur Übersicht</a>
        </form>
    </div>
</body>
</html>
