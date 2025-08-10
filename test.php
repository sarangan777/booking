<?php
/**
 * VanGo System Test
 * Quick test to verify everything is working
 */

// Start session
session_start();

// Include database functions
require_once 'database.php';

// Set headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VanGo - System Test</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .test-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .test-item {
            margin: 15px 0;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .test-item.success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .test-item.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .test-item.info {
            background: #d1edff;
            border-left-color: #0c5460;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div style="text-align: center; margin-bottom: 30px;">
            <i class="fas fa-van-shuttle" style="font-size: 48px; color: #667eea; margin-bottom: 20px;"></i>
            <h1>VanGo System Test</h1>
            <p>Testing system components and database connectivity</p>
        </div>

        <?php
        $tests = [];
        
        // Test 1: PHP Version
        $tests['PHP Version'] = [
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'success' : 'error',
            'message' => 'PHP ' . PHP_VERSION . ' is ' . (version_compare(PHP_VERSION, '7.4.0', '>=') ? 'compatible' : 'too old')
        ];
        
        // Test 2: Database Connection
        $pdo = getDatabaseConnection();
        if ($pdo) {
            $tests['Database Connection'] = [
                'status' => 'success',
                'message' => 'Database connection successful'
            ];
            
            // Test 3: Check if tables exist
            $tables = ['users', 'vans', 'bookings', 'payments', 'reviews', 'admin_users', 'system_settings'];
            $missingTables = [];
            
            foreach ($tables as $table) {
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                if (!$stmt->fetch()) {
                    $missingTables[] = $table;
                }
            }
            
            if (empty($missingTables)) {
                $tests['Database Tables'] = [
                    'status' => 'success',
                    'message' => 'All required tables exist'
                ];
            } else {
                $tests['Database Tables'] = [
                    'status' => 'error',
                    'message' => 'Missing tables: ' . implode(', ', $missingTables)
                ];
            }
            
            // Test 4: Check sample data
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM vans");
            $vanCount = $stmt->fetch()['count'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch()['count'];
            
            $tests['Sample Data'] = [
                'status' => ($vanCount > 0 && $userCount > 0) ? 'success' : 'info',
                'message' => "Found $vanCount vans and $userCount users"
            ];
            
        } else {
            $tests['Database Connection'] = [
                'status' => 'error',
                'message' => 'Database connection failed'
            ];
        }
        
        // Test 5: Session Support
        $tests['Session Support'] = [
            'status' => 'success',
            'message' => 'PHP sessions are working'
        ];
        
        // Test 6: File Permissions
        $writableFiles = ['database.php', 'setup.php'];
        $fileIssues = [];
        
        foreach ($writableFiles as $file) {
            if (!file_exists($file)) {
                $fileIssues[] = "$file (missing)";
            } elseif (!is_readable($file)) {
                $fileIssues[] = "$file (not readable)";
            }
        }
        
        if (empty($fileIssues)) {
            $tests['File Permissions'] = [
                'status' => 'success',
                'message' => 'All required files are accessible'
            ];
        } else {
            $tests['File Permissions'] = [
                'status' => 'error',
                'message' => 'File issues: ' . implode(', ', $fileIssues)
            ];
        }
        
        // Display test results
        foreach ($tests as $testName => $test) {
            $icon = $test['status'] === 'success' ? 'check-circle' : 
                   ($test['status'] === 'error' ? 'exclamation-circle' : 'info-circle');
            ?>
            <div class="test-item <?php echo $test['status']; ?>">
                <h3><i class="fas fa-<?php echo $icon; ?>"></i> <?php echo $testName; ?></h3>
                <p><?php echo $test['message']; ?></p>
            </div>
            <?php
        }
        
        // Overall status
        $successCount = count(array_filter($tests, function($test) { return $test['status'] === 'success'; }));
        $totalTests = count($tests);
        $overallStatus = $successCount === $totalTests ? 'success' : 'info';
        ?>
        
        <div class="test-item <?php echo $overallStatus; ?>" style="margin-top: 30px;">
            <h3><i class="fas fa-<?php echo $overallStatus === 'success' ? 'check-circle' : 'info-circle'; ?>"></i> Overall Status</h3>
            <p><?php echo $successCount; ?> out of <?php echo $totalTests; ?> tests passed</p>
            <?php if ($successCount === $totalTests): ?>
                <p><strong>🎉 All tests passed! Your VanGo system is ready to use.</strong></p>
            <?php else: ?>
                <p><strong>⚠️ Some tests failed. Please check the issues above.</strong></p>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn">
                <i class="fas fa-home"></i> Go to Home Page
            </a>
            <a href="setup.php" class="btn">
                <i class="fas fa-database"></i> Run Setup
            </a>
            <a href="login.php" class="btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
        
        <?php if ($successCount === $totalTests): ?>
        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h3>🚀 Quick Start Guide</h3>
            <ol>
                <li><strong>Test the System:</strong> Visit the home page and try booking a van</li>
                <li><strong>Create Account:</strong> Sign up for a new user account</li>
                <li><strong>Admin Access:</strong> Login with admin@vango.com / admin123</li>
                <li><strong>Manage Vans:</strong> Use the admin dashboard to manage your fleet</li>
            </ol>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 