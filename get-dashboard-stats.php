<?php
/**
 * Dashboard Statistics API
 * Provides statistics for the admin dashboard
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
    
    // Get total bookings
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $totalBookings = $stmt->fetch()['count'] ?? 0;
    
    // Get total vans
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM vans");
    $totalVans = $stmt->fetch()['count'] ?? 0;
    
    // Get total customers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $totalCustomers = $stmt->fetch()['count'] ?? 0;
    
    // Get total revenue
    $stmt = $pdo->query("SELECT SUM(total_amount) as revenue FROM bookings WHERE status IN ('confirmed', 'completed')");
    $totalRevenue = $stmt->fetch()['revenue'] ?? 0;
    
    // Get recent bookings
    $stmt = $pdo->query("
        SELECT b.booking_id, b.contact_name as customer_name, b.status, b.created_at
        FROM bookings b 
        ORDER BY b.created_at DESC 
        LIMIT 5
    ");
    $recentBookings = $stmt->fetchAll();
    
    // Get van availability
    $stmt = $pdo->query("
        SELECT van_id, name, type, status 
        FROM vans 
        ORDER BY status, name 
        LIMIT 10
    ");
    $vanAvailability = $stmt->fetchAll();
    
    $stats = [
        'total_bookings' => $totalBookings,
        'total_vans' => $totalVans,
        'total_customers' => $totalCustomers,
        'total_revenue' => number_format($totalRevenue, 2)
    ];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'recent_bookings' => $recentBookings,
        'van_availability' => $vanAvailability
    ]);
    
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading dashboard data: ' . $e->getMessage()
    ]);
}
?>