<?php
/**
 * Van Booking System - Booking Processing
 * Handles booking form submissions and API requests
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

try {
    // Get input data (handle both JSON and form data)
    $input = file_get_contents('php://input');
    $bookingData = [];
    
    // Check if it's JSON data
    if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
        $bookingData = json_decode($input, true) ?? [];
    } else {
        // Handle form data
        $bookingData = [
            'name' => trim($_POST['contactName'] ?? ''),
            'email' => trim($_POST['contactEmail'] ?? ''),
            'phone' => trim($_POST['contactPhone'] ?? ''),
            'van_type' => $_POST['vanType'] ?? '',
            'pickup_date' => $_POST['pickupDate'] ?? '',
            'pickup_time' => $_POST['pickupTime'] ?? '',
            'return_date' => $_POST['returnDate'] ?? '',
            'return_time' => $_POST['returnTime'] ?? '',
            'pickup_location' => trim($_POST['pickupLocation'] ?? ''),
            'destination' => trim($_POST['dropoffLocation'] ?? ''),
            'special_requests' => trim($_POST['specialRequests'] ?? '')
        ];
    }
    
    // Map JSON data to expected format
    if (isset($bookingData['contactName'])) {
        $bookingData = [
            'name' => trim($bookingData['contactName'] ?? ''),
            'email' => trim($bookingData['contactEmail'] ?? ''),
            'phone' => trim($bookingData['contactPhone'] ?? ''),
            'van_type' => $bookingData['vanType'] ?? '',
            'pickup_date' => $bookingData['pickupDate'] ?? '',
            'pickup_time' => $bookingData['pickupTime'] ?? '',
            'return_date' => $bookingData['returnDate'] ?? '',
            'return_time' => $bookingData['returnTime'] ?? '',
            'pickup_location' => trim($bookingData['pickupLocation'] ?? ''),
            'destination' => trim($bookingData['dropoffLocation'] ?? ''),
            'special_requests' => trim($bookingData['specialRequests'] ?? '')
        ];
    }

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

    // Calculate total price
    $totalPrice = calculateBookingPrice($bookingData['van_type'], $bookingData['pickup_date'], $bookingData['return_date']);
    
    if ($totalPrice <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid booking dates or van type'
        ]);
        exit();
    }

    // Check van availability
    $isAvailable = checkVanAvailability($bookingData['van_type'], $bookingData['pickup_date'], $bookingData['return_date']);
    
    if (!$isAvailable) {
        echo json_encode([
            'success' => false,
            'message' => 'Selected van is not available for the chosen dates'
        ]);
        exit();
    }

    // Get database connection
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        exit();
    }

    // Generate booking reference
    $bookingReference = generateBookingReference();

    // Insert booking into database
    $sql = "INSERT INTO bookings (
        name, email, phone, van_type, pickup_date, pickup_time, 
        return_date, return_time, pickup_location, destination, 
        special_requests, total_price, booking_reference
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $bookingData['name'],
        $bookingData['email'],
        $bookingData['phone'],
        $bookingData['van_type'],
        $bookingData['pickup_date'],
        $bookingData['pickup_time'],
        $bookingData['return_date'],
        $bookingData['return_time'],
        $bookingData['pickup_location'],
        $bookingData['destination'],
        $bookingData['special_requests'],
        $totalPrice,
        $bookingReference
    ]);

    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create booking'
        ]);
        exit();
    }

    // Get the booking ID
    $bookingId = $pdo->lastInsertId();

    // Prepare booking details for response
    $bookingDetails = [
        'id' => $bookingId,
        'reference' => $bookingReference,
        'name' => $bookingData['name'],
        'email' => $bookingData['email'],
        'phone' => $bookingData['phone'],
        'vanType' => $bookingData['van_type'],
        'pickupDate' => $bookingData['pickup_date'],
        'pickupTime' => $bookingData['pickup_time'],
        'returnDate' => $bookingData['return_date'],
        'returnTime' => $bookingData['return_time'],
        'pickupLocation' => $bookingData['pickup_location'],
        'destination' => $bookingData['destination'],
        'specialRequests' => $bookingData['special_requests'],
        'totalPrice' => number_format($totalPrice, 2),
        'status' => 'pending'
    ];

    // Send confirmation email (optional)
    sendConfirmationEmail($bookingDetails);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking' => $bookingDetails
    ]);

} catch (Exception $e) {
    error_log("Booking error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your booking'
    ]);
}

/**
 * Validate booking data
 */
