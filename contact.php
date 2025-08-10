<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $required_fields = ['firstName', 'lastName', 'email', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Validate email
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    // Sanitize input
    $firstName = htmlspecialchars(trim($input['firstName']));
    $lastName = htmlspecialchars(trim($input['lastName']));
    $email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
    $phone = isset($input['phone']) ? htmlspecialchars(trim($input['phone'])) : '';
    $subject = htmlspecialchars(trim($input['subject']));
    $message = htmlspecialchars(trim($input['message']));
    
    // Get client information
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Connect to database
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Insert contact message
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (first_name, last_name, email, phone, subject, message, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $firstName,
        $lastName,
        $email,
        $phone,
        $subject,
        $message,
        $ipAddress,
        $userAgent
    ]);
    
    if (!$result) {
        throw new Exception('Failed to save contact message');
    }
    
    $messageId = $pdo->lastInsertId();
    
    // Send email notification to admin (optional)
    $adminEmail = getSystemSetting('contact_email', 'info@vango.com');
    $siteName = getSystemSetting('site_name', 'VanGo');
    
    $emailSubject = "New Contact Message: $subject";
    $emailBody = "
    New contact message received from $firstName $lastName
    
    Details:
    - Name: $firstName $lastName
    - Email: $email
    - Phone: $phone
    - Subject: $subject
    - Message: $message
    - Date: " . date('Y-m-d H:i:s') . "
    - IP: $ipAddress
    
    Message ID: $messageId
    ";
    
    // Uncomment the following lines to enable email notifications
    // mail($adminEmail, $emailSubject, $emailBody, "From: $siteName <noreply@vango.com>");
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We will get back to you soon.',
        'message_id' => $messageId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 