<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Booking Flow - VanGo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .test-button {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        .test-button:hover {
            background: #5a6fd8;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>VanGo Booking Flow Test</h1>
        
        <div class="test-section">
            <h2>1. Test Booking Confirmation to Cart Redirect</h2>
            <p>This simulates a successful booking that should redirect to the cart page.</p>
            <button class="test-button" onclick="testBookingRedirect()">Test Booking Redirect</button>
            <div id="redirect-result"></div>
        </div>
        
        <div class="test-section">
            <h2>2. Test Cart Page with Sample Data</h2>
            <p>This will open the cart page with sample booking data.</p>
            <button class="test-button" onclick="testCartPage()">Open Cart Page</button>
        </div>
        
        <div class="test-section">
            <h2>3. Current Session Status</h2>
            <p><strong>User Logged In:</strong> <?php echo isset($_SESSION['user_id']) ? 'Yes' : 'No'; ?></p>
            <p><strong>User ID:</strong> <?php echo $_SESSION['user_id'] ?? 'Not set'; ?></p>
            <p><strong>User Name:</strong> <?php echo $_SESSION['user_name'] ?? 'Not set'; ?></p>
        </div>
        
        <div class="test-section">
            <h2>4. Quick Links</h2>
            <a href="book-van.php" class="test-button">Go to Booking Page</a>
            <a href="add-to-cart.php" class="test-button">Go to Cart Page</a>
            <a href="index.php" class="test-button">Go to Home Page</a>
        </div>
    </div>

    <script>
        function testBookingRedirect() {
            const resultDiv = document.getElementById('redirect-result');
            resultDiv.innerHTML = '<p>Testing booking redirect...</p>';
            
            // Simulate booking data
            const bookingData = {
                vanId: 'VAN001',
                vanName: 'Premium Van',
                vanType: 'Premium',
                pickupLocation: 'Downtown Hub',
                dropoffLocation: 'Airport Terminal',
                pickupDate: '2024-01-20',
                pickupTime: '09:00',
                returnDate: '2024-01-20',
                returnTime: '17:00',
                passengers: '4',
                basePrice: 180,
                specialRequests: 'Test booking'
            };
            
            // Create cart URL
            const cartUrl = `add-to-cart.php?vanId=${bookingData.vanId}&vanName=${encodeURIComponent(bookingData.vanName)}&vanType=${encodeURIComponent(bookingData.vanType)}&pickupLocation=${encodeURIComponent(bookingData.pickupLocation)}&dropoffLocation=${encodeURIComponent(bookingData.dropoffLocation)}&pickupDate=${bookingData.pickupDate}&pickupTime=${bookingData.pickupTime}&returnDate=${bookingData.returnDate}&returnTime=${bookingData.returnTime}&passengers=${bookingData.passengers}&basePrice=${bookingData.basePrice}&specialRequests=${encodeURIComponent(bookingData.specialRequests)}`;
            
            resultDiv.innerHTML = `
                <p class="success">✅ Booking data prepared successfully!</p>
                <p><strong>Cart URL:</strong> ${cartUrl}</p>
                <button class="test-button" onclick="window.open('${cartUrl}', '_blank')">Open Cart Page</button>
            `;
        }
        
        function testCartPage() {
            // Open cart page with sample data
            const sampleUrl = 'add-to-cart.php?vanId=VAN001&vanName=Premium%20Van&vanType=Premium&pickupLocation=Downtown%20Hub&dropoffLocation=Airport%20Terminal&pickupDate=2024-01-20&pickupTime=09:00&returnDate=2024-01-20&returnTime=17:00&passengers=4&basePrice=180&specialRequests=Test%20booking';
            window.open(sampleUrl, '_blank');
        }
    </script>
</body>
</html> 