function validateBookingData($data) {
    $errors = [];
    
    if (empty($data['name'])) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($data['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($data['phone'])) {
        $errors['phone'] = 'Phone number is required';
    }
    
    if (empty($data['van_type'])) {
        $errors['van_type'] = 'Van type is required';
    }
    
    if (empty($data['pickup_date'])) {
        $errors['pickup_date'] = 'Pickup date is required';
    }
    
    if (empty($data['pickup_time'])) {
        $errors['pickup_time'] = 'Pickup time is required';
    }
    
    if (empty($data['pickup_location'])) {
        $errors['pickup_location'] = 'Pickup location is required';
    }
    
    return $errors;
}

/**
 * Calculate booking price
 */
function calculateBookingPrice($vanType, $pickupDate, $returnDate) {
    // Base prices for different van types
    $basePrices = [
        'passenger' => 80,
        'cargo' => 100,
        'luxury' => 150,
        'minibus' => 120
    ];
    
    $basePrice = $basePrices[$vanType] ?? 100;
    
    // Calculate duration
    $start = new DateTime($pickupDate);
    $end = new DateTime($returnDate ?: $pickupDate);
    $duration = $start->diff($end)->days + 1;
    
    return $basePrice * $duration;
}

/**
 * Check van availability
 */
function checkVanAvailability($vanType, $pickupDate, $returnDate) {
    // For now, assume all vans are available
    // In a real system, you would check the database
    return true;
}

/**
 * Generate booking reference
 */
function generateBookingReference() {
    return 'VB' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 8));
}

/**
 * Send confirmation email to customer
 */
function sendConfirmationEmail($booking) {
    // Email configuration
    $to = $booking['email'];
    $subject = "Van Booking Confirmation - Reference: " . $booking['reference'];
    
    // Email content
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 10px 10px; }
            .booking-details { background: white; padding: 15px; border-radius: 8px; margin: 15px 0; }
            .booking-details h3 { color: #667eea; margin-top: 0; }
            .detail-row { display: flex; justify-content: space-between; margin: 8px 0; }
            .total { font-weight: bold; font-size: 1.1em; color: #667eea; border-top: 2px solid #667eea; padding-top: 10px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 0.9em; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>VanGo Booking Confirmation</h1>
                <p>Your van booking has been confirmed!</p>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($booking['name']) . ",</p>
                <p>Thank you for choosing VanGo for your transportation needs. Your booking has been confirmed with the following details:</p>
                
                <div class='booking-details'>
                    <h3>Booking Details</h3>
                    <div class='detail-row'>
                        <span>Booking Reference:</span>
                        <span><strong>" . $booking['reference'] . "</strong></span>
                    </div>
                    <div class='detail-row'>
                        <span>Van Type:</span>
                        <span>" . htmlspecialchars($booking['vanType']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span>Pickup Date:</span>
                        <span>" . $booking['pickupDate'] . " at " . $booking['pickupTime'] . "</span>
                    </div>
                    <div class='detail-row'>
                        <span>Pickup Location:</span>
                        <span>" . htmlspecialchars($booking['pickupLocation']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span>Destination:</span>
                        <span>" . htmlspecialchars($booking['destination']) . "</span>
                    </div>
                    <div class='detail-row total'>
                        <span>Total Amount:</span>
                        <span>$" . $booking['totalPrice'] . "</span>
                    </div>
                </div>
                
                <p><strong>Important Information:</strong></p>
                <ul>
                    <li>Please arrive 10 minutes before your scheduled pickup time</li>
                    <li>Have your booking reference ready</li>
                    <li>Contact us immediately if you need to make changes</li>
                    <li>Free cancellation up to 24 hours before pickup</li>
                </ul>
                
                <p>If you have any questions or need to modify your booking, please contact us at:</p>
                <p><strong>Phone:</strong> +1 (555) 123-4567<br>
                <strong>Email:</strong> info@vango.com</p>
                
                <div class='footer'>
                    <p>Thank you for choosing VanGo!</p>
                    <p>Safe travels!</p>
                </div>
            </div>
        </div>
    </body>
    </html>";
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: VanGo <noreply@vango.com>" . "\r\n";
    
    // Send email (uncomment to enable)
    // mail($to, $subject, $message, $headers);
    
    // For now, just log the email
    error_log("Email would be sent to: " . $to . " with subject: " . $subject);
}

/**
 * API endpoint to get van availability
 */
if (isset($_GET['action']) && $_GET['action'] === 'check_availability') {
    $vanType = $_GET['van_type'] ?? '';
    $pickupDate = $_GET['pickup_date'] ?? '';
    $returnDate = $_GET['return_date'] ?? '';
    
    if (empty($vanType) || empty($pickupDate) || empty($returnDate)) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit();
    }
    
    $isAvailable = checkVanAvailability($vanType, $pickupDate, $returnDate);
    echo json_encode(['success' => true, 'available' => $isAvailable]);
    exit();
}

