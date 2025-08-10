<?php
/**
 * System Settings Configuration Endpoint
 * Configures initial system settings for VanGo
 */

// Include database functions
require_once 'database.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        exit();
    }

    // Default system settings
    $defaultSettings = [
        'site_name' => 'VanGo',
        'site_description' => 'Premium Van Booking Service',
        'contact_email' => 'info@vango.com',
        'contact_phone' => '+1 (555) 123-4567',
        'contact_address' => '123 Main St, City, State 12345',
        'booking_advance_days' => '30',
        'cancellation_hours' => '24',
        'tax_rate' => '8.5',
        'currency' => 'USD',
        'timezone' => 'America/New_York',
        'maintenance_mode' => 'false',
        'email_notifications' => 'true',
        'sms_notifications' => 'false',
        'max_passengers' => '15',
        'min_booking_hours' => '1',
        'max_booking_days' => '30'
    ];

    // Insert or update settings
    $stmt = $pdo->prepare("
        INSERT INTO system_settings (setting_key, setting_value, description) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");

    $success = true;
    foreach ($defaultSettings as $key => $value) {
        $description = ucwords(str_replace('_', ' ', $key));
        if (!$stmt->execute([$key, $value, $description])) {
            $success = false;
            break;
        }
    }

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'System settings configured successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to configure system settings'
        ]);
    }
} catch (Exception $e) {
    error_log("System settings configuration error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while configuring system settings'
    ]);
}
?> 