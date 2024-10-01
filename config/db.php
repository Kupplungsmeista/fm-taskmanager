<?php
$host = 'localhost';
$dbname = 'hausverwaltung';
$username = 'hausverwaltung';  // Passe hier ggf. Benutzername und Passwort deiner MariaDB an
$password = 'hausverwaltung';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}
?>
