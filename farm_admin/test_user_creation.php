<?php
// Test file to demonstrate user creation functionality
session_start();

// Simulate admin session for testing
$_SESSION['user_id'] = 1;

require_once '../includes/db.php';

// Check if user is admin (simulate)
$isAdmin = true; // For testing purposes

if (!$isAdmin) {
    die('Access denied');
}

echo "<h1>User Creation Test</h1>";
echo "<p>This is a test page to demonstrate the user creation functionality.</p>";

// Test random data generation functions
function generateRandomName() {
    $firstNames = [
        'Alexandru', 'Mihai', 'Andrei', 'Cristian', 'Daniel', 'Florin', 'Gabriel', 'Ion', 'Lucian', 'Marius',
        'Ana', 'Maria', 'Elena', 'Ioana', 'Andreea', 'Cristina', 'Diana', 'Gabriela', 'Irina', 'Laura'
    ];
    
    $lastNames = [
        'Popescu', 'Ionescu', 'Popa', 'Radu', 'Dumitrescu', 'Stan', 'Stoica', 'Gheorghe', 'Nistor', 'Moldovan',
        'Constantinescu', 'Marin', 'Tudor', 'Sandu', 'Luca', 'Vasile', 'Ciobanu', 'Mihai', 'Dobre', 'Petrescu'
    ];
    
    return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
}

function generateRandomEmail($username) {
    $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'example.com'];
    $domain = $domains[array_rand($domains)];
    $cleanUsername = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($username));
    return $cleanUsername . rand(100, 9999) . '@' . $domain;
}

echo "<h2>Sample Random Data Generation:</h2>";
echo "<ul>";
for ($i = 1; $i <= 5; $i++) {
    $username = 'user' . rand(100000, 999999);
    $fullName = generateRandomName();
    $email = generateRandomEmail($username);
    $age = rand(18, 65);
    $gender = rand(0, 1) ? 'masculin' : 'feminin';
    
    echo "<li>";
    echo "<strong>User $i:</strong><br>";
    echo "Username: $username<br>";
    echo "Full Name: $fullName<br>";
    echo "Email: $email<br>";
    echo "Age: $age<br>";
    echo "Gender: $gender<br>";
    echo "</li><br>";
}
echo "</ul>";

echo "<h2>Admin Panel Integration:</h2>";
echo "<p>The 'Add Users' functionality has been integrated into the admin panel.</p>";
echo "<p>Features:</p>";
echo "<ul>";
echo "<li>Auto Create Users: Create multiple users with random data</li>";
echo "<li>Manual Create User: Create individual users with custom data</li>";
echo "<li>Random data generation for: names, emails, ages, genders, countries, cities</li>";
echo "<li>Automatic slot assignment for new users</li>";
echo "</ul>";

echo "<h2>How to Access:</h2>";
echo "<ol>";
echo "<li>Log in as an admin user</li>";
echo "<li>Go to Settings page</li>";
echo "<li>Click on 'Admin Panel' tab</li>";
echo "<li>Scroll down to see the 'Add Users' button in the secondary button row</li>";
echo "<li>Click 'Add Users' to access the user creation interface</li>";
echo "</ol>";
?>
