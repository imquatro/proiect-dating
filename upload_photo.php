<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Trebuie să fii autentificat.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // Setări upload
    $upload_dir = 'uploads/' . $user_id . '/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $filename = basename($_FILES["file"]["name"]);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $timestamp = date("Ymd_His") . '_' . rand(10, 99);
    $db_filename = "photo_" . $timestamp . "." . $ext;
    $target_file = $upload_dir . $db_filename;

    $uploadOk = 1;
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Validări minime
    if ($_FILES["file"]["size"] > 10 * 1024 * 1024) $uploadOk = 0; // max 10MB
    if (!in_array($ext, $allowed)) $uploadOk = 0;

    if ($uploadOk && move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        // --- ACTUALIZARE GALLERY & GALLERY_STATUS CORECT ---
        $stmt = $db->prepare("SELECT gallery, gallery_status FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $gallery = $user['gallery'] ? explode(',', $user['gallery']) : [];
        $statuses = $user['gallery_status'] ? explode(',', $user['gallery_status']) : [];

        $gallery[] = $db_filename;
        $statuses[] = 'pending';

        $stmt = $db->prepare("UPDATE users SET gallery = ?, gallery_status = ? WHERE id = ?");
        $stmt->execute([implode(',', $gallery), implode(',', $statuses), $user_id]);

        header("Location: profile.php?msg=Upload+reușit");
        exit;
    } else {
        die("Upload nereușit.");
    }
} else {
    die("Formular invalid.");
}
?>
