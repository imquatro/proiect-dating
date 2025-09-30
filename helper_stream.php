<?php
session_start(['read_and_close' => true]);
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}
$userId = (int)$_SESSION['user_id'];

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
set_time_limit(0);
ignore_user_abort(true);

require_once __DIR__ . '/includes/db.php';
$lastSent = 0;
$lastPing = time();

while (!connection_aborted()) {
    $stmt = $db->prepare(
        'SELECT ulh.helper_id, ulh.action, ulh.helped_at, ulh.clicks, u.gallery'
        . ' FROM user_last_helpers ulh'
        . ' JOIN users u ON u.id = ulh.helper_id'
        . ' WHERE ulh.owner_id = ? AND UNIX_TIMESTAMP(ulh.helped_at) * 1000 > ?'
        . ' ORDER BY ulh.helped_at ASC'
    );
    $stmt->execute([$userId, $lastSent]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (time() - strtotime($row['helped_at']) > 5) {
            continue;
        }
        $gallery = !empty($row['gallery']) ? array_filter(explode(',', $row['gallery'])) : [];
        $photo = 'default-avatar.png';
        if (!empty($gallery)) {
            $candidate = 'uploads/' . $row['helper_id'] . '/' . $gallery[0];
            if (is_file($candidate)) {
                $photo = $candidate;
            }
        }
        $data = [
            'helper_id' => (int)$row['helper_id'],
            'action'    => $row['action'],
            'helped_at' => $row['helped_at'],
            'photo'     => $photo,
            'clicks'    => isset($row['clicks']) ? (int)$row['clicks'] : 1,
        ];
        $lastSent = max($lastSent, strtotime($row['helped_at']) * 1000);
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