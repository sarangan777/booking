<?php
/**
 * VanGo Admin Login
 * Admin authentication with default credentials
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

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

// Check for default admin credentials
if ($email === 'admin@gmail.com' && $password === 'admin1234') {
    // Create admin session
    $_SESSION['admin_id'] = 'ADMIN001';
    $_SESSION['admin_email'] = $email;
    $_SESSION['admin_name'] = 'Administrator';
    $_SESSION['admin_role'] = 'super_admin';
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_login_time'] = time();
    
    // Log successful admin login
    error_log("Admin login successful: {$email} at " . date('Y-m-d H:i:s'));
    
    echo json_encode([
        'success' => true, 
        'message' => 'Admin login successful!',
        'redirect' => 'admin-dashboard.php',
        'admin' => [
            'admin_id' => 'ADMIN001',
            'name' => 'Administrator',
            'email' => $email,
            'role' => 'super_admin'
        ]
    ]);
} else {
    // Check database for admin users
    try {
        $pdo = getDatabaseConnection();
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Create admin session
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_login_time'] = time();
                
                // Update last login
                $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE admin_id = ?");
                $updateStmt->execute([$admin['admin_id']]);
                
                // Log successful admin login
                error_log("Admin login successful: {$email} at " . date('Y-m-d H:i:s'));
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Admin login successful!',
                    'redirect' => 'admin-dashboard.php',
                    'admin' => [
                        'admin_id' => $admin['admin_id'],
                        'name' => $admin['first_name'] . ' ' . $admin['last_name'],
                        'email' => $admin['email'],
                        'role' => $admin['role']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        }
    } catch (Exception $e) {
        error_log("Admin login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
}
?> 