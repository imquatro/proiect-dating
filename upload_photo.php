<?php
session_start(); // IMPORTANT: Prima linie, fără spații sau enteruri înainte!
require_once __DIR__ . '/includes/db.php';

// --- DEBUG temporar (DEZACTIVEAZĂ la PROD!): ---
// var_dump($_SESSION, $_POST, $_FILES); exit;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_FILES['profile_photo']) &&
    isset($_POST['user_id']) &&
    $_POST['user_id'] == $user_id
) {
    $img = $_FILES['profile_photo'];

    // Creează folderul userului după ID dacă nu există (uploads/1/, uploads/2/, etc.)
    $upload_dir = __DIR__ . "/uploads/" . $user_id . "/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Nume unic pentru poză
    $ext = pathinfo($img['name'], PATHINFO_EXTENSION);
    $name = "photo_" . date("Ymd_His") . "_" . rand(10,99) . "." . strtolower($ext);

    $upload_path = $upload_dir . $name;
    $db_path = "uploads/" . $user_id . "/" . $name; // calea stocată în DB

    // Extensii permise și dimensiune maximă
    $allowed_exts = ['jpg','jpeg','png','webp','gif'];
    if (in_array(strtolower($ext), $allowed_exts) && $img['size'] < 8*1024*1024) {
        if (move_uploaded_file($img['tmp_name'], $upload_path)) {
            // Adaugă poza la galerie (concatenează dacă există deja)
            $stmt = $db->prepare("SELECT gallery FROM users WHERE id=?");
            $stmt->execute([$user_id]);
            $current = $stmt->fetchColumn();

            // Dacă există deja poze, concatenează cu virgulă
            $new_gallery = $current ? ($current . "," . $db_path) : $db_path;

            $stmt = $db->prepare("UPDATE users SET gallery=? WHERE id=?");
            $stmt->execute([$new_gallery, $user_id]);
        }
    }
    // Redirecționează înapoi la profil
    header("Location: profile.php");
    exit;
}
header("Location: profile.php?error=upload");
exit;
