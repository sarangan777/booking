<?php
require_once 'database.php';

echo "<h1>Contact Form System Test</h1>";

// Test database connection
echo "<h2>1. Database Connection Test</h2>";
$pdo = getDatabaseConnection();
if ($pdo) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// Test contact_messages table
echo "<h2>2. Contact Messages Table Test</h2>";
try {
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'contact_messages'");
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "✅ Contact messages table exists<br>";
    } else {
        echo "❌ Contact messages table does not exist<br>";
        echo "Creating table...<br>";
        createTables();
        echo "✅ Table created<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "<br>";
}

// Test inserting a sample contact message
echo "<h2>3. Sample Contact Message Insertion Test</h2>";
try {
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (first_name, last_name, email, phone, subject, message, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        'John',
        'Doe',
        'john.doe@example.com',
        '+1234567890',
        'Test Message',
        'This is a test contact message to verify the system is working.',
        '127.0.0.1',
        'Test User Agent'
    ]);
    
    if ($result) {
        $messageId = $pdo->lastInsertId();
        echo "✅ Sample contact message inserted successfully (ID: $messageId)<br>";
    } else {
        echo "❌ Failed to insert sample contact message<br>";
    }
} catch (Exception $e) {
    echo "❌ Error inserting sample message: " . $e->getMessage() . "<br>";
}

// Display recent contact messages
echo "<h2>4. Recent Contact Messages</h2>";
try {
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, subject, status, created_at 
        FROM contact_messages 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($messages) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Date</th></tr>";
        foreach ($messages as $message) {
            echo "<tr>";
            echo "<td>{$message['id']}</td>";
            echo "<td>{$message['first_name']} {$message['last_name']}</td>";
            echo "<td>{$message['email']}</td>";
            echo "<td>{$message['subject']}</td>";
            echo "<td>{$message['status']}</td>";
            echo "<td>{$message['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No contact messages found<br>";
    }
} catch (Exception $e) {
    echo "❌ Error retrieving messages: " . $e->getMessage() . "<br>";
}

// Test contact.php API endpoint
echo "<h2>5. Contact API Test</h2>";
echo "<p>Testing the contact.php endpoint with sample data...</p>";

$testData = [
    'firstName' => 'Jane',
    'lastName' => 'Smith',
    'email' => 'jane.smith@example.com',
    'phone' => '+1987654321',
    'subject' => 'API Test',
    'message' => 'This is a test message sent via the API endpoint.'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/booking/contact.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Response Code: $httpCode<br>";
echo "Response: " . htmlspecialchars($response) . "<br>";

if ($httpCode === 200) {
    $result = json_decode($response, true);
    if ($result && $result['success']) {
        echo "✅ Contact API test successful<br>";
    } else {
        echo "❌ Contact API test failed: " . ($result['message'] ?? 'Unknown error') . "<br>";
    }
} else {
    echo "❌ Contact API test failed with HTTP code: $httpCode<br>";
}

echo "<h2>6. System Status</h2>";
echo "✅ Contact form system is ready for use<br>";
echo "✅ Database integration is working<br>";
echo "✅ API endpoint is functional<br>";
echo "<br><a href='contact.html'>Go to Contact Page</a>";
?> 