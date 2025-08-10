<?php
/**
 * Test Admin Login System
 * Verifies admin login functionality and database setup
 */

require_once 'database.php';

echo "<h1>VanGo Admin Login System Test</h1>";

// Test database connection
echo "<h2>1. Database Connection Test</h2>";
$pdo = getDatabaseConnection();
if ($pdo) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
    exit;
}

// Test database setup
echo "<h2>2. Database Setup Test</h2>";
if (createDatabase()) {
    echo "<p style='color: green;'>✓ Database created successfully</p>";
} else {
    echo "<p style='color: red;'>✗ Database creation failed</p>";
}

if (createTables()) {
    echo "<p style='color: green;'>✓ Tables created successfully</p>";
} else {
    echo "<p style='color: red;'>✗ Table creation failed</p>";
}

if (insertSampleData()) {
    echo "<p style='color: green;'>✓ Sample data inserted successfully</p>";
} else {
    echo "<p style='color: red;'>✗ Sample data insertion failed</p>";
}

// Test admin user
echo "<h2>3. Admin User Test</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
    $stmt->execute(['admin@gmail.com']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p style='color: green;'>✓ Admin user found</p>";
        echo "<p><strong>Admin ID:</strong> " . htmlspecialchars($admin['admin_id']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
        echo "<p><strong>Name:</strong> " . htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) . "</p>";
        echo "<p><strong>Role:</strong> " . htmlspecialchars($admin['role']) . "</p>";
        
        // Test password verification
        if (password_verify('admin1234', $admin['password'])) {
            echo "<p style='color: green;'>✓ Password verification successful</p>";
        } else {
            echo "<p style='color: red;'>✗ Password verification failed</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Admin user not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error checking admin user: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test vans with seats
echo "<h2>4. Vans with Seats Test</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM vans ORDER BY daily_rate ASC");
    $stmt->execute();
    $vans = $stmt->fetchAll();
    
    if ($vans) {
        echo "<p style='color: green;'>✓ Found " . count($vans) . " vans</p>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Van ID</th><th>Name</th><th>Type</th><th>Seats</th><th>Capacity</th><th>Daily Rate</th><th>Status</th></tr>";
        
        foreach ($vans as $van) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($van['van_id']) . "</td>";
            echo "<td>" . htmlspecialchars($van['name']) . "</td>";
            echo "<td>" . htmlspecialchars($van['type']) . "</td>";
            echo "<td>" . htmlspecialchars($van['seats']) . "</td>";
            echo "<td>" . htmlspecialchars($van['capacity']) . "</td>";
            echo "<td>$" . number_format($van['daily_rate'], 2) . "</td>";
            echo "<td>" . htmlspecialchars($van['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ No vans found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error checking vans: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test admin login endpoint
echo "<h2>5. Admin Login Endpoint Test</h2>";
echo "<p>Testing admin login with default credentials...</p>";

// Simulate admin login request
$loginData = [
    'email' => 'admin@gmail.com',
    'password' => 'admin1234'
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($loginData)
    ]
]);

$response = file_get_contents('http://localhost/booking/admin-login.php', false, $context);

if ($response !== false) {
    $result = json_decode($response, true);
    if ($result && isset($result['success'])) {
        if ($result['success']) {
            echo "<p style='color: green;'>✓ Admin login successful</p>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($result['message']) . "</p>";
            echo "<p><strong>Redirect:</strong> " . htmlspecialchars($result['redirect']) . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Admin login failed: " . htmlspecialchars($result['message']) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Invalid response format</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Could not test admin login endpoint</p>";
}

echo "<h2>6. Navigation Links</h2>";
echo "<p><a href='admin-login.html' target='_blank'>Admin Login Page</a></p>";
echo "<p><a href='admin-dashboard.php' target='_blank'>Admin Dashboard</a> (requires login)</p>";
echo "<p><a href='index.php' target='_blank'>Home Page</a></p>";

echo "<h2>7. Default Admin Credentials</h2>";
echo "<div style='background: #f8f9fa; padding: 1rem; border-radius: 5px;'>";
echo "<p><strong>Email:</strong> admin@gmail.com</p>";
echo "<p><strong>Password:</strong> admin1234</p>";
echo "</div>";

echo "<h2>8. Test Instructions</h2>";
echo "<ol>";
echo "<li>Click on 'Admin Login Page' link above</li>";
echo "<li>Enter the default credentials: admin@gmail.com / admin1234</li>";
echo "<li>Click 'Login to Dashboard'</li>";
echo "<li>You should be redirected to the admin dashboard</li>";
echo "</ol>";
?> 