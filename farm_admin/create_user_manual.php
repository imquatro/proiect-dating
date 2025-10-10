<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

require_once '../includes/db.php';
require_once '../includes/slot_helpers.php';

// Check if user is admin
$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$age = isset($_POST['age']) ? intval($_POST['age']) : null;
$country = trim($_POST['country'] ?? '');
$city = trim($_POST['city'] ?? '');
$gender = $_POST['gender'] ?? '';

// Validate input
$errors = [];

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($username)) {
    $errors[] = 'Username is required';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters long';
}

if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters long';
}

if (empty($age) || $age < 18 || $age > 99) {
    $errors[] = 'Age must be between 18 and 99';
}

if (empty($country)) {
    $errors[] = 'Country is required';
}

if (empty($city)) {
    $errors[] = 'City is required';
}

if (empty($gender) || !in_array($gender, ['masculin', 'feminin'])) {
    $errors[] = 'Valid gender selection is required';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Validation errors', 'errors' => $errors]);
    exit;
}

try {
    $db->beginTransaction();
    
    // Check if email or username already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'User with this email or username already exists']);
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user with auto_account flag (manual creation = 0)
    $stmt = $db->prepare("INSERT INTO users (email, username, password, age, country, city, gender, level, auto_account) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0)");
    
    if ($stmt->execute([$email, $username, $hashedPassword, $age, $country, $city, $gender])) {
        $userId = $db->lastInsertId();
        
        // Add default slots for the user
        $defaults = $db->query("SELECT slot_number, slot_type, unlocked FROM default_slots");
        $ins = $db->prepare("INSERT INTO user_slots (user_id, slot_number, slot_type, unlocked, required_level) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($defaults as $slot) {
            $slotNum = (int)$slot['slot_number'];
            $required = get_slot_required_level($slotNum);
            $ins->execute([$userId, $slotNum, $slot['slot_type'], $slot['unlocked'], $required]);
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'user' => [
                'id' => $userId,
                'username' => $username,
                'email' => $email,
                'age' => $age,
                'country' => $country,
                'city' => $city,
                'gender' => $gender
            ]
        ]);
        
    } else {
        throw new Exception('Failed to create user');
    }
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error creating user: ' . $e->getMessage()
    ]);
}
?>
