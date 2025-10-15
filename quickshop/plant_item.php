<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

require_once '../includes/db.php';

$data   = json_decode(file_get_contents('php://input'), true);
$slots  = isset($data['slots']) && is_array($data['slots']) ? array_map('intval', $data['slots']) : [];
$slots  = array_values(array_unique($slots));
$itemId = intval($data['item'] ?? 0);

if (empty($slots) || !$itemId) {
    echo json_encode(['success' => false]);
    exit;
}

$userId    = $_SESSION['user_id'];
$slotCount = count($slots);

// Verify item and price from database
$stmt = $db->prepare('SELECT price, image_plant, water_times, feed_times, water_interval, feed_interval FROM farm_items WHERE id = ?');
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    echo json_encode(['success' => false]);
    exit;
}

$price = (int)$item['price'];
$image = $item['image_plant'];

if (strpos($image, 'img/') !== 0) {
    $image = 'img/' . ltrim($image, '/');
}

// Check user funds and VIP status
$stmt = $db->prepare('SELECT money, vip FROM users WHERE id = ?');
$stmt->execute([$userId]);
$urow  = $stmt->fetch(PDO::FETCH_ASSOC);
$money = isset($urow['money']) ? (int)$urow['money'] : 0;
$vip   = !empty($urow['vip']);

// Allow multiple slot planting only for VIP users
if ($slotCount > 1 && !$vip) {
    echo json_encode(['success' => false, 'error' => 'You are not VIP']);
    exit;
}

$totalPrice = $price * $slotCount;
if ($money < $totalPrice) {
    echo json_encode(['success' => false, 'error' => 'Insufficient funds']);
    exit;
}

// Deduct funds and store plant
try {
    $db->beginTransaction();
    $db->prepare('UPDATE users SET money = money - ? WHERE id = ?')
        ->execute([$totalPrice, $userId]);
    $ins = $db->prepare('INSERT INTO user_plants (user_id, slot_number, item_id, planted_at)
                  VALUES (?, ?, ?, NOW())
                  ON DUPLICATE KEY UPDATE item_id = VALUES(item_id), planted_at = NOW()');
    
    // Initialize slot states for real-time sync
    $stateIns = $db->prepare(
        'INSERT INTO user_slot_states 
         (user_id, slot_number, image, water_interval, feed_interval, water_remaining, feed_remaining, updated_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE 
         image = VALUES(image), 
         water_interval = VALUES(water_interval), 
         feed_interval = VALUES(feed_interval), 
         water_remaining = VALUES(water_remaining), 
         feed_remaining = VALUES(feed_remaining),
         timer_type = NULL,
         timer_end = NULL,
         updated_at = NOW()'
    );
    
    foreach ($slots as $slotId) {
        $ins->execute([$userId, $slotId, $itemId]);
        $stateIns->execute([
            $userId, 
            $slotId, 
            $image, 
            (int)$item['water_interval'], 
            (int)$item['feed_interval'], 
            (int)$item['water_times'], 
            (int)$item['feed_times']
        ]);
    }
    $db->commit();

    $walletStmt = $db->prepare('SELECT money, gold FROM users WHERE id = ?');
    $walletStmt->execute([$userId]);
    $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'image'   => $image,
        'money'   => isset($wallet['money']) ? (int)$wallet['money'] : 0,
        'gold'    => isset($wallet['gold']) ? (int)$wallet['gold'] : 0
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false]);
}
