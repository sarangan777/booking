<?php
/**
 * Test Booking Success Functionality
 * This file tests the booking success message display
 */

session_start();

// Simulate a logged-in user
$_SESSION['user_id'] = 'USER001';
$_SESSION['user_name'] = 'John Smith';
$_SESSION['user_email'] = 'john.smith@email.com';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Booking Success - VanGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div style="padding: 50px; text-align: center;">
        <h1>Test Booking Success</h1>
        <p>Click the button below to test the booking success modal:</p>
        
        <button onclick="testBookingSuccess()" style="
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            margin: 20px;
        ">
            Test Booking Success Modal
        </button>
    </div>

    <!-- Booking Success Modal -->
    <div id="bookingSuccessModal" class="modal" style="display:none;">
        <div class="modal-content booking-success">
            <button class="close-button" onclick="closeBookingModal()">&times;</button>
            
            <h3><i class="fas fa-check-circle"></i> Booking Confirmed!</h3>
            <p>Your van booking has been successfully created. Here are your booking details:</p>
            
            <div id="bookingDetails" class="booking-details">
                <div>
                    <div><strong>Booking ID:</strong> <span id="successBookingId">B202400001</span></div>
                    <div><strong>Van:</strong> <span id="successVanName">Premium Van</span></div>
                    <div><strong>Pickup:</strong> <span id="successPickupLocation">Downtown Hub</span></div>
                    <div><strong>Dropoff:</strong> <span id="successDropoffLocation">Airport Terminal</span></div>
                    <div><strong>Date:</strong> <span id="successPickupDate">2024-02-15</span></div>
                    <div><strong>Time:</strong> <span id="successPickupTime">09:00</span></div>
                    <div><strong>Passengers:</strong> <span id="successPassengers">4</span></div>
                    <div><strong>Total Amount:</strong> <span id="successTotalAmount">$360.00</span></div>
                </div>
            </div>
            
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 20px;">
                You will now be redirected to complete your payment and finalize your booking.
            </p>
            
            <button id="continueToCartBtn" class="submit-btn">
                <i class="fas fa-shopping-cart"></i> Continue to Payment
            </button>
        </div>
    </div>

    <script>
        function testBookingSuccess() {
            document.getElementById('bookingSuccessModal').style.display = 'block';
            document.getElementById('continueToCartBtn').focus();
        }

        function closeBookingModal() {
            document.getElementById('bookingSuccessModal').style.display = 'none';
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('bookingSuccessModal');
            if (event.target === modal) {
                closeBookingModal();
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeBookingModal();
            }
        });

        // Test continue button
        document.getElementById('continueToCartBtn').onclick = function() {
            alert('Continue to payment functionality would redirect to payment page');
        };
    </script>
</body>
</html> 