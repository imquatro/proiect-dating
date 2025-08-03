<?php
require_once __DIR__ . '/includes/db.php';

$stmt = $db->query("SELECT id, gallery, gallery_status FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $gallery = $user['gallery'] ? explode(',', $user['gallery']) : [];
    $statuses = $user['gallery_status'] ? explode(',', $user['gallery_status']) : [];
    $photoCount = count($gallery);

    if (count($statuses) < $photoCount) {
        $statuses = array_merge($statuses, array_fill(0, $photoCount - count($statuses), 'pending'));
    } elseif (count($statuses) > $photoCount) {
        $statuses = array_slice($statuses, 0, $photoCount);
    }

    $newStatus = $photoCount ? implode(',', $statuses) : '';
    $db->prepare("UPDATE users SET gallery_status = ? WHERE id = ?")->execute([$newStatus, $user['id']]);
}

echo "Sincronizare terminată.";
