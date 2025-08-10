<?php
/**
 * Test Authentication System
 * Tests both user and admin login functionality
 */

session_start();
require_once 'database.php';

// Set content type
header('Content-Type: text/html; charset=UTF-8');

// Function to test database connection
function testDatabaseConnection() {
    try {
        $pdo = getDatabaseConnection();
        if ($pdo) {
            return ['success' => true, 'message' => 'Database connection successful'];
        } else {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Function to test user login
function testUserLogin($email, $password) {
    try {
        $pdo = getDatabaseConnection();
        if (!$pdo) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return [
                'success' => true, 
                'message' => 'User login successful',
                'user' => [
                    'user_id' => $user['user_id'],
                    'name' => $user['first_name'] . ' ' . $user['last_name'],
                    'email' => $user['email']
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid user credentials'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Function to test admin login
function testAdminLogin($email, $password) {
    // Test default admin credentials
    if ($email === 'admin@gmail.com' && $password === 'admin1234') {
        return [
            'success' => true, 
            'message' => 'Admin login successful (default credentials)',
            'admin' => [
                'admin_id' => 'ADMIN001',
                'name' => 'Administrator',
                'email' => $email,
                'role' => 'super_admin'
            ]
        ];
    }
    
    // Test database admin users
    try {
        $pdo = getDatabaseConnection();
        if (!$pdo) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            return [
                'success' => true, 
                'message' => 'Admin login successful (database)',
                'admin' => [
                    'admin_id' => $admin['admin_id'],
                    'name' => $admin['first_name'] . ' ' . $admin['last_name'],
                    'email' => $admin['email'],
                    'role' => $admin['role']
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid admin credentials'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Handle form submissions
$testResults = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['test_database'])) {
        $testResults['database'] = testDatabaseConnection();
    }
    
    if (isset($_POST['test_user_login'])) {
        $email = $_POST['user_email'] ?? '';
        $password = $_POST['user_password'] ?? '';
        $testResults['user_login'] = testUserLogin($email, $password);
    }
    
    if (isset($_POST['test_admin_login'])) {
        $email = $_POST['admin_email'] ?? '';
        $password = $_POST['admin_password'] ?? '';
        $testResults['admin_login'] = testAdminLogin($email, $password);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Authentication System - VanGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .test-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
        }
        
        .test-section h3 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .test-form {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }
        
        .form-group input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .test-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .test-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .result {
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .result.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .result.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .result.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .credentials-info {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .credentials-info h4 {
            margin-bottom: 10px;
            color: #856404;
        }
        
        .credentials-info ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .credentials-info li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1><i class="fas fa-shield-alt"></i> Authentication System Test</h1>
        <p>Test both user and admin login functionality</p>
        
        <div class="credentials-info">
            <h4><i class="fas fa-info-circle"></i> Default Credentials</h4>
            <ul>
                <li><strong>Admin Login:</strong> admin@gmail.com / admin1234</li>
                <li><strong>User Login:</strong> Create a user account first via signup</li>
            </ul>
        </div>
        
        <!-- Database Connection Test -->
        <div class="test-section">
            <h3><i class="fas fa-database"></i> Database Connection Test</h3>
            <form method="POST" class="test-form">
                <button type="submit" name="test_database" class="test-btn">
                    <i class="fas fa-plug"></i> Test Database Connection
                </button>
            </form>
            <?php if (isset($testResults['database'])): ?>
                <div class="result <?php echo $testResults['database']['success'] ? 'success' : 'error'; ?>">
                    <strong><?php echo $testResults['database']['success'] ? '✓' : '✗'; ?></strong>
                    <?php echo htmlspecialchars($testResults['database']['message']); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- User Login Test -->
        <div class="test-section">
            <h3><i class="fas fa-user"></i> User Login Test</h3>
            <form method="POST" class="test-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="user_email">Email:</label>
                        <input type="email" id="user_email" name="user_email" placeholder="user@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="user_password">Password:</label>
                        <input type="password" id="user_password" name="user_password" placeholder="Password" required>
                    </div>
                </div>
                <button type="submit" name="test_user_login" class="test-btn">
                    <i class="fas fa-sign-in-alt"></i> Test User Login
                </button>
            </form>
            <?php if (isset($testResults['user_login'])): ?>
                <div class="result <?php echo $testResults['user_login']['success'] ? 'success' : 'error'; ?>">
                    <strong><?php echo $testResults['user_login']['success'] ? '✓' : '✗'; ?></strong>
                    <?php echo htmlspecialchars($testResults['user_login']['message']); ?>
                    <?php if ($testResults['user_login']['success'] && isset($testResults['user_login']['user'])): ?>
                        <br><small>User: <?php echo htmlspecialchars($testResults['user_login']['user']['name']); ?> (<?php echo htmlspecialchars($testResults['user_login']['user']['email']); ?>)</small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Admin Login Test -->
        <div class="test-section">
            <h3><i class="fas fa-user-shield"></i> Admin Login Test</h3>
            <form method="POST" class="test-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="admin_email">Admin Email:</label>
                        <input type="email" id="admin_email" name="admin_email" placeholder="admin@gmail.com" value="admin@gmail.com">
                    </div>
                    <div class="form-group">
                        <label for="admin_password">Admin Password:</label>
                        <input type="password" id="admin_password" name="admin_password" placeholder="admin1234" value="admin1234">
                    </div>
                </div>
                <button type="submit" name="test_admin_login" class="test-btn">
                    <i class="fas fa-user-shield"></i> Test Admin Login
                </button>
            </form>
            <?php if (isset($testResults['admin_login'])): ?>
                <div class="result <?php echo $testResults['admin_login']['success'] ? 'success' : 'error'; ?>">
                    <strong><?php echo $testResults['admin_login']['success'] ? '✓' : '✗'; ?></strong>
                    <?php echo htmlspecialchars($testResults['admin_login']['message']); ?>
                    <?php if ($testResults['admin_login']['success'] && isset($testResults['admin_login']['admin'])): ?>
                        <br><small>Admin: <?php echo htmlspecialchars($testResults['admin_login']['admin']['name']); ?> (<?php echo htmlspecialchars($testResults['admin_login']['admin']['email']); ?>) - Role: <?php echo htmlspecialchars($testResults['admin_login']['admin']['role']); ?></small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Navigation -->
        <div class="test-section">
            <h3><i class="fas fa-link"></i> Quick Navigation</h3>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="auth.html" class="test-btn" style="text-decoration: none; display: inline-block;">
                    <i class="fas fa-sign-in-alt"></i> Go to Login Page
                </a>
                <a href="admin-dashboard.php" class="test-btn" style="text-decoration: none; display: inline-block;">
                    <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                </a>
                <a href="index.php" class="test-btn" style="text-decoration: none; display: inline-block;">
                    <i class="fas fa-home"></i> Home Page
                </a>
            </div>
        </div>
    </div>
</body>
</html> 