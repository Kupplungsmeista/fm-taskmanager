<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo 'Nicht autorisiert';
    exit();
}

$task_id = $_GET['task_id'];

// Hochgeladene Dateien abrufen
$stmt = $pdo->prepare('SELECT * FROM files WHERE task_id = ?');
$stmt->execute([$task_id]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3 class="mt-4">Galerie</h3>
<div class="row">
    <?php foreach ($files as $file): ?>
        <?php if (in_array(pathinfo($file['filename'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])): ?>
            <div class="col-md-3 position-relative mb-3" id="file-<?php echo $file['id']; ?>">
                <!-- Bild löschen -->
                <button class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="deleteImage(<?php echo $file['id']; ?>)">X</button>
                <!-- Bild anzeigen -->
                <img src="<?php echo $file['filepath']; ?>" class="img-fluid img-thumbnail" alt="<?php echo $file['filename']; ?>"
                    data-bs-toggle="modal" data-bs-target="#imageModal" onclick="openImage('<?php echo $file['filepath']; ?>')">
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<h3 class="mt-4">Dokumente</h3>
<ul>
    <?php foreach ($files as $file): ?>
        <?php if (in_array(pathinfo($file['filename'], PATHINFO_EXTENSION), ['pdf', 'docx', 'doc', 'txt'])): ?>
            <li id="file-<?php echo $file['id']; ?>"><a href="<?php echo $file['filepath']; ?>" target="_blank"><?php echo $file['filename']; ?></a>
                <button class="btn btn-sm btn-danger" onclick="deleteImage(<?php echo $file['id']; ?>)">X</button>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
