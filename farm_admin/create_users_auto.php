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

$userCount = isset($_POST['user_count']) ? intval($_POST['user_count']) : 0;

if ($userCount < 1) {
    echo json_encode(['success' => false, 'message' => 'Number of users must be at least 1']);
    exit;
}

// Warning for very large numbers
if ($userCount > 1000) {
    echo json_encode(['success' => false, 'message' => 'Warning: Creating more than 1000 users at once may take a long time. Please create in smaller batches.']);
    exit;
}

// Random data generators
function generateRandomNickname() {
    $nicknames = [
        'Alex', 'Mihai', 'Andrei', 'Cristi', 'Dan', 'Florin', 'Gabriel', 'Ion', 'Lucian', 'Marius',
        'Ana', 'Maria', 'Elena', 'Ioana', 'Andreea', 'Cristina', 'Diana', 'Gabriela', 'Irina', 'Laura',
        'Bogdan', 'Catalin', 'Dragos', 'Emil', 'Fabian', 'George', 'Horia', 'Iulian', 'Laur', 'Nico',
        'Ovidiu', 'Petru', 'Radu', 'Sebi', 'Tudor', 'Vlad', 'Zeno', 'Adrian', 'Bogdan', 'Claudiu',
        'Dorin', 'Eugen', 'Felix', 'Gheorghe', 'Horia', 'Ilie', 'Josef', 'Kevin', 'Liviu', 'Marian',
        'Narcis', 'Octavian', 'Pavel', 'Rares', 'Sorin', 'Tiberiu', 'Ursu', 'Valentin', 'Xavier', 'Yorick',
        'Mike', 'John', 'David', 'Chris', 'Mark', 'Paul', 'Tom', 'Sam', 'Nick', 'Rob',
        'Sarah', 'Emma', 'Lisa', 'Kate', 'Amy', 'Jen', 'Beth', 'Ruth', 'Sue', 'Tina'
    ];
    
    $nickname = $nicknames[array_rand($nicknames)];
    $number = rand(100, 9999);
    
    return $nickname . $number;
}

function generateRandomEmail($username) {
    $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'example.com'];
    $domain = $domains[array_rand($domains)];
    $cleanUsername = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($username));
    return $cleanUsername . rand(100, 9999) . '@' . $domain;
}

function generateRandomAge() {
    return rand(18, 65);
}

function generateRandomGender() {
    return rand(0, 1) ? 'masculin' : 'feminin';
}

function generateRandomCountry() {
    $countries = [
        'România', 'Moldova', 'Italia', 'Franța', 'Germania', 'Spania', 'Portugalia', 'Grecia', 'Polonia', 'Ungaria'
    ];
    return $countries[array_rand($countries)];
}

function generateRandomCity($country) {
    $cities = [
        'România' => ['București', 'Cluj-Napoca', 'Timișoara', 'Iași', 'Constanța', 'Craiova', 'Galați', 'Ploiești'],
        'Moldova' => ['Chișinău', 'Bălți', 'Tiraspol', 'Bendery'],
        'Italia' => ['Roma', 'Milano', 'Napoli', 'Torino', 'Palermo'],
        'Franța' => ['Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice'],
        'Germania' => ['Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt'],
        'Spania' => ['Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Zaragoza'],
        'Portugalia' => ['Lisabona', 'Porto', 'Coimbra', 'Braga'],
        'Grecia' => ['Atena', 'Salonika', 'Patras', 'Heraklion'],
        'Polonia' => ['Varșovia', 'Krakow', 'Gdansk', 'Wroclaw'],
        'Ungaria' => ['Budapesta', 'Debrecen', 'Szeged', 'Miskolc']
    ];
    
    if (isset($cities[$country])) {
        return $cities[$country][array_rand($cities[$country])];
    }
    return 'Unknown';
}

$createdUsers = [];
$errors = [];

try {
    $db->beginTransaction();
    
    for ($i = 0; $i < $userCount; $i++) {
        // Generate random data
        $username = generateRandomNickname();
        $email = generateRandomEmail($username);
        $password = $_POST['default_password'] ?? 'password123'; // Use provided password or default
        $age = generateRandomAge();
        $country = generateRandomCountry();
        $city = generateRandomCity($country);
        $gender = generateRandomGender();
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if email or username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        
        if ($stmt->fetch()) {
            $errors[] = "User with email {$email} or username {$username} already exists";
            continue;
        }
        
        // Insert user with auto_account flag
        $stmt = $db->prepare("INSERT INTO users (email, username, password, age, country, city, gender, level, auto_account) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1)");
        
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
            
            // AUTO-ENROLL IN PVP BRONZE LEAGUE
            $db->prepare("INSERT IGNORE INTO user_league_status (user_id, league_id) VALUES (?, 1)")->execute([$userId]);
            
            $createdUsers[] = [
                'id' => $userId,
                'username' => $username,
                'email' => $email,
                'age' => $age,
                'country' => $country,
                'city' => $city,
                'gender' => $gender
            ];
        } else {
            $errors[] = "Failed to create user with email {$email}";
        }
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully created " . count($createdUsers) . " users",
        'created_users' => $createdUsers,
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error creating users: ' . $e->getMessage()
    ]);
}
?>
