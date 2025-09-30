<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/includes/db.php';

$currentId = (int)$_SESSION['user_id'];
$targetId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $currentId;
if ($targetId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_target']);
    exit;
}

try {
    $db->exec('CREATE TABLE IF NOT EXISTS profile_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        target_id INT NOT NULL,
        author_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_target_created (target_id, created_at),
        CONSTRAINT fk_profile_comments_target FOREIGN KEY (target_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_profile_comments_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'db_error']);
    exit;
}

$userCheck = $db->prepare('SELECT 1 FROM users WHERE id = ?');
$userCheck->execute([$targetId]);
if (!$userCheck->fetchColumn()) {
    http_response_code(404);
    echo json_encode(['error' => 'user_not_found']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$canDelete = ($currentId === $targetId);

if ($method === 'GET') {
    $stmt = $db->prepare('SELECT pc.id, pc.comment, pc.created_at, pc.author_id, u.username
                           FROM profile_comments pc
                           JOIN users u ON u.id = pc.author_id
                           WHERE pc.target_id = ?
                           ORDER BY pc.created_at ASC, pc.id ASC');
    $stmt->execute([$targetId]);
    $comments = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $comments[] = [
            'id' => (int)$row['id'],
            'text' => $row['comment'],
            'created_at' => $row['created_at'],
            'author_id' => (int)$row['author_id'],
            'author' => $row['username'],
            'can_delete' => $canDelete,
        ];
    }
    echo json_encode($comments);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $text = trim($input['text'] ?? '');
    if ($text === '') {
        http_response_code(400);
        echo json_encode(['error' => 'empty']);
        exit;
    }
    if ((function_exists('mb_strlen') ? mb_strlen($text) : strlen($text)) > 500) {
        http_response_code(400);
        echo json_encode(['error' => 'too_long']);
        exit;
    }

    if ($targetId !== $currentId) {
        $stmt = $db->prepare('SELECT 1 FROM friend_requests WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND status = \'accepted\'');
        $stmt->execute([$currentId, $targetId, $targetId, $currentId]);
        if (!$stmt->fetchColumn()) {
            http_response_code(403);
            echo json_encode(['error' => 'not_friends']);
            exit;
        }
    }

    $authorStmt = $db->prepare('SELECT username FROM users WHERE id = ?');
    $authorStmt->execute([$currentId]);
    $username = $authorStmt->fetchColumn() ?: 'User';

    $insert = $db->prepare('INSERT INTO profile_comments (target_id, author_id, comment) VALUES (?, ?, ?)');
    $insert->execute([$targetId, $currentId, $text]);
    $commentId = (int)$db->lastInsertId();

    $dateStmt = $db->prepare('SELECT created_at FROM profile_comments WHERE id = ?');
    $dateStmt->execute([$commentId]);
    $createdAt = $dateStmt->fetchColumn();

    echo json_encode([
        'id' => $commentId,
        'text' => $text,
        'created_at' => $createdAt,
        'author_id' => $currentId,
        'author' => $username,
        'can_delete' => $canDelete,
    ]);
    exit;
}

if ($method === 'DELETE') {
    $commentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($commentId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_comment']);
        exit;
    }
    if (!$canDelete) {
        http_response_code(403);
        echo json_encode(['error' => 'forbidden']);
        exit;
    }

    $del = $db->prepare('DELETE FROM profile_comments WHERE id = ? AND target_id = ?');
    $del->execute([$commentId, $targetId]);
    echo json_encode(['ok' => $del->rowCount() > 0]);
    exit;
}

http_response_code(405);
?>