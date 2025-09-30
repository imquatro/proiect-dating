<?php
session_start(['read_and_close' => true]);
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/includes/db.php';

$userId   = (int)$_SESSION['user_id'];
$targetId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $userId;
$since    = isset($_GET['since']) ? (int)$_GET['since'] : 0;

if ($targetId !== $userId) {
    $friendStmt = $db->prepare(
        'SELECT 1 FROM friend_requests WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND status = \'accepted\''
    );
    $friendStmt->execute([$userId, $targetId, $targetId, $userId]);
    if (!$friendStmt->fetchColumn()) {
        http_response_code(403);
        exit;
    }
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
set_time_limit(0);
ignore_user_abort(true);

$lastSent = $since;
$lastPing = time();

while (!connection_aborted()) {
    $stmt = $db->prepare(
        'SELECT slot_number, image, water_interval, feed_interval, water_remaining, feed_remaining, timer_type, timer_end, UNIX_TIMESTAMP(updated_at) * 1000 AS updated_at'
        . ' FROM user_slot_states'
        . ' WHERE user_id = ? AND updated_at > ?'
        . ' ORDER BY updated_at ASC'
    );
    $stmt->execute([$targetId, date('Y-m-d H:i:s', $lastSent / 1000)]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data = [
            'slotId'       => (int)$row['slot_number'],
            'image'        => $row['image'],
            'waterInterval'=> (int)$row['water_interval'],
            'feedInterval' => (int)$row['feed_interval'],
            'waterTimes'   => (int)$row['water_remaining'],
            'feedTimes'    => (int)$row['feed_remaining'],
            'timerType'    => $row['timer_type'],
            'timerEnd'     => $row['timer_end'] ? (strtotime($row['timer_end']) * 1000) : null
        ];
        $lastSent = max($lastSent, (int)$row['updated_at']);
        echo 'data: ' . json_encode($data) . "\n\n";
        @ob_flush();
        @flush();
        $lastPing = time();
    }
    if (time() - $lastPing >= 15) {
        echo ": ping\n\n";
        @ob_flush();
        @flush();
        $lastPing = time();
    }
    sleep(1);
}