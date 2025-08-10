<?php
/**
 * VanGo User Signup Handler
 * Handles user registration with database storage
 */

session_start();
require_once 'database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

// Validate input
if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit;
}

// Split name into first and last name
$nameParts = explode(' ', $name, 2);
$firstName = trim($nameParts[0]);
$lastName = isset($nameParts[1]) ? trim($nameParts[1]) : '';

if (empty($firstName)) {
    echo json_encode(['success' => false, 'message' => 'Please enter your full name']);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists']);
        exit;
    }
    
    // Generate unique user ID
    $userId = 'U' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (user_id, first_name, last_name, email, password, phone, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'active', CURRENT_TIMESTAMP)
    ");
    
    $stmt->execute([
        $userId,
        $firstName,
        $lastName,
        $email,
        $hashedPassword,
        '' // Default empty phone, can be updated later
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Log successful registration
    error_log("User registration successful: {$email} at " . date('Y-m-d H:i:s'));
    
    echo json_encode([
        'success' => true, 
        'message' => 'Account created successfully! Please sign in.',
        'user' => [
            'user_id' => $userId,
            'name' => $firstName . ' ' . $lastName,
            'email' => $email
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?> 