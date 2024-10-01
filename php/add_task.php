<?php
session_start();
require '../config/db.php';

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Platzhalter für die spätere Implementierung

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
        <p>Diese Seite wird demnächst implementiert.</p>
        <a href="overview.php" class="btn btn-secondary">Zurück zur Übersicht</a>
    </div>
</body>
</html>
