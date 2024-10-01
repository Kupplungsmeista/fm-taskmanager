<?php
session_start();
require '../config/db.php';

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verarbeiten der Suchanfrage
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// SQL-Abfrage für die Aufgaben nach Status zählen
$stmt = $pdo->prepare('
    SELECT 
        (SELECT COUNT(*) FROM tasks WHERE status = "Ausstehend") AS ausstehend,
        (SELECT COUNT(*) FROM tasks WHERE status = "In Bearbeitung") AS in_bearbeitung,
        (SELECT COUNT(*) FROM tasks WHERE status = "Erledigt") AS erledigt
');
$stmt->execute();
$status_counts = $stmt->fetch(PDO::FETCH_ASSOC);

// SQL-Abfrage vorbereiten für die Aufgabensuche
$sql = "SELECT tasks.*, users.username as creator_name FROM tasks
        LEFT JOIN users ON tasks.created_by = users.id
        WHERE 1";

$params = [];

if ($search_query != '') {
    $sql .= " AND (tasks.title LIKE :search OR tasks.description LIKE :search OR tasks.status LIKE :search OR tasks.priority LIKE :search OR tasks.objekt LIKE :search OR tasks.einheit LIKE :search)";
    $params[':search'] = '%' . $search_query . '%';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Aufgabenübersicht</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        table tr {
            cursor: pointer;
        }
        table tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <!-- Navigationsleiste -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Hausverwaltung</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Hauptinhalt -->
    <div class="container mt-4">
        <h2>Aufgabenübersicht</h2>

        <!-- Suchfeld -->
        <form method="GET" class="d-flex mb-3">
            <input class="form-control me-2" type="search" name="search" placeholder="Suche" aria-label="Search" value="<?php echo htmlspecialchars($search_query); ?>">
            <button class="btn btn-outline-success" type="submit">Suche</button>
        </form>

        <!-- Statusübersicht -->
        <div class="alert alert-info">
            <p>Aufgabenstatus:</p>
            <ul>
                <li>Ausstehend: <strong><?php echo $status_counts['ausstehend']; ?></strong></li>
                <li>In Bearbeitung: <strong><?php echo $status_counts['in_bearbeitung']; ?></strong></li>
                <li>Erledigt: <strong><?php echo $status_counts['erledigt']; ?></strong></li>
            </ul>
        </div>

        <!-- Button zum Hinzufügen einer neuen Aufgabe -->
        <a href="add_task.php" class="btn btn-primary mb-3">Neue Aufgabe hinzufügen</a>

        <!-- Tabelle der Aufgaben -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Objekt</th>
                    <th>Einheit</th>
                    <th>Priorität</th>
                    <th>Status</th>
                    <th>Fälligkeitsdatum</th>
                    <th>Eintragungsdatum</th>
                    <th>Ersteller</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr onclick="window.location='edit_task.php?id=<?php echo $task['id']; ?>'">
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['objekt']); ?></td>
                        <td><?php echo htmlspecialchars($task['einheit']); ?></td>
                        <td><?php echo htmlspecialchars($task['priority']); ?></td>
                        <td><?php echo htmlspecialchars($task['status']); ?></td>
                        <td><?php echo date('d.m.Y', strtotime($task['due_date'])); ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($task['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($task['creator_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
