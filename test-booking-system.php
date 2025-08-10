<?php
/**
 * VanGo Booking System Test
 * Tests booking functionality and database integration
 */

session_start();
require_once 'database.php';

echo "<h1>VanGo Booking System Test</h1>";

// Test database connection
echo "<h2>1. Database Connection Test</h2>";
$pdo = getDatabaseConnection();
if ($pdo) {
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    exit;
}

// Test vans table
echo "<h2>2. Vans Table Test</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'vans'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Vans table exists!</p>";
        
        // Count vans
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM vans");
        $result = $stmt->fetch();
        echo "<p>Total vans in database: <strong>{$result['count']}</strong></p>";
        
        // Show available vans
        $stmt = $pdo->query("SELECT van_id, name, type, capacity, daily_rate, status FROM vans WHERE status = 'available' LIMIT 5");
        $vans = $stmt->fetchAll();
        
        if ($vans) {
            echo "<h3>Available Vans:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Van ID</th><th>Name</th><th>Type</th><th>Capacity</th><th>Daily Rate</th><th>Status</th></tr>";
            foreach ($vans as $van) {
                echo "<tr>";
                echo "<td>{$van['van_id']}</td>";
                echo "<td>{$van['name']}</td>";
                echo "<td>{$van['type']}</td>";
                echo "<td>{$van['capacity']}</td>";
                echo "<td>\${$van['daily_rate']}</td>";
                echo "<td>{$van['status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No available vans found.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Vans table does not exist!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking vans table: " . $e->getMessage() . "</p>";
}

// Test bookings table
echo "<h2>3. Bookings Table Test</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'bookings'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Bookings table exists!</p>";
        
        // Count bookings
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
        $result = $stmt->fetch();
        echo "<p>Total bookings in database: <strong>{$result['count']}</strong></p>";
        
        // Show recent bookings
        $stmt = $pdo->query("
            SELECT b.booking_id, b.user_id, b.van_id, b.pickup_date, b.total_amount, b.status, v.name as van_name 
            FROM bookings b 
            LEFT JOIN vans v ON b.van_id = v.van_id 
            ORDER BY b.created_at DESC 
            LIMIT 5
        ");
        $bookings = $stmt->fetchAll();
        
        if ($bookings) {
            echo "<h3>Recent Bookings:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Booking ID</th><th>User ID</th><th>Van</th><th>Pickup Date</th><th>Total Amount</th><th>Status</th></tr>";
            foreach ($bookings as $booking) {
                echo "<tr>";
                echo "<td>{$booking['booking_id']}</td>";
                echo "<td>{$booking['user_id']}</td>";
                echo "<td>{$booking['van_name']}</td>";
                echo "<td>{$booking['pickup_date']}</td>";
                echo "<td>\${$booking['total_amount']}</td>";
                echo "<td>{$booking['status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No bookings found.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Bookings table does not exist!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking bookings table: " . $e->getMessage() . "</p>";
}

// Test API endpoints
echo "<h2>4. API Endpoints Test</h2>";
echo "<p><strong>Get Vans Endpoint:</strong> <code>get-vans.php</code> - Returns available vans</p>";
echo "<p><strong>Booking Endpoint:</strong> <code>booking.php</code> - Accepts POST with booking data</p>";

// Test user session
echo "<h2>5. User Session Test</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ User is logged in!</p>";
    echo "<p><strong>User ID:</strong> {$_SESSION['user_id']}</p>";
    echo "<p><strong>User Name:</strong> {$_SESSION['user_name']}</p>";
    echo "<p><strong>User Email:</strong> {$_SESSION['user_email']}</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No user logged in.</p>";
    echo "<p><a href='auth.html'>Login to test booking</a></p>";
}

// Test van creation
echo "<h2>6. Test Van Creation</h2>";
echo "<form method='POST' action='test-booking-system.php'>";
echo "<p><label>Van Name: <input type='text' name='van_name' value='Test Van'></label></p>";
echo "<p><label>Van Type: <select name='van_type'>";
echo "<option value='passenger'>Passenger</option>";
echo "<option value='luxury'>Luxury</option>";
echo "<option value='cargo'>Cargo</option>";
echo "<option value='minibus'>Minibus</option>";
echo "</select></label></p>";
echo "<p><label>Capacity: <input type='number' name='capacity' value='8'></label></p>";
echo "<p><label>Daily Rate: <input type='number' name='daily_rate' value='150' step='0.01'></label></p>";
echo "<p><input type='submit' name='create_test_van' value='Create Test Van'></p>";
echo "</form>";

// Handle test van creation
if (isset($_POST['create_test_van'])) {
    $vanName = $_POST['van_name'];
    $vanType = $_POST['van_type'];
    $capacity = $_POST['capacity'];
    $dailyRate = $_POST['daily_rate'];
    
    try {
        // Generate van ID
        $vanId = 'V' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        // Create test van
        $stmt = $pdo->prepare("
            INSERT INTO vans (van_id, name, type, model, year, capacity, daily_rate, hourly_rate, description, features, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', CURRENT_TIMESTAMP)
        ");
        
        $stmt->execute([
            $vanId,
            $vanName,
            $vanType,
            'Test Model',
            2024,
            $capacity,
            $dailyRate,
            $dailyRate / 8, // Hourly rate
            'Test van for booking system',
            json_encode(['AC', 'WiFi', 'GPS'])
        ]);
        
        echo "<p style='color: green;'>✅ Test van created successfully!</p>";
        echo "<p><strong>Van ID:</strong> {$vanId}</p>";
        echo "<p><strong>Name:</strong> {$vanName}</p>";
        echo "<p><strong>Type:</strong> {$vanType}</p>";
        echo "<p><strong>Daily Rate:</strong> \${$dailyRate}</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error creating test van: " . $e->getMessage() . "</p>";
    }
}

// Test booking creation (if user is logged in)
if (isset($_SESSION['user_id'])) {
    echo "<h2>7. Test Booking Creation</h2>";
    echo "<form method='POST' action='test-booking-system.php'>";
    echo "<p><label>Van ID: <input type='text' name='test_van_id' placeholder='Enter van ID'></label></p>";
    echo "<p><label>Pickup Date: <input type='date' name='test_pickup_date'></label></p>";
    echo "<p><label>Return Date: <input type='date' name='test_return_date'></label></p>";
    echo "<p><label>Pickup Location: <input type='text' name='test_pickup_location' value='Test Location'></label></p>";
    echo "<p><label>Dropoff Location: <input type='text' name='test_dropoff_location' value='Test Destination'></label></p>";
    echo "<p><input type='submit' name='create_test_booking' value='Create Test Booking'></p>";
    echo "</form>";
    
    // Handle test booking creation
    if (isset($_POST['create_test_booking'])) {
        $vanId = $_POST['test_van_id'];
        $pickupDate = $_POST['test_pickup_date'];
        $returnDate = $_POST['test_return_date'];
        $pickupLocation = $_POST['test_pickup_location'];
        $dropoffLocation = $_POST['test_dropoff_location'];
        
        if ($vanId && $pickupDate && $returnDate) {
            try {
                // Generate booking ID
                $bookingId = 'B' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                
                // Create test booking
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (booking_id, user_id, van_id, pickup_location, dropoff_location,
                    pickup_date, pickup_time, return_date, return_time, passengers, total_amount, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)
                ");
                
                $stmt->execute([
                    $bookingId,
                    $_SESSION['user_id'],
                    $vanId,
                    $pickupLocation,
                    $dropoffLocation,
                    $pickupDate,
                    '09:00:00',
                    $returnDate,
                    '17:00:00',
                    2,
                    150.00
                ]);
                
                echo "<p style='color: green;'>✅ Test booking created successfully!</p>";
                echo "<p><strong>Booking ID:</strong> {$bookingId}</p>";
                echo "<p><strong>Van ID:</strong> {$vanId}</p>";
                echo "<p><strong>Pickup Date:</strong> {$pickupDate}</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error creating test booking: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Please fill in all required fields.</p>";
        }
    }
}

echo "<h2>8. System Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";
echo "<p><strong>Database Host:</strong> " . DB_HOST . "</p>";
echo "<p><strong>Database Name:</strong> " . DB_NAME . "</p>";

echo "<hr>";
echo "<p><a href='index.php'>← Back to Home</a> | <a href='book-van.php'>Book a Van</a> | <a href='auth.html'>Login/Signup</a> | <a href='setup.php'>Database Setup</a></p>";
?> 