/**
 * API endpoint to get van types
 */
if (isset($_GET['action']) && $_GET['action'] === 'get_van_types') {
    $vanTypes = getVanTypes();
    echo json_encode(['success' => true, 'van_types' => $vanTypes]);
    exit();
}

/**
 * API endpoint to get booking by reference
 */
if (isset($_GET['action']) && $_GET['action'] === 'get_booking') {
    $reference = $_GET['reference'] ?? '';
    
    if (empty($reference)) {
        echo json_encode(['success' => false, 'message' => 'Booking reference required']);
        exit();
    }
    
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_reference = ?");
        $stmt->execute([$reference]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            echo json_encode(['success' => true, 'booking' => $booking]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error retrieving booking']);
    }
    exit();
}

// Get available vans from database
$vans = [];
$pdo = getDatabaseConnection();
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM vans WHERE status = 'available' ORDER BY type, daily_rate");
    $vans = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Van - VanGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .booking-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }
        
        .booking-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .booking-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            margin-top: 40px;
        }
        
        .booking-form-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .booking-summary {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .van-selection {
            margin-bottom: 30px;
        }
        
        .van-option {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .van-option:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        .van-option.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
        
        .van-option-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .van-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        
        .van-price {
            font-size: 1.1rem;
            font-weight: 600;
            color: #667eea;
        }
        
        .van-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .van-detail {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .van-features {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .feature-tag {
            background: #e9ecef;
            color: #666;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section h3 i {
            color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .summary-item:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 1.1rem;
            color: #667eea;
        }
        
        .total-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 10px;
            border: 2px solid #667eea;
        }
        
        .booking-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .booking-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .booking-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .login-prompt {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .login-prompt h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .login-prompt p {
            color: #856404;
            margin-bottom: 15px;
        }
        
        .login-btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .progress-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }
        
        .progress-step {
            display: flex;
            align-items: center;
            margin: 0 20px;
        }
        
        .progress-step.active {
            color: #667eea;
        }
        
        .progress-step.completed {
            color: #28a745;
        }
        
        .progress-step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: 600;
        }
        
        .progress-step.active .progress-step-number {
            background: #667eea;
            color: white;
        }
        
        .progress-step.completed .progress-step-number {
            background: #28a745;
            color: white;
        }
        
        @media (max-width: 768px) {
            .booking-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .booking-form-section {
                padding: 20px;
            }
            
            .progress-indicator {
                flex-direction: column;
                gap: 15px;
            }
            
            .progress-step {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-van-shuttle"></i>
                <span>VanGo</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="vans.php" class="nav-link">Our Vans</a>
                </li>
                <li class="nav-item">
                    <a href="about.php" class="nav-link">About</a>
                </li>
                <li class="nav-item">
                    <a href="contact.php" class="nav-link">Contact</a>
                </li>
                <li class="nav-item">
                    <a href="booking.php" class="nav-link active">Book Now</a>
                </li>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($userName); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="dashboard.php" class="dropdown-item">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a></li>
                            <li><a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user-edit"></i> My Profile
                            </a></li>
                            <li><a href="bookings.php" class="dropdown-item">
                                <i class="fas fa-calendar-check"></i> My Bookings
                            </a></li>
                            <li><a href="favorites.php" class="dropdown-item">
                                <i class="fas fa-heart"></i> Favorites
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a href="logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="login.php" class="nav-link">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="signup.php" class="nav-link signup-btn">Sign Up</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="admin.php" class="nav-link admin-link">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                </li>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="booking-hero">
        <div class="booking-container">
            <h1>Book Your Van</h1>
            <p>Choose your perfect van and book your journey with ease</p>
            
            <!-- Progress Indicator -->
            <div class="progress-indicator">
                <div class="progress-step active">
                    <div class="progress-step-number">1</div>
                    <span>Select Van</span>
                </div>
                <div class="progress-step">
                    <div class="progress-step-number">2</div>
                    <span>Details</span>
                </div>
                <div class="progress-step">
                    <div class="progress-step-number">3</div>
                    <span>Confirm</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Form -->
    <section class="booking-section">
        <div class="booking-container">
            <div class="booking-grid">
                <!-- Booking Form -->
                <div class="booking-form-section">
                    <form id="bookingForm">
                        <!-- Van Selection -->
                        <div class="form-section">
                            <h3><i class="fas fa-van-shuttle"></i> Select Your Van</h3>
                            <div class="van-selection">
                                <?php if (empty($vans)): ?>
                                    <p>No vans available at the moment. Please check back later.</p>
                                <?php else: ?>
                                    <?php foreach ($vans as $van): ?>
                                        <div class="van-option" data-van-id="<?php echo $van['van_id']; ?>" data-price="<?php echo $van['daily_rate']; ?>">
                                            <div class="van-option-header">
                                                <div class="van-name"><?php echo htmlspecialchars($van['name']); ?></div>
                                                <div class="van-price">$<?php echo $van['daily_rate']; ?>/day</div>
                                            </div>
                                            <div class="van-details">
                                                <div class="van-detail">
                                                    <i class="fas fa-users"></i>
                                                    <span><?php echo $van['capacity']; ?> seats</span>
                                                </div>
                                                <div class="van-detail">
                                                    <i class="fas fa-tag"></i>
                                                    <span><?php echo ucfirst($van['type']); ?></span>
                                                </div>
                                                <div class="van-detail">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <span><?php echo htmlspecialchars($van['location']); ?></span>
                                                </div>
                                            </div>
                                            <div class="van-features">
                                                <?php 
                                                $features = json_decode($van['features'], true);
                                                if ($features) {
                                                    foreach (array_slice($features, 0, 3) as $feature) {
                                                        echo '<span class="feature-tag">' . ucfirst(str_replace('_', ' ', $feature)) . '</span>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Trip Details -->
                        <div class="form-section">
                            <h3><i class="fas fa-route"></i> Trip Details</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="pickupLocation">Pickup Location *</label>
                                    <input type="text" id="pickupLocation" name="pickupLocation" placeholder="Enter pickup address" required>
                                </div>
                                <div class="form-group">
                                    <label for="dropoffLocation">Dropoff Location *</label>
                                    <input type="text" id="dropoffLocation" name="dropoffLocation" placeholder="Enter dropoff address" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="pickupDate">Pickup Date *</label>
                                    <input type="date" id="pickupDate" name="pickupDate" required>
                                </div>
                                <div class="form-group">
                                    <label for="pickupTime">Pickup Time *</label>
                                    <input type="time" id="pickupTime" name="pickupTime" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="returnDate">Return Date</label>
                                    <input type="date" id="returnDate" name="returnDate">
                                </div>
                                <div class="form-group">
                                    <label for="returnTime">Return Time</label>
                                    <input type="time" id="returnTime" name="returnTime">
                                </div>
                            </div>
                        </div>

                        <!-- Passenger Details -->
                        <div class="form-section">
                            <h3><i class="fas fa-users"></i> Passenger Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="passengers">Number of Passengers *</label>
                                    <select id="passengers" name="passengers" required>
                                        <option value="">Select passengers</option>
                                        <?php for ($i = 1; $i <= 15; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i == 1 ? 'Passenger' : 'Passengers'; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="luggage">Luggage Pieces</label>
                                    <select id="luggage" name="luggage">
                                        <option value="">Select luggage</option>
                                        <?php for ($i = 0; $i <= 10; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?> pieces</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Special Requests -->
                        <div class="form-section">
                            <h3><i class="fas fa-comment"></i> Special Requests</h3>
                            <div class="form-group">
                                <label for="specialRequests">Additional Requirements</label>
                                <textarea id="specialRequests" name="specialRequests" rows="4" placeholder="Any special requirements, accessibility needs, or additional services..."></textarea>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-address-card"></i> Contact Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contactName">Contact Name *</label>
                                    <input type="text" id="contactName" name="contactName" value="<?php echo $isLoggedIn ? htmlspecialchars($userName) : ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="contactPhone">Contact Phone *</label>
                                    <input type="tel" id="contactPhone" name="contactPhone" placeholder="+1 (555) 123-4567" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="contactEmail">Contact Email *</label>
                                <input type="email" id="contactEmail" name="contactEmail" placeholder="your@email.com" required>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Booking Summary -->
                <div class="booking-summary">
                    <h3>Booking Summary</h3>
                    
                    <?php if (!$isLoggedIn): ?>
                        <div class="login-prompt">
                            <h4><i class="fas fa-info-circle"></i> Login Required</h4>
                            <p>Please login to complete your booking</p>
                            <a href="login.php" class="login-btn">Login Now</a>
                        </div>
                    <?php endif; ?>

                    <div class="summary-item">
                        <span>Selected Van:</span>
                        <span id="selectedVan">None</span>
                    </div>
                    <div class="summary-item">
                        <span>Duration:</span>
                        <span id="duration">0 days</span>
                    </div>
                    <div class="summary-item">
                        <span>Passengers:</span>
                        <span id="passengerCount">0</span>
                    </div>
                    <div class="summary-item">
                        <span>Base Price:</span>
                        <span id="basePrice">$0</span>
                    </div>
                    <div class="summary-item">
                        <span>Additional Fees:</span>
                        <span id="additionalFees">$0</span>
                    </div>
                    <div class="summary-item">
                        <span>Total:</span>
                        <span id="totalPrice">$0</span>
                    </div>

                    <div class="total-price">
                        <div>Total Amount</div>
                        <div id="totalDisplay">$0</div>
                    </div>

                    <?php if ($isLoggedIn): ?>
                        <button type="button" class="booking-btn" onclick="submitBooking()">
                            <i class="fas fa-check"></i> Confirm Booking
                        </button>
                    <?php else: ?>
                        <button type="button" class="booking-btn" disabled>
                            <i class="fas fa-lock"></i> Login to Book
                        </button>
                    <?php endif; ?>

                    <div style="margin-top: 20px; font-size: 0.9rem; color: #666; text-align: center;">
                        <p><i class="fas fa-shield-alt"></i> Secure booking</p>
                        <p><i class="fas fa-undo"></i> Free cancellation up to 24h before</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>VanGo</h3>
                    <p>Premium van booking service for all your transportation needs.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="vans.php">Our Vans</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="booking.php">Book Now</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="#">Airport Transfer</a></li>
                        <li><a href="#">City Tours</a></li>
                        <li><a href="#">Corporate Travel</a></li>
                        <li><a href="#">Wedding Transportation</a></li>
                        <li><a href="#">Group Travel</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@vango.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Main St, City, State 12345</p>
                    <p><i class="fas fa-clock"></i> 24/7 Service Available</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 VanGo. All rights reserved. | <a href="privacy.php">Privacy Policy</a> | <a href="terms.php">Terms of Service</a></p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        // Booking page specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            initializeBookingPage();
        });

        function initializeBookingPage() {
            // Van selection
            const vanOptions = document.querySelectorAll('.van-option');
            vanOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove previous selection
                    vanOptions.forEach(opt => opt.classList.remove('selected'));
                    // Add selection to current option
                    this.classList.add('selected');
                    
                    // Update summary
                    updateBookingSummary();
                });
            });

            // Form inputs for real-time updates
            const formInputs = document.querySelectorAll('#bookingForm input, #bookingForm select');
            formInputs.forEach(input => {
                input.addEventListener('change', updateBookingSummary);
                input.addEventListener('input', updateBookingSummary);
            });

            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('pickupDate').min = today;
            document.getElementById('returnDate').min = today;
        }

        function updateBookingSummary() {
            const selectedVan = document.querySelector('.van-option.selected');
            const pickupDate = document.getElementById('pickupDate').value;
            const returnDate = document.getElementById('returnDate').value;
            const passengers = document.getElementById('passengers').value;
            const luggage = document.getElementById('luggage').value;

            // Update selected van
            if (selectedVan) {
                const vanName = selectedVan.querySelector('.van-name').textContent;
                const vanPrice = parseFloat(selectedVan.dataset.price);
                document.getElementById('selectedVan').textContent = vanName;
                document.getElementById('basePrice').textContent = `$${vanPrice}`;
            } else {
                document.getElementById('selectedVan').textContent = 'None';
                document.getElementById('basePrice').textContent = '$0';
            }

            // Calculate duration
            let duration = 1;
            if (pickupDate && returnDate) {
                const start = new Date(pickupDate);
                const end = new Date(returnDate);
                const diffTime = Math.abs(end - start);
                duration = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            }
            document.getElementById('duration').textContent = `${duration} day${duration > 1 ? 's' : ''}`;

            // Update passengers
            document.getElementById('passengerCount').textContent = passengers || '0';

            // Calculate total
            const basePrice = selectedVan ? parseFloat(selectedVan.dataset.price) * duration : 0;
            const additionalFees = (parseInt(passengers) || 0) * 5 + (parseInt(luggage) || 0) * 2;
            const total = basePrice + additionalFees;

            document.getElementById('additionalFees').textContent = `$${additionalFees}`;
            document.getElementById('totalPrice').textContent = `$${total}`;
            document.getElementById('totalDisplay').textContent = `$${total}`;
        }

        function submitBooking() {
            const form = document.getElementById('bookingForm');
            const selectedVan = document.querySelector('.van-option.selected');
            
            if (!selectedVan) {
                showNotification('Please select a van', 'error');
                return;
            }

            // Validate form
            const formData = new FormData(form);
            let isValid = true;
            
            for (let [name, value] of formData.entries()) {
                if (name !== 'returnDate' && name !== 'returnTime' && name !== 'luggage' && name !== 'specialRequests') {
                    if (!value.trim()) {
                        isValid = false;
                        break;
                    }
                }
            }

            if (!isValid) {
                showNotification('Please fill in all required fields', 'error');
                return;
            }

            // Show loading state
            const submitBtn = document.querySelector('.booking-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;

            // Simulate booking submission
            setTimeout(() => {
                showNotification('Booking submitted successfully! We will contact you shortly.', 'success');
                form.reset();
                document.querySelectorAll('.van-option').forEach(opt => opt.classList.remove('selected'));
                updateBookingSummary();
                
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        }
    </script>
</body>
</html> 