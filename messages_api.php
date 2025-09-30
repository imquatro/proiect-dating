<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

require_once __DIR__ . '/includes/db.php';
$user_id = (int)$_SESSION['user_id'];

$db->exec('CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0
)');
try {
    $db->exec('ALTER TABLE messages ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0');
} catch (PDOException $e) {
    // Column may already exist
}

$action = $_REQUEST['action'] ?? '';
$friend_id = isset($_REQUEST['friend_id']) ? (int)$_REQUEST['friend_id'] : 0;
if ($action !== 'unread_count' && $friend_id <= 0) {
    echo json_encode(['error' => 'missing_friend']);
    exit;
}

switch ($action) {
    case 'send':
        $text = trim($_POST['message'] ?? '');
        if ($text !== '') {
            $stmt = $db->prepare('INSERT INTO messages (sender_id, receiver_id, message, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)');
            $stmt->execute([$user_id, $friend_id, $text]);
        }
        echo json_encode(['status' => 'ok']);
        break;

    case 'fetch':
        $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
        $stmt = $db->prepare('SELECT id, sender_id, receiver_id, message, created_at
            FROM messages
            WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
            AND id > ?
            ORDER BY id ASC');
        $stmt->execute([$user_id, $friend_id, $friend_id, $user_id, $last_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $db->prepare('UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0');
        $stmt->execute([$friend_id, $user_id]);

        $db->exec('CREATE TABLE IF NOT EXISTS typing_status (
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            last_typing DATETIME NOT NULL,
            PRIMARY KEY(sender_id, receiver_id)
        )');
        $stmt = $db->prepare('SELECT last_typing FROM typing_status WHERE sender_id = ? AND receiver_id = ?');
        $stmt->execute([$friend_id, $user_id]);
        $typing = false;
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $typing = (time() - strtotime($row['last_typing']) < 5);
        }

        echo json_encode(['messages' => $messages, 'typing' => $typing]);
        break;

    case 'typing':
        $db->exec('CREATE TABLE IF NOT EXISTS typing_status (
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            last_typing DATETIME NOT NULL,
            PRIMARY KEY(sender_id, receiver_id)
        )');
        $stmt = $db->prepare('INSERT INTO typing_status (sender_id, receiver_id, last_typing)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE last_typing = NOW()');
        $stmt->execute([$user_id, $friend_id]);
        echo json_encode(['status' => 'ok']);
        break;

    case 'stop_typing':
        $db->exec('CREATE TABLE IF NOT EXISTS typing_status (
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            last_typing DATETIME NOT NULL,
            PRIMARY KEY(sender_id, receiver_id)
        )');
        $stmt = $db->prepare('DELETE FROM typing_status WHERE sender_id = ? AND receiver_id = ?');
        $stmt->execute([$user_id, $friend_id]);
        echo json_encode(['status' => 'ok']);
        break;

    case 'unread_count':
        $stmt = $db->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0');
        $stmt->execute([$user_id]);
        $count = (int)$stmt->fetchColumn();
        echo json_encode(['count' => $count]);
        break;

    case 'delete_conversation':
        $stmt = $db->prepare('DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)');
        $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
        echo json_encode(['status' => 'deleted']);
        break;

    default:
        echo json_encode(['error' => 'unknown_action']);
}