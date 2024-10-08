<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$message = '';
// Benutzerverwaltung: Hinzufügen, Entfernen, Bearbeiten
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $username = $_POST['username'] ?? null;
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    try {
        if (isset($_POST['add_user'])) {
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $password]);
            $message = "Benutzer erfolgreich hinzugefügt.";
        } elseif (isset($_POST['remove_user'])) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = "Benutzer erfolgreich entfernt.";
        } elseif (isset($_POST['edit_user'])) {
            $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->execute([$_POST['new_username'], $user_id]);
            if ($password) {
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$password, $user_id]);
            }
            $message = "Benutzer erfolgreich aktualisiert.";
        }
    } catch (PDOException $e) {
        $message = "Fehler: " . $e->getMessage();
    }
}

// Objektverwaltung: Hinzufügen, Entfernen, Bearbeiten
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $object_id = $_POST['object_id'] ?? null;
    $object_name = $_POST['object_name'] ?? null;

    try {
        if (isset($_POST['add_object'])) {
            $stmt = $pdo->prepare("INSERT INTO objekte (name) VALUES (?)");
            $stmt->execute([$object_name]);
            $message = "Objekt erfolgreich hinzugefügt.";
        } elseif (isset($_POST['remove_object'])) {
            $stmt = $pdo->prepare("DELETE FROM objekte WHERE id = ?");
            $stmt->execute([$object_id]);
            $message = "Objekt erfolgreich entfernt.";
        } elseif (isset($_POST['edit_object'])) {
            $stmt = $pdo->prepare("UPDATE objekte SET name = ? WHERE id = ?");
            $stmt->execute([$_POST['new_object_name'], $object_id]);
            $message = "Objekt erfolgreich aktualisiert.";
        }
    } catch (PDOException $e) {
        $message = "Fehler: " . $e->getMessage();
    }
}

$users = $pdo->query("SELECT id, username FROM users")->fetchAll(PDO::FETCH_ASSOC);
$objects = $pdo->query("SELECT id, name FROM objekte")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Einstellungen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h2>Einstellungen</h2>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-info"><?= $message ?></div>
            <?php endif; ?>

            <!-- Benutzerverwaltung -->
            <div class="card mb-4">
                <div class="card-header">Benutzerverwaltung</div>
                <div class="card-body">
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Benutzer hinzufügen</button>

                    <!-- Tabelle mit Benutzern -->
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Benutzername</th>
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= $user['username'] ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" name="remove_user">Entfernen</button>
                                    </form>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal" onclick="editUser(<?= $user['id'] ?>, '<?= $user['username'] ?>')">Bearbeiten</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Objektverwaltung -->
            <div class="card mb-4">
                <div class="card-header">Objektverwaltung</div>
                <div class="card-body">
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addObjectModal">Objekt hinzufügen</button>

                    <!-- Tabelle mit Objekten -->
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Objektname</th>
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($objects as $object): ?>
                            <tr>
                                <td><?= $object['id'] ?></td>
                                <td><?= $object['name'] ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="object_id" value="<?= $object['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" name="remove_object">Entfernen</button>
                                    </form>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editObjectModal" onclick="editObject(<?= $object['id'] ?>, '<?= $object['name'] ?>')">Bearbeiten</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal zum Hinzufügen eines Benutzers -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Benutzer hinzufügen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Benutzername</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Passwort</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="add_user">Hinzufügen</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal zum Bearbeiten eines Benutzers -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Benutzer bearbeiten</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label class="form-label">Neuer Benutzername</label>
                        <input type="text" class="form-control" id="new_username" name="new_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Neues Passwort (optional)</label>
                        <input type="password" class="form-control" id="new_password" name="password">
                    </div>
                    <button type="submit" class="btn btn-warning" name="edit_user">Aktualisieren</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal zum Hinzufügen eines Objekts -->
<div class="modal fade" id="addObjectModal" tabindex="-1" aria-labelledby="addObjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addObjectModalLabel">Objekt hinzufügen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Objektname</label>
                        <input type="text" class="form-control" name="object_name" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="add_object">Hinzufügen</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal zum Bearbeiten eines Objekts -->
<div class="modal fade" id="editObjectModal" tabindex="-1" aria-labelledby="editObjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editObjectModalLabel">Objekt bearbeiten</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="object_id" id="edit_object_id">
                    <div class="mb-3">
                        <label class="form-label">Neuer Objektname</label>
                        <input type="text" class="form-control" id="new_object_name" name="new_object_name" required>
                    </div>
                    <button type="submit" class="btn btn-warning" name="edit_object">Aktualisieren</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function editUser(userId, username) {
        document.getElementById('edit_user_id').value = userId;
        document.getElementById('new_username').value = username;
    }

    function editObject(objectId, objectName) {
        document.getElementById('edit_object_id').value = objectId;
        document.getElementById('new_object_name').value = objectName;
    }
</script>

</body>
</html>
