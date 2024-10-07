<?php
session_start();
require '../config/db.php';

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
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
function toggleSort($column, $current_order_by, $current_order_dir)
{
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
        /* Hover-Effekt für Tabellenzeilen */
        table tr:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }

        /* Card-Design für Übersicht und Suchfeld */
        .status-card, .search-card, .table-card {
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.05);
        }

        /* Buttons klarer hervorheben */
        .btn-primary, .btn-outline-success {
            font-weight: bold;
        }

        /* Abstand für Seitentitel und Schaltflächen */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar einbinden -->
            <?php include 'sidebar.php'; ?>

            <!-- Hauptinhalt -->
            <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="header border-bottom">
                    <h2 class="text-primary">Aufgabenübersicht</h2>

                    <!-- Logout Button oben rechts -->
                    <a href="logout.php" class="btn btn-outline-danger">Logout</a>
                </div>

                <!-- Suchfeld -->
                <div class="search-card">
                    <form method="GET" class="d-flex">
                        <input class="form-control me-2" type="search" name="search" placeholder="Suche" aria-label="Search"
                            value="<?php echo htmlspecialchars($search_query); ?>">
                        <button class="btn btn-outline-success" type="submit">Suche</button>
                    </form>
                </div>

                <!-- Statusübersicht -->
                <div class="status-card alert alert-info">
                    <h5>Aufgabenstatus:</h5>
                    <ul>
                        <li>Ausstehend: <strong><?php echo $status_counts['ausstehend']; ?></strong></li>
                        <li>In Bearbeitung: <strong><?php echo $status_counts['in_bearbeitung']; ?></strong></li>
                        <li>Erledigt: <strong><?php echo $status_counts['erledigt']; ?></strong></li>
                    </ul>
                </div>

                <!-- Button zum Hinzufügen einer neuen Aufgabe -->
                <a href="add_task.php" class="btn btn-primary mb-3">Neue Aufgabe hinzufügen</a>

                <!-- Tabelle der Aufgaben -->
                <div class="table-card">
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
            </main>
        </div>
    </div>
</body>

</html>
