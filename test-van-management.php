<?php
/**
 * Test Van Management System
 * Tests the van management functionality for admin
 */

session_start();
require_once 'database.php';

// Set content type
header('Content-Type: text/html; charset=UTF-8');

// Check if admin is logged in
$isAdminLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

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

// Function to get all vans
function getAllVans() {
    try {
        $pdo = getDatabaseConnection();
        if (!$pdo) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        $stmt = $pdo->query("SELECT * FROM vans ORDER BY type, daily_rate");
        $vans = $stmt->fetchAll();
        
        return [
            'success' => true, 
            'message' => 'Vans loaded successfully',
            'vans' => $vans,
            'count' => count($vans)
        ];
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
    
    if (isset($_POST['test_vans'])) {
        $testResults['vans'] = getAllVans();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Van Management - VanGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .test-container {
            max-width: 1200px;
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
        
        .test-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .test-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .test-btn.secondary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .test-btn.danger {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
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
        
        .result.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .result.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .vans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .van-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
        }
        
        .van-card h4 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .van-card .price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .van-card .features {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin: 10px 0;
        }
        
        .feature-badge {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        
        .navigation-links {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .navigation-links a {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .navigation-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .admin-login-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .admin-login-section h3 {
            color: #856404;
            margin-bottom: 15px;
        }
        
        .admin-login-section p {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .admin-login-section .credentials {
            background: rgba(255, 255, 255, 0.5);
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1><i class="fas fa-van-shuttle"></i> Van Management System Test</h1>
        <p>Test the van management functionality for administrators</p>
        
        <?php if (!$isAdminLoggedIn): ?>
            <div class="admin-login-section">
                <h3><i class="fas fa-exclamation-triangle"></i> Admin Login Required</h3>
                <p>You need to be logged in as an admin to test van management features.</p>
                <p><strong>Default Admin Credentials:</strong></p>
                <div class="credentials">
                    Email: admin@gmail.com<br>
                    Password: admin1234
                </div>
                <div class="navigation-links">
                    <a href="auth.html">
                        <i class="fas fa-sign-in-alt"></i> Go to Login Page
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="result success">
                <strong><i class="fas fa-check-circle"></i> Admin Logged In</strong><br>
                Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator'); ?>!
            </div>
        <?php endif; ?>
        
        <!-- Database Connection Test -->
        <div class="test-section">
            <h3><i class="fas fa-database"></i> Database Connection Test</h3>
            <form method="POST" style="display: inline;">
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
        
        <!-- Van Loading Test -->
        <div class="test-section">
            <h3><i class="fas fa-van-shuttle"></i> Van Loading Test</h3>
            <form method="POST" style="display: inline;">
                <button type="submit" name="test_vans" class="test-btn">
                    <i class="fas fa-list"></i> Load All Vans
                </button>
            </form>
            <?php if (isset($testResults['vans'])): ?>
                <div class="result <?php echo $testResults['vans']['success'] ? 'success' : 'error'; ?>">
                    <strong><?php echo $testResults['vans']['success'] ? '✓' : '✗'; ?></strong>
                    <?php echo htmlspecialchars($testResults['vans']['message']); ?>
                    <?php if ($testResults['vans']['success']): ?>
                        <br><strong>Found <?php echo $testResults['vans']['count']; ?> vans in database</strong>
                    <?php endif; ?>
                </div>
                
                <?php if ($testResults['vans']['success'] && !empty($testResults['vans']['vans'])): ?>
                    <div class="vans-grid">
                        <?php foreach ($testResults['vans']['vans'] as $van): ?>
                            <div class="van-card">
                                <h4>
                                    <i class="fas fa-van-shuttle"></i>
                                    <?php echo htmlspecialchars($van['type']); ?> Van
                                </h4>
                                <div class="price">$<?php echo number_format($van['daily_rate']); ?>/day</div>
                                <p><strong>Seats:</strong> <?php echo $van['seats']; ?></p>
                                <p><strong>Status:</strong> 
                                    <span style="color: <?php echo $van['status'] === 'available' ? '#28a745' : '#ffc107'; ?>;">
                                        <?php echo ucfirst($van['status']); ?>
                                    </span>
                                </p>
                                <div class="features">
                                    <?php if ($van['ac']): ?>
                                        <span class="feature-badge"><i class="fas fa-snowflake"></i> AC</span>
                                    <?php endif; ?>
                                    <?php if ($van['wifi']): ?>
                                        <span class="feature-badge"><i class="fas fa-wifi"></i> WiFi</span>
                                    <?php endif; ?>
                                    <?php if ($van['gps']): ?>
                                        <span class="feature-badge"><i class="fas fa-map-marker-alt"></i> GPS</span>
                                    <?php endif; ?>
                                </div>
                                <p style="margin-top: 10px; font-size: 0.9rem; color: #666;">
                                    ID: <?php echo htmlspecialchars($van['van_id']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- API Testing -->
        <?php if ($isAdminLoggedIn): ?>
            <div class="test-section">
                <h3><i class="fas fa-code"></i> API Testing</h3>
                <div class="result info">
                    <p><strong>Available API Endpoints:</strong></p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li><strong>GET manage-vans.php</strong> - Get all vans</li>
                        <li><strong>GET manage-vans.php?id=VAN_ID</strong> - Get specific van</li>
                        <li><strong>POST manage-vans.php</strong> - Add new van</li>
                        <li><strong>PUT manage-vans.php?id=VAN_ID</strong> - Update van</li>
                        <li><strong>DELETE manage-vans.php?id=VAN_ID</strong> - Delete van</li>
                    </ul>
                </div>
                <button class="test-btn secondary" onclick="testGetVans()">
                    <i class="fas fa-download"></i> Test GET API
                </button>
                <button class="test-btn" onclick="testAddVan()">
                    <i class="fas fa-plus"></i> Test Add Van
                </button>
                <div id="api-results"></div>
            </div>
        <?php endif; ?>
        
        <!-- Navigation -->
        <div class="test-section">
            <h3><i class="fas fa-link"></i> Quick Navigation</h3>
            <div class="navigation-links">
                <a href="admin-dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                </a>
                <a href="book-van.php">
                    <i class="fas fa-calendar-plus"></i> Book Van Page
                </a>
                <a href="auth.html">
                    <i class="fas fa-sign-in-alt"></i> Login Page
                </a>
                <a href="index.php">
                    <i class="fas fa-home"></i> Home Page
                </a>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="test-section">
            <h3><i class="fas fa-info-circle"></i> How to Test Van Management</h3>
            <div class="result info">
                <ol style="margin: 0; padding-left: 20px;">
                    <li><strong>Login as Admin:</strong> Use the default admin credentials to log in</li>
                    <li><strong>Access Admin Dashboard:</strong> Go to the admin dashboard to manage vans</li>
                    <li><strong>Add Vans:</strong> Click "Add New Van" to create new van entries</li>
                    <li><strong>Edit Vans:</strong> Click "Edit" on any van to modify its details</li>
                    <li><strong>Delete Vans:</strong> Click "Delete" to remove vans (only if no active bookings)</li>
                    <li><strong>Test API:</strong> Use the API testing buttons to verify backend functionality</li>
                </ol>
            </div>
        </div>
    </div>

    <script>
        // API Testing Functions
        function testGetVans() {
            const resultsDiv = document.getElementById('api-results');
            resultsDiv.innerHTML = '<div class="result info">Testing GET API...</div>';
            
            fetch('manage-vans.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultsDiv.innerHTML = `
                            <div class="result success">
                                <strong>✓ GET API Test Successful</strong><br>
                                Retrieved ${data.count} vans from database
                            </div>
                        `;
                    } else {
                        resultsDiv.innerHTML = `
                            <div class="result error">
                                <strong>✗ GET API Test Failed</strong><br>
                                ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    resultsDiv.innerHTML = `
                        <div class="result error">
                            <strong>✗ GET API Test Failed</strong><br>
                            Error: ${error.message}
                        </div>
                    `;
                });
        }
        
        function testAddVan() {
            const resultsDiv = document.getElementById('api-results');
            resultsDiv.innerHTML = '<div class="result info">Testing Add Van API...</div>';
            
            const testVan = {
                type: 'Test Van',
                seats: 6,
                daily_rate: 150.00,
                status: 'available',
                ac: true,
                wifi: false,
                gps: true
            };
            
            fetch('manage-vans.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(testVan)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultsDiv.innerHTML = `
                        <div class="result success">
                            <strong>✓ Add Van API Test Successful</strong><br>
                            Test van added with ID: ${data.van.van_id}
                        </div>
                    `;
                } else {
                    resultsDiv.innerHTML = `
                        <div class="result error">
                            <strong>✗ Add Van API Test Failed</strong><br>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultsDiv.innerHTML = `
                    <div class="result error">
                        <strong>✗ Add Van API Test Failed</strong><br>
                        Error: ${error.message}
                    </div>
                `;
            });
        }
    </script>
</body>
</html> 