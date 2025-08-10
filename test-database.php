<?php
// Test database connection
require_once 'database.php';

header('Content-Type: application/json');

try {
    $pdo = getDatabaseConnection();
    if ($pdo) {
        echo json_encode([
            'success' => true,
            'message' => 'Database connection successful',
            'database' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 