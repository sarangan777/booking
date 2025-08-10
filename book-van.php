<?php
// Start session
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';

// Include database functions
require_once 'database.php';

// Get available vans from database
$vans = [];
$pdo = getDatabaseConnection();
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM vans WHERE status = 'available' ORDER BY type, daily_rate");
    $vans = $stmt->fetchAll();
}

// Function to get appropriate icon for van type
function getVanIcon($type) {
    $type = strtolower($type);
    switch ($type) {
        case 'passenger':
        case 'people':
            return 'users';
        case 'luxury':
        case 'premium':
            return 'crown';
        case 'cargo':
        case 'freight':
            return 'box';
        case 'minibus':
        case 'bus':
            return 'bus';
        case 'suv':
            return 'car';
        case 'sedan':
            return 'car-side';
        default:
            return 'van-shuttle';
    }
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
            padding: 120px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .booking-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .booking-hero-content {
            position: relative;
            z-index: 2;
        }
        
        .booking-hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .booking-hero p {
            font-size: 1.3rem;
            max-width: 600px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }
        
        .booking-section {
            padding: 100px 0;
            background: #f8f9fa;
        }
        
        .booking-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 60px;
        }
        
        .booking-form-container {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .form-header h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 15px;
        }
        
        .form-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .form-section {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
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
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .van-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .van-option {
            border: 2px solid #e9ecef;
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .van-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .van-option:hover {
            border-color: #667eea;
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.15);
        }
        
        .van-option:hover::before {
            transform: scaleX(1);
        }
        
        .van-option.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.3);
            animation: selectedPulse 0.3s ease;
        }
        
        .van-option.selected::before {
            transform: scaleX(1);
        }
        
        .van-option.selected::after {
            content: '✓';
            position: absolute;
            top: 15px;
            right: 15px;
            width: 25px;
            height: 25px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }
        
        .van-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        
        .van-option.selected .van-icon {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2);
        }
        
        .van-option h4 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #333;
        }
        
        .van-option.selected h4 {
            color: white;
        }
        
        .van-type {
            font-size: 0.9rem;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .van-option.selected .van-type {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .van-capacity {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 15px;
            font-size: 0.95rem;
            color: #666;
        }
        
        .van-option.selected .van-capacity {
            color: rgba(255, 255, 255, 0.9);
        }
        
        .van-capacity i {
            color: #667eea;
            font-size: 1rem;
        }
        
        .van-option.selected .van-capacity i {
            color: white;
        }
        
        .van-price {
            font-size: 1.8rem;
            font-weight: 800;
            color: #667eea;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .van-price .currency {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .van-price .period {
            font-size: 0.9rem;
            font-weight: 500;
            opacity: 0.7;
        }
        
        .van-option.selected .van-price {
            color: white;
        }
        
        .van-features {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .feature-badge {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .feature-badge:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }
        
        .van-option.selected .feature-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .van-option.selected .feature-badge:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .van-popular {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .van-recommended {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .no-vans-message {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 40px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            border: 2px dashed #dee2e6;
        }
        
        .no-vans-message i {
            font-size: 4rem;
            color: #ffc107;
            margin-bottom: 20px;
        }
        
        .no-vans-message h3 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .no-vans-message p {
            color: #666;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .booking-summary {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        
        .summary-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .summary-header h3 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            color: #666;
            font-weight: 500;
        }
        
        .summary-value {
            color: #333;
            font-weight: 600;
        }
        
        .price-breakdown {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .price-item:last-child {
            margin-bottom: 0;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .total-price {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            text-align: center;
            margin: 20px 0;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .features-section {
            padding: 80px 0;
            background: white;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px 20px;
            border-radius: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        
        .feature-card i {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .booking-hero h1 {
                font-size: 2.5rem;
            }
            
            .booking-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .van-selection {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .van-option {
                padding: 25px 20px;
            }
            
            .van-icon {
                width: 70px;
                height: 70px;
                font-size: 2rem;
            }
            
            .van-option h4 {
                font-size: 1.3rem;
            }
            
            .van-price {
                font-size: 1.6rem;
            }
            
            .booking-summary {
                position: static;
                margin-top: 30px;
            }
        }
        
        @media (max-width: 480px) {
            .van-option {
                padding: 20px 15px;
            }
            
            .van-icon {
                width: 60px;
                height: 60px;
                font-size: 1.8rem;
            }
            
            .van-option h4 {
                font-size: 1.2rem;
            }
            
            .van-price {
                font-size: 1.4rem;
            }
            
            .feature-badge {
                font-size: 0.7rem;
                padding: 4px 8px;
            }
        }
        
        /* Success Modal Styles */
        .success-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .booking-success h3 {
            color: #667eea;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        
        .booking-success p {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .modal-content button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .modal-content button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        /* Loading Animation */
        .van-selection.loading {
            position: relative;
            min-height: 200px;
        }
        
        .van-selection.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 40px;
            height: 40px;
            margin: -20px 0 0 -20px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Van Selection Animation */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Selection Feedback */
        @keyframes selectedPulse {
            0% { transform: translateY(-8px) scale(1); }
            50% { transform: translateY(-8px) scale(1.02); }
            100% { transform: translateY(-8px) scale(1); }
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
                    <a href="vans.html" class="nav-link">Our Vans</a>
                </li>
                <li class="nav-item">
                    <a href="about.html" class="nav-link">About</a>
                </li>
                <li class="nav-item">
                    <a href="contact.html" class="nav-link">Contact</a>
                </li>
                <li class="nav-item">
                    <a href="book-van.php" class="nav-link active">Book Now</a>
                </li>
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($userName); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="auth.html" class="dropdown-item">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a></li>
                            <li><a href="auth.html" class="dropdown-item">
                                <i class="fas fa-user-edit"></i> My Profile
                            </a></li>
                            <li><a href="auth.html" class="dropdown-item">
                                <i class="fas fa-calendar-check"></i> My Bookings
                            </a></li>
                            <li><a href="auth.html" class="dropdown-item">
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
                        <a href="auth.html" class="nav-link">Login</a>
                    </li>
                <?php endif; ?>
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
        <div class="booking-hero-content">
            <h1>Book Your Perfect Van</h1>
            <p>Choose from our premium fleet and book your transportation with ease. Professional service guaranteed.</p>
        </div>
    </section>

    <!-- Booking Section -->
    <section class="booking-section">
        <div class="booking-container">
            <!-- Booking Form -->
            <div class="booking-form-container">
                <div class="form-header">
                    <h2>Booking Details</h2>
                    <p>Fill in your travel details and we'll provide you with the best van options.</p>
                </div>
                
                <form id="bookingForm">
                    <!-- Trip Details -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-route"></i>
                            Trip Details
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="pickupLocation">Pickup Location *</label>
                                <input type="text" id="pickupLocation" name="pickupLocation" placeholder="Enter pickup address" required>
                            </div>
                            <div class="form-group">
                                <label for="dropoffLocation">Dropoff Location *</label>
                                <input type="text" id="dropoffLocation" name="dropoffLocation" placeholder="Enter destination address" required>
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
                                <label for="returnDate">Return Date *</label>
                                <input type="date" id="returnDate" name="returnDate" required>
                            </div>
                            <div class="form-group">
                                <label for="returnTime">Return Time *</label>
                                <input type="time" id="returnTime" name="returnTime" required>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Details -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-users"></i>
                            Passenger Information
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="passengers">Number of Passengers *</label>
                                <select id="passengers" name="passengers" required>
                                    <option value="">Select passengers</option>
                                    <option value="1">1 Passenger</option>
                                    <option value="2">2 Passengers</option>
                                    <option value="3">3 Passengers</option>
                                    <option value="4">4 Passengers</option>
                                    <option value="5">5 Passengers</option>
                                    <option value="6">6 Passengers</option>
                                    <option value="7">7 Passengers</option>
                                    <option value="8">8 Passengers</option>
                                    <option value="9">9 Passengers</option>
                                    <option value="10">10+ Passengers</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="luggage">Luggage Pieces</label>
                                <select id="luggage" name="luggage">
                                    <option value="0">No luggage</option>
                                    <option value="1-2">1-2 pieces</option>
                                    <option value="3-5">3-5 pieces</option>
                                    <option value="6-10">6-10 pieces</option>
                                    <option value="10+">10+ pieces</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Van Selection -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-van-shuttle"></i>
                            Choose Your Van
                        </div>
                        <div class="van-selection">
                            <?php if (empty($vans)): ?>
                                <div class="no-vans-message">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <h3>No Vans Available</h3>
                                    <p>We're currently updating our fleet. Please check back later or contact us for special arrangements.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($vans as $van): ?>
                                    <div class="van-option" 
                                         data-van="<?php echo htmlspecialchars($van['van_id'] ?? ''); ?>" 
                                         data-price="<?php echo $van['daily_rate'] ?? 0; ?>"
                                         data-capacity="<?php echo $van['seats'] ?? 0; ?>"
                                         data-type="<?php echo htmlspecialchars($van['type'] ?? ''); ?>">
                                        
                                        <?php if (($van['daily_rate'] ?? 0) >= 200): ?>
                                            <div class="van-popular">Popular</div>
                                        <?php elseif (($van['seats'] ?? 0) >= 8): ?>
                                            <div class="van-recommended">Recommended</div>
                                        <?php endif; ?>
                                        
                                        <div class="van-icon">
                                            <i class="fas fa-<?php echo getVanIcon($van['type'] ?? ''); ?>"></i>
                                        </div>
                                        
                                        <h4><?php echo htmlspecialchars($van['type'] ?? ''); ?> Van</h4>
                                        <div class="van-type"><?php echo htmlspecialchars($van['type'] ?? ''); ?> Class</div>
                                        
                                        <div class="van-capacity">
                                            <i class="fas fa-users"></i>
                                            <span><?php echo $van['seats'] ?? 0; ?> Passengers</span>
                                        </div>
                                        
                                        <div class="van-price">
                                            <span class="currency">$</span>
                                            <span class="amount"><?php echo number_format($van['daily_rate'] ?? 0); ?></span>
                                            <span class="period">/day</span>
                                        </div>
                                        
                                        <div class="van-features">
                                            <?php 
                                            $features = json_decode($van['features'] ?? '[]', true);
                                            if (is_array($features)) {
                                                foreach ($features as $feature) {
                                                    $icon = '';
                                                    $label = '';
                                                    switch ($feature) {
                                                        case 'air_conditioning':
                                                        case 'climate_control':
                                                            $icon = 'snowflake';
                                                            $label = 'AC';
                                                            break;
                                                        case 'wifi':
                                                            $icon = 'wifi';
                                                            $label = 'WiFi';
                                                            break;
                                                        case 'gps':
                                                        case 'navigation':
                                                            $icon = 'map-marker-alt';
                                                            $label = 'GPS';
                                                            break;
                                                        case 'entertainment':
                                                        case 'entertainment_system':
                                                            $icon = 'tv';
                                                            $label = 'Entertainment';
                                                            break;
                                                        case 'refreshments':
                                                            $icon = 'coffee';
                                                            $label = 'Refreshments';
                                                            break;
                                                        case 'chauffeur':
                                                            $icon = 'user-tie';
                                                            $label = 'Chauffeur';
                                                            break;
                                                        case 'comfortable_seating':
                                                            $icon = 'couch';
                                                            $label = 'Comfort';
                                                            break;
                                                        case 'storage':
                                                        case 'storage_space':
                                                            $icon = 'box';
                                                            $label = 'Storage';
                                                            break;
                                                        default:
                                                            $icon = 'check';
                                                            $label = ucfirst(str_replace('_', ' ', $feature));
                                                    }
                                                    if ($icon && $label) {
                                                        echo "<span class='feature-badge'><i class='fas fa-{$icon}'></i> {$label}</span>";
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Additional Services -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-plus-circle"></i>
                            Additional Services
                        </div>
                        <div class="form-group">
                            <label for="specialRequests">Special Requests</label>
                            <textarea id="specialRequests" name="specialRequests" placeholder="Any special requests?"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="conductDetails">Conduct Details</label>
                            <textarea id="conductDetails" name="conductDetails" placeholder="Enter conduct details (e.g. driver requirements, conduct expectations, etc.)"></textarea>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-address-card"></i>
                            Contact Information
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contactName">Contact Name *</label>
                                <input type="text" id="contactName" name="contactName" placeholder="Your full name" required>
                            </div>
                            <div class="form-group">
                                <label for="contactPhone">Phone Number *</label>
                                <input type="tel" id="contactPhone" name="contactPhone" placeholder="Your phone number" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="contactEmail">Email Address *</label>
                            <input type="email" id="contactEmail" name="contactEmail" placeholder="Your email address" required>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Booking Summary -->
            <div class="booking-summary">
                <div class="summary-header">
                    <h3>Booking Summary</h3>
                    <p>Review your booking details</p>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Van Type:</span>
                    <span class="summary-value" id="summaryVanType">Not selected</span>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Passengers:</span>
                    <span class="summary-value" id="summaryPassengers">-</span>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Pickup Date:</span>
                    <span class="summary-value" id="summaryDate">-</span>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Pickup Time:</span>
                    <span class="summary-value" id="summaryTime">-</span>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Return Date:</span>
                    <span class="summary-value" id="summaryReturnDate">-</span>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Return Time:</span>
                    <span class="summary-value" id="summaryReturnTime">-</span>
                </div>
                
                <div class="price-breakdown">
                    <div class="price-item">
                        <span>Base Rate:</span>
                        <span id="basePrice">$0</span>
                    </div>
                    <div class="price-item">
                        <span>Service Fee:</span>
                        <span id="serviceFee">$0</span>
                    </div>
                    <div class="price-item">
                        <span>Total:</span>
                        <span id="totalPrice">$0</span>
                    </div>
                </div>
                
                <div class="total-price" id="displayTotalPrice">$0</div>
                
                <?php if ($isLoggedIn): ?>
                    <button type="submit" class="submit-btn" onclick="submitBooking()">
                        <i class="fas fa-calendar-check"></i> Confirm Booking
                    </button>
                <?php else: ?>
                    <a href="auth.html" class="submit-btn" style="text-decoration: none; display: block; text-align: center;">
                        <i class="fas fa-sign-in-alt"></i> Login to Book
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Safe & Secure</h3>
                <p>All our vans are regularly maintained and our drivers are thoroughly vetted for your safety.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-clock"></i>
                <h3>24/7 Support</h3>
                <p>Our customer support team is available round the clock to assist you with any questions.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-credit-card"></i>
                <h3>Secure Payment</h3>
                <p>Multiple payment options with secure processing to ensure your financial information is protected.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-calendar-check"></i>
                <h3>Instant Confirmation</h3>
                <p>Get instant booking confirmation and detailed itinerary sent to your email.</p>
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
                        <li><a href="vans.html">Our Vans</a></li>
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="contact.html">Contact</a></li>
                        <li><a href="book-van.php">Book Now</a></li>
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
                <p>&copy; 2024 VanGo. All rights reserved. | <a href="auth.html">Privacy Policy</a> | <a href="auth.html">Terms of Service</a></p>
            </div>
        </div>
    </footer>

    <!-- Booking Success Modal -->
    <div id="bookingSuccessModal" class="modal" style="display:none;">
        <div class="modal-content booking-success">
            <button class="close-button" onclick="closeBookingModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 1.5rem; color: #666; cursor: pointer;">&times;</button>
            
            <h3><i class="fas fa-check-circle"></i> 🎉 Booking Confirmed!</h3>
            <p>Congratulations! Your van booking has been successfully created and confirmed. Here are your booking details:</p>
            
            <div id="bookingDetails" class="booking-details" style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: left;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 0.9rem;">
                    <div><strong>Booking ID:</strong> <span id="successBookingId"></span></div>
                    <div><strong>Van:</strong> <span id="successVanName"></span></div>
                    <div><strong>Pickup:</strong> <span id="successPickupLocation"></span></div>
                    <div><strong>Dropoff:</strong> <span id="successDropoffLocation"></span></div>
                    <div><strong>Date:</strong> <span id="successPickupDate"></span></div>
                    <div><strong>Time:</strong> <span id="successPickupTime"></span></div>
                    <div><strong>Passengers:</strong> <span id="successPassengers"></span></div>
                    <div><strong>Total Amount:</strong> <span id="successTotalAmount"></span></div>
                    <div><strong>Conduct Details:</strong> <span id="successConductDetails"></span></div>
                </div>
            </div>
            
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 20px;">
                <strong>Next Steps:</strong> Complete your payment to finalize your booking. You'll receive a confirmation email with all the details.
            </p>
            
            <button id="continueToCartBtn" class="submit-btn">
                <i class="fas fa-shopping-cart"></i> Continue to Payment
            </button>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal" style="display:none;">
        <div class="modal-content" style="text-align: center; padding: 40px 30px; max-width: 500px;">
            <button class="close-button" onclick="closeErrorModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 1.5rem; color: #666; cursor: pointer;">&times;</button>
            
            <h3 style="color: #dc3545; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> Booking Error
            </h3>
            
            <div id="errorMessage" style="color: #666; font-size: 1.1rem; line-height: 1.6; margin-bottom: 30px; text-align: left; white-space: pre-line;">
            </div>
            
            <button onclick="closeErrorModal()" class="submit-btn" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Van Selection
        document.addEventListener('DOMContentLoaded', function() {
            // Add click event listeners to van options
            document.querySelectorAll('.van-option').forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    document.querySelectorAll('.van-option').forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Get van data
                    const vanId = this.getAttribute('data-van');
                    const vanType = this.getAttribute('data-type');
                    const price = parseFloat(this.getAttribute('data-price'));
                    const capacity = this.getAttribute('data-capacity');
                    
                    // Update summary
                    document.getElementById('summaryVanType').textContent = vanType + ' Van';
                    document.getElementById('basePrice').textContent = `$${price}`;
                    
                    // Store selected van data
                    window.selectedVan = {
                        van_id: vanId,
                        type: vanType,
                        daily_rate: price,
                        seats: capacity
                    };
                    
                    calculateTotal();
                });
            });
        });

        // Form field updates
        document.getElementById('passengers').addEventListener('change', function() {
            document.getElementById('summaryPassengers').textContent = this.value + ' passengers';
        });

        document.getElementById('pickupDate').addEventListener('change', function() {
            document.getElementById('summaryDate').textContent = this.value;
        });

        document.getElementById('pickupTime').addEventListener('change', function() {
            document.getElementById('summaryTime').textContent = this.value;
        });

        document.getElementById('returnDate').addEventListener('change', function() {
            document.getElementById('summaryReturnDate').textContent = this.value;
        });

        document.getElementById('returnTime').addEventListener('change', function() {
            document.getElementById('summaryReturnTime').textContent = this.value;
        });

        // Price calculation
        function calculateTotal() {
            const basePrice = parseFloat(document.getElementById('basePrice').textContent.replace('$', '')) || 0;
            const serviceFee = basePrice * 0.1; // 10% service fee
            const total = basePrice + serviceFee;
            
            document.getElementById('serviceFee').textContent = `$${serviceFee.toFixed(0)}`;
            document.getElementById('totalPrice').textContent = `$${total.toFixed(0)}`;
            document.getElementById('displayTotalPrice').textContent = `$${total.toFixed(0)}`;
        }

        // Form submission
        function submitBooking() {
            // Check if user is logged in
            if (!window.selectedVan) {
                showError('Please select a van first');
                return;
            }

            // Helper to safely get value
            function safeValue(id) {
                const el = document.getElementById(id);
                return el ? el.value.trim() : '';
            }

            // Get form data
            const formData = {
                vanId: window.selectedVan.van_id,
                pickupLocation: safeValue('pickupLocation'),
                dropoffLocation: safeValue('dropoffLocation'),
                pickupDate: safeValue('pickupDate'),
                pickupTime: safeValue('pickupTime'),
                returnDate: safeValue('returnDate'),
                returnTime: safeValue('returnTime'),
                passengers: safeValue('passengers'),
                specialRequests: safeValue('specialRequests'),
                additionalServices: [], // Add this for additional services
                conductDetails: safeValue('conductDetails'),
                contactName: safeValue('contactName'),
                contactEmail: safeValue('contactEmail'),
                contactPhone: safeValue('contactPhone')
            };

            // Enhanced validation with specific error messages
            const errors = [];
            
            if (!formData.pickupLocation) {
                errors.push('Pickup location is required');
            }
            
            if (!formData.dropoffLocation) {
                errors.push('Dropoff location is required');
            }
            
            if (!formData.pickupDate) {
                errors.push('Pickup date is required');
            }
            
            if (!formData.pickupTime) {
                errors.push('Pickup time is required');
            }
            
            if (!formData.returnDate) {
                errors.push('Return date is required');
            }
            
            if (!formData.returnTime) {
                errors.push('Return time is required');
            }
            
            if (!formData.passengers || formData.passengers < 1) {
                errors.push('At least 1 passenger is required');
            }
            
            if (!formData.contactName) {
                errors.push('Contact name is required');
            }
            
            if (!formData.contactEmail) {
                errors.push('Contact email is required');
            }
            
            if (!formData.contactPhone) {
                errors.push('Contact phone is required');
            }
            
            // Date validation
            if (formData.pickupDate && formData.returnDate) {
                const pickupDate = new Date(formData.pickupDate);
                const returnDate = new Date(formData.returnDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (pickupDate < today) {
                    errors.push('Pickup date cannot be in the past');
                }
                if (returnDate < pickupDate) {
                    errors.push('Return date must be after pickup date');
                }
            }
            
            // Show errors if any
            if (errors.length > 0) {
                showError('Please fix the following errors:\n' + errors.join('\n'));
                return;
            }

            // Show loading state
            const submitBtn = document.querySelector('.submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;

            // Submit booking to database
            fetch('booking-simple.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store booking data for cart
                    window._bookingForCart = data.booking;
                    
                    // Show detailed booking success modal
                    document.getElementById('bookingSuccessModal').innerHTML = `
                        <div class="modal-content booking-success" style="text-align:center; padding:40px 30px; max-width:500px;">
                            <h3 style="color:#28a745; font-size:2rem; margin-bottom:20px;">
                                <i class="fas fa-check-circle"></i> 🎉 Booking Confirmed!
                            </h3>
                            <p style="font-size:1.1rem; color:#333; margin-bottom:20px;">
                                Your van booking has been successfully created and confirmed. Here are your booking details:
                            </p>
                            <div style="background:#f8f9fa; padding:20px; border-radius:10px; margin:20px 0; text-align:left;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; font-size:0.9rem;">
                                    <div><strong>Booking ID:</strong> ${data.booking.booking_id}</div>
                                    <div><strong>Van:</strong> ${data.booking.van_name}</div>
                                    <div><strong>Pickup:</strong> ${data.booking.pickup_location}</div>
                                    <div><strong>Dropoff:</strong> ${data.booking.dropoff_location}</div>
                                    <div><strong>Date:</strong> ${data.booking.pickup_date}</div>
                                    <div><strong>Time:</strong> ${data.booking.pickup_time}</div>
                                    <div><strong>Passengers:</strong> ${data.booking.passengers}</div>
                                    <div><strong>Total Amount:</strong> $${data.booking.total_amount}</div>
                                </div>
                            </div>
                            <p style="font-size:0.9rem; color:#666; margin-bottom:20px;">
                                <strong>Next Steps:</strong> Complete your payment to finalize your booking. You'll receive a confirmation email with all the details.
                            </p>
                            <button id="continueToCartBtn" class="submit-btn">
                                <i class="fas fa-shopping-cart"></i> Continue to Payment
                            </button>
                        </div>
                    `;
                    document.getElementById('bookingSuccessModal').style.display = 'block';
                    document.getElementById('bookingForm').reset();
                    resetSummary();
                } else {
                    if (data.login_required) {
                        showError(data.message + '\n\nPlease login first, then try booking again.');
                    } else {
                        showError(data.message || 'Booking failed. Please try again.');
                    }
                }
            })
            .catch(error => {
                console.error('Booking error:', error);
                showError('Network error occurred. Please try again.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        // Show error message
        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorModal').style.display = 'block';
            // Highlight fields if mentioned in error
            if (message.includes('Return date is required')) highlightField('returnDate');
            if (message.includes('Return time is required')) highlightField('returnTime');
            if (message.includes('Pickup date is required')) highlightField('pickupDate');
            if (message.includes('Pickup time is required')) highlightField('pickupTime');
            if (message.includes('Pickup location is required')) highlightField('pickupLocation');
            if (message.includes('Dropoff location is required')) highlightField('dropoffLocation');
            if (message.includes('Contact name is required')) highlightField('contactName');
            if (message.includes('Contact email is required')) highlightField('contactEmail');
            if (message.includes('Contact phone is required')) highlightField('contactPhone');
        }

        // Show booking success
        function showBookingSuccess(booking) {
            // Show a simple booking success modal with no details
            document.getElementById('bookingSuccessModal').innerHTML = `
                <div class="modal-content booking-success" style="text-align:center; padding:40px 30px; max-width:400px;">
                    <h3 style="color:#28a745; font-size:2rem; margin-bottom:20px;">
                        <i class="fas fa-check-circle"></i> Booking Successful!
                    </h3>
                    <p style="font-size:1.2rem; color:#333; margin-bottom:30px;">
                        Your van booking has been received and confirmed.<br>Thank you for booking with VanGo!
                    </p>
                    <button onclick="closeBookingModal()" class="submit-btn" style="background:linear-gradient(135deg,#28a745,#20c997);margin-top:10px;">OK</button>
                </div>
            `;
            document.getElementById('bookingSuccessModal').style.display = 'block';
            document.getElementById('bookingForm').reset();
            resetSummary();
        }

        // Close booking modal
        function closeBookingModal() {
            document.getElementById('bookingSuccessModal').style.display = 'none';
        }

        // Continue to cart on modal button click
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'continueToCartBtn') {
                const booking = window._bookingForCart;
                if (!booking) return;
                const params = new URLSearchParams({
                    bookingId: booking.booking_id || '',
                    vanId: booking.van_id || '',
                    vanName: booking.van_name || '',
                    vanType: booking.van_type || '',
                    pickupLocation: booking.pickup_location || '',
                    dropoffLocation: booking.dropoff_location || '',
                    pickupDate: booking.pickup_date || '',
                    pickupTime: booking.pickup_time || '',
                    returnDate: booking.return_date || '',
                    returnTime: booking.return_time || '',
                    passengers: booking.passengers || '',
                    basePrice: booking.total_amount || '',
                    specialRequests: booking.special_requests || '',
                    conductDetails: booking.conduct_details || ''
                });
                window.location.href = `add-to-cart.php?${params.toString()}`;
            }
        });

        // Close modal on outside click
        window.onclick = function(event) {
            const successModal = document.getElementById('bookingSuccessModal');
            const errorModal = document.getElementById('errorModal');
            
            if (event.target === successModal) {
                closeBookingModal();
            }
            if (event.target === errorModal) {
                closeErrorModal();
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeBookingModal();
                closeErrorModal();
            }
        });

        // Reset summary
        function resetSummary() {
            document.getElementById('summaryVanType').textContent = 'Not selected';
            document.getElementById('summaryPassengers').textContent = '-';
            document.getElementById('summaryDate').textContent = '-';
            document.getElementById('summaryTime').textContent = '-';
            document.getElementById('summaryReturnDate').textContent = '-';
            document.getElementById('summaryReturnTime').textContent = '-';
            document.getElementById('displayTotalPrice').textContent = '$0';
            document.querySelectorAll('.van-option').forEach(opt => opt.classList.remove('selected'));
            window.selectedVan = null;
        }

        // Close error modal
        function closeErrorModal() {
            document.getElementById('errorModal').style.display = 'none';
        }

        // Auto-fill return date/time when pickup date/time is selected
        if (document.getElementById('pickupDate') && document.getElementById('returnDate')) {
            document.getElementById('pickupDate').addEventListener('change', function() {
                if (!document.getElementById('returnDate').value) {
                    document.getElementById('returnDate').value = this.value;
                }
            });
        }
        if (document.getElementById('pickupTime') && document.getElementById('returnTime')) {
            document.getElementById('pickupTime').addEventListener('change', function() {
                if (!document.getElementById('returnTime').value) {
                    document.getElementById('returnTime').value = this.value;
                }
            });
        }

        // Highlight missing fields on validation error
        function highlightField(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.borderColor = '#dc3545';
                el.focus();
                setTimeout(() => { el.style.borderColor = ''; }, 2000);
            }
        }
    </script>
</body>
</html> 
