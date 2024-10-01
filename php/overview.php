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

// Sortierung festlegen
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'status';
$order_dir = isset($_GET['order_dir']) && $_GET['order_dir'] === 'desc' ? 'desc' : 'asc';

// SQL-Abfrage für die Statusübersicht (Anzahl der Aufgaben nach Status)
$status_stmt = $pdo->prepare('
    SELECT 
        (SELECT COUNT(*) FROM tasks WHERE status = "Ausstehend") AS ausstehend,
        (SELECT COUNT(*) FROM tasks WHERE status = "In Bearbeitung") AS in_bearbeitung,
        (SELECT COUNT(*) FROM tasks WHERE status = "Erledigt") AS erledigt
');
$status_stmt->execute();
$status_counts = $status_stmt->fetch(PDO::FETCH_ASSOC);

// SQL-Abfrage für die Aufgaben mit benutzerdefinierter Sortierung nach Status
$sql = "SELECT tasks.*, users.username as creator_name, monteurs.name as monteur_name FROM tasks
        LEFT JOIN users ON tasks.created_by = users.id
        LEFT JOIN monteurs ON tasks.monteur_id = monteurs.id
        WHERE 1";

// Suchfunktion hinzufügen
$params = [];
if ($search_query != '') {
    $sql .= " AND (tasks.title LIKE :search OR tasks.description LIKE :search OR tasks.status LIKE :search OR tasks.priority LIKE :search OR tasks.objekt LIKE :search OR tasks.einheit LIKE :search)";
    $params[':search'] = '%' . $search_query . '%';
}

// Sortierung hinzufügen
$sql .= " ORDER BY $order_by $order_dir";

// Abfrage vorbereiten und ausführen
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Funktion, um die Sortierrichtung zu wechseln
function toggleSort($column, $current_order_by, $current_order_dir) {
    if ($column === $current_order_by) {
        return $current_order_dir === 'asc' ? 'desc' : 'asc';
    }
    return 'asc';
}
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
            <input class="form-control me-2" type="search" name="search" placeholder="Suche" aria-label="Search"
                value="<?php echo htmlspecialchars($search_query); ?>">
            <button class="btn btn-outline-success" type="submit">Suche</button>
        </form>

        <!-- Statusübersicht mit einem Bootstrap Alert -->
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
                    <th><a href="?order_by=title&order_dir=<?php echo toggleSort('title', $order_by, $order_dir); ?>">Titel</a></th>
                    <th><a href="?order_by=objekt&order_dir=<?php echo toggleSort('objekt', $order_by, $order_dir); ?>">Objekt</a></th>
                    <th><a href="?order_by=einheit&order_dir=<?php echo toggleSort('einheit', $order_by, $order_dir); ?>">Einheit</a></th>
                    <th><a href="?order_by=priority&order_dir=<?php echo toggleSort('priority', $order_by, $order_dir); ?>">Priorität</a></th>
                    <th><a href="?order_by=status&order_dir=<?php echo toggleSort('status', $order_by, $order_dir); ?>">Status</a></th>
                    <th><a href="?order_by=due_date&order_dir=<?php echo toggleSort('due_date', $order_by, $order_dir); ?>">Fälligkeitsdatum</a></th>
                    <th><a href="?order_by=monteur_name&order_dir=<?php echo toggleSort('monteur_name', $order_by, $order_dir); ?>">Monteur</a></th>
                    <th><a href="?order_by=created_at&order_dir=<?php echo toggleSort('created_at', $order_by, $order_dir); ?>">Eintragungsdatum</a></th>
                    <th><a href="?order_by=creator_name&order_dir=<?php echo toggleSort('creator_name', $order_by, $order_dir); ?>">Ersteller</a></th>
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
                        <td><?php echo htmlspecialchars($task['monteur_name'] ?: 'Kein Monteur'); ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($task['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($task['creator_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
