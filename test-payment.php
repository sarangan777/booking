<?php
/**
 * Test Payment Form
 * This page tests the payment form functionality
 */

// Start session
session_start();

// Set test user session
$_SESSION['user_id'] = 'USER001';
$_SESSION['user_name'] = 'Test User';
$_SESSION['user_email'] = 'test@example.com';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Payment Form - VanGo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .info { color: #007bff; }
        .warning { color: #ffc107; }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin: 10px;
        }
        .test-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Payment Form</h1>
        
        <div class="test-section">
            <h3>✅ What's Fixed:</h3>
            <ul>
                <li>Payment form now shows card details fields</li>
                <li>Form validation prevents empty submissions</li>
                <li>Pre-filled test data for easy testing</li>
                <li>Visual feedback for form validation</li>
                <li>Detailed success modal with payment info</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h3>📋 Test Steps:</h3>
            <ol>
                <li>Click "Test Payment Form" below</li>
                <li>You should see the payment form with pre-filled data</li>
                <li>Try submitting with empty fields - should show validation errors</li>
                <li>Submit with the pre-filled data - should show success modal</li>
                <li>Check that the success modal shows payment details</li>
            </ol>
        </div>
        
        <div class="test-section">
            <h3>🔍 Expected Behavior:</h3>
            <ul>
                <li>Payment form should be visible with card fields</li>
                <li>Form should validate all required fields</li>
                <li>Success modal should show booking and payment details</li>
                <li>No more "delecty payment success" - proper validation</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="add-to-cart.php?bookingId=B2024001&vanId=VAN001&vanName=Economy+Van&vanType=Economy&pickupLocation=Test+Pickup&dropoffLocation=Test+Dropoff&pickupDate=2024-02-15&pickupTime=09:00&returnDate=2024-02-17&returnTime=18:00&passengers=3&basePrice=240.00&specialRequests=Test+request&conductDetails=Test+conduct" class="btn">
                🧪 Test Payment Form
            </a>
            
            <a href="auto-login.php" class="btn" style="background: #28a745;">
                🔐 Auto Login
            </a>
            
            <a href="book-van.php" class="btn" style="background: #ffc107; color: #333;">
                📝 Test Booking
            </a>
        </div>
        
        <div class="test-section">
            <h3>💳 Test Payment Data:</h3>
            <ul>
                <li><strong>Card Number:</strong> 4111 1111 1111 1111</li>
                <li><strong>Expiry Date:</strong> 12/25</li>
                <li><strong>CVV:</strong> 123</li>
                <li><strong>Cardholder:</strong> Test User</li>
                <li><strong>Address:</strong> 123 Test St, Test City, TC 12345</li>
            </ul>
        </div>
    </div>
</body>
</html> 