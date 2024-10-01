<?php
session_start();
require '../config/db.php';

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Benutzerinformationen abrufen
$user_id = $_SESSION['user_id'];

// Formularverarbeitung beim Abschicken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $objekt = $_POST['objekt'];
    $einheit = $_POST['einheit'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $due_date = $_POST['due_date'];
    
    // Aktuelles Datum für das Eintragungsdatum
    $created_at = date('Y-m-d H:i:s');
    
    // Einfügen der Aufgabe in die Datenbank
    $stmt = $pdo->prepare('INSERT INTO tasks (title, description, objekt, einheit, priority, status, due_date, created_at, created_by) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$title, $description, $objekt, $einheit, $priority, $status, $due_date, $created_at, $user_id]);

    // Weiterleitung zur Übersicht nach dem Speichern
    header('Location: overview.php');
    exit();
}
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
        <h2>Neue Aufgabe hinzufügen</h2>

        <!-- Formular zur Eingabe der Aufgabeninformationen -->
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Titel</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Beschreibung</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label for="objekt" class="form-label">Objekt</label>
                <input type="text" class="form-control" id="objekt" name="objekt" required>
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
                <input type="date" class="form-control" id="due_date" name="due_date" required>
            </div>

            <!-- Speichern Button -->
            <button type="submit" class="btn btn-primary">Speichern</button>
            
            <!-- Zurück zur Übersicht Button -->
            <a href="overview.php" class="btn btn-secondary">Zurück zur Übersicht</a>
        </form>
    </div>
</body>
</html>
