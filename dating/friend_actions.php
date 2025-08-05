<?php
session_start();
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nu ești autentificat']);
    exit;
}

$action = $_POST['action'] ?? '';
$currentId = $_SESSION['user_id'];

require_once __DIR__ . '/includes/update_last_active.php';

function build_user($row) {
    $avatar = 'img/user_default.png';
    if (!empty($row['gallery'])) {
        $gal = explode(',', $row['gallery']);
        $avatar = 'uploads/' . $row['id'] . '/' . trim($gal[0]);
    }
    $last = isset($row['last_active']) ? strtotime($row['last_active']) : time();
    $diff = time() - $last;
    if ($diff <= 300) {
        $status = 'online';
    } elseif ($diff <= 1200) {
        $status = 'idle';
    } else {
        $status = 'offline';
    }
    return [
        'id' => $row['id'],
        'username' => $row['username'],
        'avatar' => $avatar,
        'status' => $status
    ];
}

if ($action === 'send_request') {
    $receiver = (int)($_POST['user_id'] ?? 0);
    if (!$receiver || $receiver == $currentId) {
        echo json_encode(['success' => false, 'message' => 'Utilizator invalid']);
        exit;
    }
    try {
        $stmt = $db->prepare('SELECT status FROM friend_requests WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)');
        $stmt->execute([$currentId, $receiver, $receiver, $currentId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Cerere existentă']);
            exit;
        }
        $stmt = $db->prepare('INSERT INTO friend_requests (sender_id, receiver_id, status, created_at) VALUES (?, ?, "pending", NOW())');
        $stmt->execute([$currentId, $receiver]);
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Eroare DB']);
        exit;
    }
}

if ($action === 'accept_request') {
    $sender = (int)($_POST['user_id'] ?? 0);
    if (!$sender || $sender == $currentId) {
        echo json_encode(['success' => false, 'message' => 'Utilizator invalid']);
        exit;
    }
    try {
        $stmt = $db->prepare('UPDATE friend_requests SET status = "accepted", responded_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND status = "pending"');
        $stmt->execute([$sender, $currentId]);
        if (!$stmt->rowCount()) {
            echo json_encode(['success' => false, 'message' => 'Cerere inexistentă']);
            exit;
        }
        $stmt = $db->prepare('SELECT id, username, gallery, last_active FROM users WHERE id = ?');
        $stmt->execute([$sender]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'user' => build_user($user)]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Eroare DB']);
        exit;
    }
}

if ($action === 'decline_request') {
    $sender = (int)($_POST['user_id'] ?? 0);
    if (!$sender || $sender == $currentId) {
        echo json_encode(['success' => false, 'message' => 'Utilizator invalid']);
        exit;
    }
    try {
        $stmt = $db->prepare('DELETE FROM friend_requests WHERE sender_id = ? AND receiver_id = ? AND status = "pending"');
        $stmt->execute([$sender, $currentId]);
        if ($stmt->rowCount()) {
            $stmt = $db->prepare('SELECT id, username, gallery, last_active FROM users WHERE id = ?');
            $stmt->execute([$sender]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'user' => build_user($user)]);
            exit;
        }
        echo json_encode(['success' => false, 'message' => 'Cerere inexistentă']);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Eroare DB']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Acțiune necunoscută']);