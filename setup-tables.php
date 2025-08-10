<?php
/**
 * Tables Creation Endpoint
 * Creates all required tables for VanGo
 */

// Include database functions
require_once 'database.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Create tables
    $result = createTables();
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Tables created successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create tables'
        ]);
    }
} catch (Exception $e) {
    error_log("Tables creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while creating the tables'
    ]);
}
?> 