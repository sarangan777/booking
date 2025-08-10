<?php
/**
 * Bookings API for Admin Dashboard
 * Retrieves booking data for admin management
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
    
    // Get optional filters
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    // Build query
    $sql = "
        SELECT b.*, v.name as van_name, v.type as van_type
        FROM bookings b
        LEFT JOIN vans v ON b.van_id = v.van_id
        WHERE 1=1
    ";
    $params = [];
    
    if (!empty($status)) {
        $sql .= " AND b.status = ?";
        $params[] = $status;
    }
    
    if (!empty($search)) {
        $sql .= " AND (b.contact_name LIKE ? OR b.contact_email LIKE ? OR b.booking_id LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " ORDER BY b.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'bookings' => $bookings,
        'count' => count($bookings)
    ]);
    
} catch (Exception $e) {
    error_log("Get bookings error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading bookings: ' . $e->getMessage()
    ]);
}
?>