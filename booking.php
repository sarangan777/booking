<?php
/**
 * VanGo Booking System - Booking Processing
 * Handles booking form submissions and API requests with database storage
 */

// Start session
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';

// Include database functions
require_once 'database.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests for booking submissions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please login to make a booking. You can use test@example.com / password123 to test the system.',
        'login_required' => true
    ]);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        exit();
    }

    // Extract booking data
    $bookingData = [
        'user_id' => $_SESSION['user_id'],
        'van_id' => $input['vanId'] ?? '',
        'pickup_location' => trim($input['pickupLocation'] ?? ''),
        'dropoff_location' => trim($input['dropoffLocation'] ?? ''),
        'pickup_date' => $input['pickupDate'] ?? '',
        'pickup_time' => $input['pickupTime'] ?? '',
        'return_date' => $input['returnDate'] ?? null,
        'return_time' => $input['returnTime'] ?? null,
        'passengers' => intval($input['passengers'] ?? 1),
        'special_requests' => trim($input['specialRequests'] ?? ''),
        'conduct_details' => trim($input['conductDetails'] ?? ''),
        'contact_name' => trim($input['contactName'] ?? ''),
        'contact_email' => trim($input['contactEmail'] ?? ''),
        'contact_phone' => trim($input['contactPhone'] ?? ''),
        'additional_services' => $input['additionalServices'] ?? []
    ];

    // Validate booking data
    $errors = validateBookingData($bookingData);
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        exit();
    }

    // Get database connection
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Check if van exists and is available
    $vanStmt = $pdo->prepare("SELECT * FROM vans WHERE van_id = ? AND status = 'available'");
    $vanStmt->execute([$bookingData['van_id']]);
    $van = $vanStmt->fetch();
    
    if (!$van) {
        echo json_encode(['success' => false, 'message' => 'Selected van is not available']);
        exit();
    }
    
    // Check for booking conflicts
    $conflictStmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM bookings 
        WHERE van_id = ? AND status IN ('pending', 'confirmed', 'in_progress')
        AND (
            (pickup_date <= ? AND return_date >= ?) OR
            (pickup_date <= ? AND return_date >= ?) OR
            (pickup_date >= ? AND return_date <= ?)
        )
    ");
    $conflictStmt->execute([
        $bookingData['van_id'],
        $bookingData['pickup_date'], $bookingData['pickup_date'],
        $bookingData['return_date'], $bookingData['return_date'],
        $bookingData['pickup_date'], $bookingData['return_date']
    ]);
    
    $conflicts = $conflictStmt->fetch();
    if ($conflicts['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Van is not available for the selected dates']);
        exit();
    }
    
    // Calculate total amount
    $pickupDateTime = new DateTime($bookingData['pickup_date'] . ' ' . $bookingData['pickup_time']);
    $returnDateTime = new DateTime($bookingData['return_date'] . ' ' . $bookingData['return_time']);
    $duration = $pickupDateTime->diff($returnDateTime);
    $days = max(1, $duration->days + ($duration->h > 0 ? 1 : 0));
    
    $baseAmount = $van['daily_rate'] * $days;
    $additionalServicesCost = calculateAdditionalServicesCost($bookingData['additional_services']);
    $totalAmount = $baseAmount + $additionalServicesCost;
    
    // Generate booking ID
    $bookingId = 'B' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    // === Insert booking details into the bookings table ===
    $stmt = $pdo->prepare("
        INSERT INTO bookings (
            booking_id, user_id, van_id, pickup_location, dropoff_location,
            pickup_date, pickup_time, return_date, return_time, passengers,
            total_amount, special_requests, conduct_details, contact_name, contact_email, contact_phone, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)
    ");
    
    $stmt->execute([
        $bookingId,
        $bookingData['user_id'],
        $bookingData['van_id'],
        $bookingData['pickup_location'],
        $bookingData['dropoff_location'],
        $bookingData['pickup_date'],
        $bookingData['pickup_time'],
        $bookingData['return_date'] ?: null,
        $bookingData['return_time'] ?: null,
        $bookingData['passengers'],
        $totalAmount,
        $bookingData['special_requests'],
        $bookingData['conduct_details'],
        $bookingData['contact_name'],
        $bookingData['contact_email'],
        $bookingData['contact_phone']
    ]);
    
    $bookingDbId = $pdo->lastInsertId();
    
    // Log successful booking
    $userEmail = $_SESSION['user_email'] ?? $_SESSION['user_id'] ?? 'unknown';
    error_log("Booking created: {$bookingId} by user {$userEmail} at " . date('Y-m-d H:i:s'));
    
    // Prepare response data
    $bookingResponse = [
        'booking_id' => $bookingId,
        'van_id' => $van['van_id'],
        'van_name' => $van['name'],
        'van_type' => $van['type'],
        'pickup_location' => $bookingData['pickup_location'],
        'dropoff_location' => $bookingData['dropoff_location'],
        'pickup_date' => $bookingData['pickup_date'],
        'pickup_time' => $bookingData['pickup_time'],
        'return_date' => $bookingData['return_date'],
        'return_time' => $bookingData['return_time'],
        'passengers' => $bookingData['passengers'],
        'total_amount' => number_format($totalAmount, 2),
        'status' => 'pending',
        'duration_days' => $days,
        'special_requests' => $bookingData['special_requests'],
        'conduct_details' => $bookingData['conduct_details'],
        'contact_name' => $bookingData['contact_name'],
        'contact_email' => $bookingData['contact_email'],
        'contact_phone' => $bookingData['contact_phone']
    ];
    
    echo json_encode([
        'success' => true,
        'message' => '🎉 Booking confirmed successfully! Your van is reserved and ready for your trip.',
        'booking' => $bookingResponse
    ]);

} catch (Exception $e) {
    error_log("Booking error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred during booking. Please try again.',
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}

/**
 * Validate booking data
 */
function validateBookingData($data) {
    $errors = [];
    
    // Required field validation
    if (empty($data['van_id'])) {
        $errors[] = 'Van selection is required';
    }
    
    if (empty($data['pickup_location'])) {
        $errors[] = 'Pickup location is required';
    }
    
    if (empty($data['dropoff_location'])) {
        $errors[] = 'Dropoff location is required';
    }
    
    if (empty($data['pickup_date'])) {
        $errors[] = 'Pickup date is required';
    }
    
    if (empty($data['pickup_time'])) {
        $errors[] = 'Pickup time is required';
    }
    
    if (empty($data['return_date'])) {
        $errors[] = 'Return date is required';
    }
    
    if (empty($data['return_time'])) {
        $errors[] = 'Return time is required';
    }
    
    if (empty($data['passengers']) || intval($data['passengers']) < 1) {
        $errors[] = 'At least 1 passenger is required';
    }
    
    // Date and time validation
    if (!empty($data['pickup_date']) && !empty($data['return_date'])) {
        try {
            $pickupDate = new DateTime($data['pickup_date']);
            $returnDate = new DateTime($data['return_date']);
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            
            if ($pickupDate < $today) {
                $errors[] = 'Pickup date cannot be in the past';
            }
            
            if ($returnDate < $pickupDate) {
                $errors[] = 'Return date must be after pickup date';
            }
            
            // Check if booking is within reasonable future (e.g., 1 year)
            $maxFutureDate = new DateTime();
            $maxFutureDate->add(new DateInterval('P1Y'));
            if ($pickupDate > $maxFutureDate) {
                $errors[] = 'Pickup date cannot be more than 1 year in the future';
            }
            
        } catch (Exception $e) {
            $errors[] = 'Invalid date format';
        }
    }
    
    // Time validation
    if (!empty($data['pickup_time']) && !empty($data['return_time'])) {
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['pickup_time'])) {
            $errors[] = 'Invalid pickup time format (use HH:MM)';
        }
        
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['return_time'])) {
            $errors[] = 'Invalid return time format (use HH:MM)';
        }
    }
    
    // Location validation
    if (!empty($data['pickup_location']) && strlen($data['pickup_location']) < 3) {
        $errors[] = 'Pickup location must be at least 3 characters long';
    }
    
    if (!empty($data['dropoff_location']) && strlen($data['dropoff_location']) < 3) {
        $errors[] = 'Dropoff location must be at least 3 characters long';
    }
    
    // Passenger validation
    if (!empty($data['passengers'])) {
        $passengers = intval($data['passengers']);
        if ($passengers < 1 || $passengers > 20) {
            $errors[] = 'Number of passengers must be between 1 and 20';
        }
    }
    
    return $errors;
}

/**
 * Calculate additional services cost
 */
function calculateAdditionalServicesCost($services) {
    $cost = 0;
    $servicePrices = [
        'insurance' => 25.00,
        'driver' => 50.00,
        'wifi' => 15.00,
        'refreshments' => 20.00,
        'child_seat' => 10.00
    ];
    
    foreach ($services as $service) {
        if (isset($servicePrices[$service])) {
            $cost += $servicePrices[$service];
        }
    }
    
    return $cost;
} 