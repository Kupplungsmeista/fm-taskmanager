<?php
require '../config/db.php';

// Verarbeiten der Sortieranfrage
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'status';
$order_dir = isset($_GET['order_dir']) && $_GET['order_dir'] === 'desc' ? 'desc' : 'asc';

// SQL-Abfrage für die Aufgaben mit benutzerdefinierter Sortierung nach Status
$sql = "SELECT tasks.*, users.username as creator_name, monteurs.name as monteur_name FROM tasks
        LEFT JOIN users ON tasks.created_by = users.id
        LEFT JOIN monteurs ON tasks.monteur_id = monteurs.id
        WHERE 1";

// Sortierung hinzufügen
$sql .= " ORDER BY $order_by $order_dir";

// Abfrage vorbereiten und ausführen
$stmt = $pdo->prepare($sql);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Aufgaben als HTML zurückgeben
foreach ($tasks as $task) {
    echo "<tr onclick=\"window.location='edit_task.php?id=" . $task['id'] . "'\">";
    echo "<td>" . htmlspecialchars($task['title']) . "</td>";
    echo "<td>" . htmlspecialchars($task['objekt']) . "</td>";
    echo "<td>" . htmlspecialchars($task['einheit']) . "</td>";
    echo "<td>" . htmlspecialchars($task['priority']) . "</td>";
    echo "<td>" . htmlspecialchars($task['status']) . "</td>";
    echo "<td>" . date('d.m.Y', strtotime($task['due_date'])) . "</td>";
    echo "<td>" . htmlspecialchars($task['monteur_name'] ?: 'Kein Monteur') . "</td>";
    echo "<td>" . date('d.m.Y H:i', strtotime($task['created_at'])) . "</td>";
    echo "<td>" . htmlspecialchars($task['creator_name']) . "</td>";
    echo "</tr>";
}
