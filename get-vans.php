<?php
/**
 * VanGo Get Available Vans
 * Retrieves available vans from database for booking
 */

session_start();
require_once 'database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Allow both GET and POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Get query parameters
    $type = $_GET['type'] ?? '';
    $seats = $_GET['seats'] ?? '';
    $capacity = $_GET['capacity'] ?? '';
    $date = $_GET['date'] ?? '';
    
    // Build query
    $sql = "SELECT * FROM vans WHERE status = 'available'";
    $params = [];
    
    if (!empty($type)) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    
    if (!empty($seats)) {
        $sql .= " AND seats >= ?";
        $params[] = $seats;
    } elseif (!empty($capacity)) {
        $sql .= " AND capacity >= ?";
        $params[] = $capacity;
    }
    
    $sql .= " ORDER BY daily_rate ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vans = $stmt->fetchAll();
    
    // If date is provided, check availability
    if (!empty($date) && !empty($vans)) {
        $availableVans = [];
        
        foreach ($vans as $van) {
            // Check if van has any conflicting bookings on the specified date
            $conflictStmt = $pdo->prepare("
                SELECT COUNT(*) as count FROM bookings 
                WHERE van_id = ? AND status IN ('pending', 'confirmed', 'in_progress')
                AND ? BETWEEN pickup_date AND return_date
            ");
            $conflictStmt->execute([$van['van_id'], $date]);
            $conflicts = $conflictStmt->fetch();
            
            if ($conflicts['count'] == 0) {
                $availableVans[] = $van;
            }
        }
        
        $vans = $availableVans;
    }
    
    // Format van data for response
    $formattedVans = [];
    foreach ($vans as $van) {
        $formattedVans[] = [
            'van_id' => $van['van_id'],
            'name' => $van['name'],
            'type' => $van['type'],
            'model' => $van['model'],
            'year' => $van['year'],
            'seats' => $van['seats'],
            'capacity' => $van['capacity'],
            'daily_rate' => number_format($van['daily_rate'], 2),
            'hourly_rate' => number_format($van['hourly_rate'], 2),
            'description' => $van['description'],
            'features' => json_decode($van['features'], true) ?? [],
            'location' => $van['location'],
            'status' => $van['status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'vans' => $formattedVans,
        'count' => count($formattedVans)
    ]);
    
} catch (Exception $e) {
    error_log("Get vans error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?> 