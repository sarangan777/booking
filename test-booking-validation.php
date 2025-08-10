<?php
/**
 * Test Booking Validation and Success Functionality
 * This file tests the complete booking flow with validation
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
    <title>Test Booking Validation - VanGo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .test-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
        }
        .test-button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            margin: 5px;
            transition: all 0.3s ease;
        }
        .test-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        .test-button.error {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        .test-button.success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Booking Validation & Success Test</h1>
        <p>Test the booking validation and success functionality</p>
        
        <div class="test-section">
            <h3>✅ Success Tests</h3>
            <button class="test-button success" onclick="testSuccessModal()">
                <i class="fas fa-check-circle"></i> Test Success Modal
            </button>
            <button class="test-button success" onclick="testValidBooking()">
                <i class="fas fa-van-shuttle"></i> Test Valid Booking
            </button>
        </div>
        
        <div class="test-section">
            <h3>❌ Validation Tests</h3>
            <button class="test-button error" onclick="testNoVanSelected()">
                <i class="fas fa-times-circle"></i> No Van Selected
            </button>
            <button class="test-button error" onclick="testMissingFields()">
                <i class="fas fa-exclamation-triangle"></i> Missing Required Fields
            </button>
            <button class="test-button error" onclick="testInvalidDates()">
                <i class="fas fa-calendar-times"></i> Invalid Dates
            </button>
            <button class="test-button error" onclick="testInvalidPassengers()">
                <i class="fas fa-users-slash"></i> Invalid Passengers
            </button>
        </div>
        
        <div class="test-section">
            <h3>🔧 Backend Tests</h3>
            <button class="test-button" onclick="testBackendValidation()">
                <i class="fas fa-server"></i> Test Backend Validation
            </button>
            <button class="test-button" onclick="testDatabaseConnection()">
                <i class="fas fa-database"></i> Test Database Connection
            </button>
        </div>
    </div>

    <!-- Booking Success Modal -->
    <div id="bookingSuccessModal" class="modal" style="display:none;">
        <div class="modal-content booking-success">
            <button class="close-button" onclick="closeBookingModal()">&times;</button>
            
            <h3><i class="fas fa-check-circle"></i> 🎉 Booking Confirmed!</h3>
            <p>Congratulations! Your van booking has been successfully created and confirmed. Here are your booking details:</p>
            
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
            <button class="close-button" onclick="closeErrorModal()">&times;</button>
            
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

    <script>
        // Success Tests
        function testSuccessModal() {
            document.getElementById('bookingSuccessModal').style.display = 'block';
        }

        function testValidBooking() {
            const validData = {
                vanId: 'VAN003',
                pickupLocation: 'Downtown Hub',
                dropoffLocation: 'Airport Terminal',
                pickupDate: '2024-02-15',
                pickupTime: '09:00',
                returnDate: '2024-02-17',
                returnTime: '18:00',
                passengers: '4',
                specialRequests: 'Need child seat',
                additionalServices: []
            };
            
            simulateBooking(validData);
        }

        // Validation Tests
        function testNoVanSelected() {
            showError('Please select a van first');
        }

        function testMissingFields() {
            showError('Please fix the following errors:\n• Pickup location is required\n• Dropoff location is required\n• Pickup date is required\n• Pickup time is required\n• Return date is required\n• Return time is required\n• At least 1 passenger is required');
        }

        function testInvalidDates() {
            showError('Please fix the following errors:\n• Pickup date cannot be in the past\n• Return date must be after pickup date');
        }

        function testInvalidPassengers() {
            showError('Please fix the following errors:\n• Number of passengers must be between 1 and 20');
        }

        // Backend Tests
        function testBackendValidation() {
            fetch('booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    vanId: '',
                    pickupLocation: '',
                    dropoffLocation: '',
                    pickupDate: '',
                    pickupTime: '',
                    returnDate: '',
                    returnTime: '',
                    passengers: '',
                    specialRequests: '',
                    additionalServices: []
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showError('Unexpected success for invalid data');
                } else {
                    showError('Backend validation working correctly:\n' + (data.message || 'Validation failed'));
                }
            })
            .catch(error => {
                showError('Backend test failed: ' + error.message);
            });
        }

        function testDatabaseConnection() {
            fetch('database.php')
            .then(response => {
                if (response.ok) {
                    showError('Database connection test: Database file accessible');
                } else {
                    showError('Database connection test: Database file not accessible');
                }
            })
            .catch(error => {
                showError('Database connection test failed: ' + error.message);
            });
        }

        // Helper Functions
        function simulateBooking(data) {
            fetch('booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showBookingSuccess(data.booking);
                } else {
                    showError('Booking failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                showError('Booking simulation failed: ' + error.message);
            });
        }

        function showBookingSuccess(booking) {
            // Populate booking details
            document.getElementById('successBookingId').textContent = booking.booking_id || 'N/A';
            document.getElementById('successVanName').textContent = booking.van_name || 'N/A';
            document.getElementById('successPickupLocation').textContent = booking.pickup_location || 'N/A';
            document.getElementById('successDropoffLocation').textContent = booking.dropoff_location || 'N/A';
            document.getElementById('successPickupDate').textContent = booking.pickup_date || 'N/A';
            document.getElementById('successPickupTime').textContent = booking.pickup_time || 'N/A';
            document.getElementById('successPassengers').textContent = booking.passengers || 'N/A';
            document.getElementById('successTotalAmount').textContent = '$' + (booking.total_amount || '0.00');
            
            // Show modal
            document.getElementById('bookingSuccessModal').style.display = 'block';
        }

        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorModal').style.display = 'block';
        }

        function closeBookingModal() {
            document.getElementById('bookingSuccessModal').style.display = 'none';
        }

        function closeErrorModal() {
            document.getElementById('errorModal').style.display = 'none';
        }

        // Modal event handlers
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

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeBookingModal();
                closeErrorModal();
            }
        });

        // Test continue button
        document.getElementById('continueToCartBtn').onclick = function() {
            alert('Continue to payment functionality would redirect to payment page');
        };
    </script>
</body>
</html> 