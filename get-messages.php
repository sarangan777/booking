<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'database.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Connect to database
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Get optional status filter
    $statusFilter = $_GET['status'] ?? '';
    
    // Build query
    $sql = "
        SELECT id, first_name, last_name, email, phone, subject, message, status, created_at
        FROM contact_messages
    ";
    $params = [];
    
    if (!empty($statusFilter)) {
        $sql .= " WHERE status = ?";
        $params[] = $statusFilter;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'count' => count($messages)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 