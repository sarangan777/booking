<?php
/**
 * Analytics API for Admin Dashboard
 * Provides data for charts and analytics
 */

session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'database.php';

try {
    $pdo = getDatabaseConnection();
    
    // Get analytics data for last 2 months
    $currentDate = date('Y-m-d');
    $twoMonthsAgo = date('Y-m-d', strtotime('-2 months'));
    
    // 1. Monthly Booking Statistics
    $bookingStats = [];
    for ($i = 1; $i >= 0; $i--) {
        $monthStart = date('Y-m-01', strtotime("-$i months"));
        $monthEnd = date('Y-m-t', strtotime("-$i months"));
        $monthName = date('M Y', strtotime("-$i months"));
        
        // Total bookings for the month
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_bookings,
                   SUM(daily_rate) as total_revenue,
                   COUNT(DISTINCT user_id) as unique_customers
            FROM bookings 
            WHERE DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$monthStart, $monthEnd]);
        $monthData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Daily breakdown for the month
        $stmt = $pdo->prepare("
            SELECT DATE(created_at) as date,
                   COUNT(*) as daily_bookings,
                   SUM(daily_rate) as daily_revenue
            FROM bookings 
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stmt->execute([$monthStart, $monthEnd]);
        $dailyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $bookingStats[] = [
            'month' => $monthName,
            'month_start' => $monthStart,
            'month_end' => $monthEnd,
            'total_bookings' => (int)$monthData['total_bookings'],
            'total_revenue' => (float)$monthData['total_revenue'],
            'unique_customers' => (int)$monthData['unique_customers'],
            'daily_data' => $dailyData
        ];
    }
    
    // 2. Van Usage Statistics
    $vanUsage = [];
    $stmt = $pdo->prepare("
        SELECT v.van_id, v.type, v.seats,
               COUNT(b.booking_id) as booking_count,
               SUM(b.daily_rate) as total_revenue,
               AVG(b.daily_rate) as avg_revenue
        FROM vans v
        LEFT JOIN bookings b ON v.van_id = b.van_id 
            AND DATE(b.created_at) BETWEEN ? AND ?
        WHERE v.status = 'available'
        GROUP BY v.van_id, v.type, v.seats
        ORDER BY booking_count DESC
    ");
    $stmt->execute([$twoMonthsAgo, $currentDate]);
    $vanUsage = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Revenue Trends (Last 60 days)
    $revenueTrends = [];
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as date,
               SUM(daily_rate) as daily_revenue,
               COUNT(*) as daily_bookings
        FROM bookings 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $stmt->execute([$twoMonthsAgo, $currentDate]);
    $revenueTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Customer Analytics
    $customerStats = [];
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT user_id) as total_customers,
            COUNT(DISTINCT CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN user_id END) as new_customers_30d,
            COUNT(DISTINCT CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN user_id END) as new_customers_7d
        FROM bookings 
        WHERE DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$twoMonthsAgo, $currentDate]);
    $customerStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 5. Top Performing Vans
    $topVans = [];
    $stmt = $pdo->prepare("
        SELECT v.type, v.seats,
               COUNT(b.booking_id) as bookings,
               SUM(b.daily_rate) as revenue,
               ROUND(AVG(b.daily_rate), 2) as avg_rate
        FROM vans v
        LEFT JOIN bookings b ON v.van_id = b.van_id 
            AND DATE(b.created_at) BETWEEN ? AND ?
        WHERE v.status = 'available'
        GROUP BY v.type, v.seats
        ORDER BY revenue DESC
        LIMIT 5
    ");
    $stmt->execute([$twoMonthsAgo, $currentDate]);
    $topVans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 6. Booking Status Distribution
    $bookingStatus = [];
    $stmt = $pdo->prepare("
        SELECT 
            status,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM bookings WHERE DATE(created_at) BETWEEN ? AND ?), 2) as percentage
        FROM bookings 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY status
        ORDER BY count DESC
    ");
    $stmt->execute([$twoMonthsAgo, $currentDate, $twoMonthsAgo, $currentDate]);
    $bookingStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 7. Peak Booking Days
    $peakDays = [];
    $stmt = $pdo->prepare("
        SELECT 
            DAYNAME(created_at) as day_name,
            COUNT(*) as bookings,
            SUM(daily_rate) as revenue
        FROM bookings 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY DAYNAME(created_at)
        ORDER BY bookings DESC
    ");
    $stmt->execute([$twoMonthsAgo, $currentDate]);
    $peakDays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 8. Average Booking Value Trends
    $avgBookingValue = [];
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            ROUND(AVG(daily_rate), 2) as avg_booking_value,
            COUNT(*) as total_bookings
        FROM bookings 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month
    ");
    $stmt->execute([$twoMonthsAgo, $currentDate]);
    $avgBookingValue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $analytics = [
        'period' => [
            'start_date' => $twoMonthsAgo,
            'end_date' => $currentDate,
            'months' => 2
        ],
        'booking_stats' => $bookingStats,
        'van_usage' => $vanUsage,
        'revenue_trends' => $revenueTrends,
        'customer_stats' => $customerStats,
        'top_vans' => $topVans,
        'booking_status' => $bookingStatus,
        'peak_days' => $peakDays,
        'avg_booking_value' => $avgBookingValue
    ];
    
    echo json_encode($analytics, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 