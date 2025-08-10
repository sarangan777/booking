<?php
/**
 * Customers API for Admin Dashboard
 * Retrieves customer data for admin management
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once 'database.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Get optional search filter
    $search = $_GET['search'] ?? '';
    
    // Build query
    $sql = "
        SELECT u.*,
               COUNT(b.id) as total_bookings,
               SUM(b.total_amount) as total_spent,
               MAX(b.created_at) as last_booking
        FROM users u
        LEFT JOIN bookings b ON u.user_id = b.user_id
        WHERE 1=1
    ";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " GROUP BY u.id ORDER BY u.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'customers' => $customers,
        'count' => count($customers)
    ]);
    
} catch (Exception $e) {
    error_log("Get customers error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading customers: ' . $e->getMessage()
    ]);
}
?>