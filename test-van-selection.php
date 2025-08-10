<?php
/**
 * Test Van Selection System
 * Tests the van selection functionality
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

// Function to get available vans
function getAvailableVans() {
    try {
        $pdo = getDatabaseConnection();
        if (!$pdo) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        $stmt = $pdo->query("SELECT * FROM vans WHERE status = 'available' ORDER BY type, daily_rate");
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
        $testResults['vans'] = getAvailableVans();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Van Selection - VanGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .test-container {
            max-width: 1000px;
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
        
        .vans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .van-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .van-card h4 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .van-card .price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .van-card .features {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            justify-content: center;
            margin-top: 10px;
        }
        
        .feature-badge {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
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
    </style>
</head>
<body>
    <div class="test-container">
        <h1><i class="fas fa-van-shuttle"></i> Van Selection System Test</h1>
        <p>Test the van selection functionality and database integration</p>
        
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
                    <i class="fas fa-list"></i> Load Available Vans
                </button>
            </form>
            <?php if (isset($testResults['vans'])): ?>
                <div class="result <?php echo $testResults['vans']['success'] ? 'success' : 'error'; ?>">
                    <strong><?php echo $testResults['vans']['success'] ? '✓' : '✗'; ?></strong>
                    <?php echo htmlspecialchars($testResults['vans']['message']); ?>
                    <?php if ($testResults['vans']['success']): ?>
                        <br><strong>Found <?php echo $testResults['vans']['count']; ?> available vans</strong>
                    <?php endif; ?>
                </div>
                
                <?php if ($testResults['vans']['success'] && !empty($testResults['vans']['vans'])): ?>
                    <div class="vans-grid">
                        <?php foreach ($testResults['vans']['vans'] as $van): ?>
                            <div class="van-card">
                                <h4><?php echo htmlspecialchars($van['type']); ?> Van</h4>
                                <div class="price">$<?php echo number_format($van['daily_rate']); ?>/day</div>
                                <p><strong><?php echo $van['seats']; ?> seats</strong></p>
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
        
        <!-- Navigation -->
        <div class="test-section">
            <h3><i class="fas fa-link"></i> Quick Navigation</h3>
            <div class="navigation-links">
                <a href="book-van.php">
                    <i class="fas fa-calendar-plus"></i> Book Van Page
                </a>
                <a href="vans.html">
                    <i class="fas fa-van-shuttle"></i> Our Vans Page
                </a>
                <a href="index.php">
                    <i class="fas fa-home"></i> Home Page
                </a>
                <a href="auth.html">
                    <i class="fas fa-sign-in-alt"></i> Login Page
                </a>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="test-section">
            <h3><i class="fas fa-info-circle"></i> How to Test Van Selection</h3>
            <div class="result info">
                <ol style="margin: 0; padding-left: 20px;">
                    <li><strong>Test Database:</strong> Click "Test Database Connection" to verify database connectivity</li>
                    <li><strong>Load Vans:</strong> Click "Load Available Vans" to see vans from the database</li>
                    <li><strong>Book Van:</strong> Go to the "Book Van Page" to test the actual van selection interface</li>
                    <li><strong>Select Van:</strong> Click on any van option to see the selection functionality</li>
                    <li><strong>Complete Booking:</strong> Fill out the form and test the booking process</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html> 