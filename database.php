<?php
/**
 * VanGo Database Management
 * Handles database connections and common database operations
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'vango_booking');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection
 * @return PDO|false Database connection or false on failure
 */
function getDatabaseConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            return $pdo;
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return false;
        }
    }
    
    return $pdo;
}

/**
 * Create database if it doesn't exist
 * @return bool Success status
 */
function createDatabase() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $pdo->exec($sql);
        
        return true;
    } catch (PDOException $e) {
        error_log("Database creation failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Create all tables for the van booking system
 * @return bool Success status
 */
function createTables() {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        return false;
    }
    
    try {
        // Users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(20) UNIQUE NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(20) NOT NULL,
            password VARCHAR(255) NOT NULL,
            address TEXT,
            date_of_birth DATE,
            newsletter_subscription BOOLEAN DEFAULT FALSE,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            INDEX idx_email (email),
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        )";
        $pdo->exec($sql);
        
        // Vans table
        $sql = "CREATE TABLE IF NOT EXISTS vans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            van_id VARCHAR(20) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            type ENUM('Economy', 'Standard', 'Premium', 'Luxury') NOT NULL,
            model VARCHAR(100) NOT NULL,
            registration_number VARCHAR(50) UNIQUE NOT NULL,
            year INT NOT NULL,
            seats INT NOT NULL DEFAULT 4,
            capacity INT NOT NULL,
            daily_rate DECIMAL(10,2) NOT NULL,
            hourly_rate DECIMAL(10,2) NOT NULL,
            description TEXT,
            features JSON,
            images JSON,
            status ENUM('available', 'maintenance', 'booked', 'out_of_service') DEFAULT 'available',
            location VARCHAR(200),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_van_id (van_id),
            INDEX idx_registration (registration_number),
            INDEX idx_type (type),
            INDEX idx_status (status),
            INDEX idx_seats (seats),
            INDEX idx_capacity (capacity)
        )";
        $pdo->exec($sql);
        
        // Bookings table
        $sql = "CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id VARCHAR(20) UNIQUE NOT NULL,
            user_id VARCHAR(20) NOT NULL,
            van_id VARCHAR(20) NOT NULL,
            pickup_location VARCHAR(200) NOT NULL,
            dropoff_location VARCHAR(200) NOT NULL,
            pickup_date DATE NOT NULL,
            pickup_time TIME NOT NULL,
            return_date DATE,
            return_time TIME,
            passengers INT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            special_requests TEXT,
            status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
            payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (van_id) REFERENCES vans(van_id) ON DELETE CASCADE,
            INDEX idx_booking_id (booking_id),
            INDEX idx_user_id (user_id),
            INDEX idx_van_id (van_id),
            INDEX idx_status (status),
            INDEX idx_pickup_date (pickup_date)
        )";
        $pdo->exec($sql);
        
        // Payments table
        $sql = "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payment_id VARCHAR(20) UNIQUE NOT NULL,
            booking_id VARCHAR(20) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('credit_card', 'debit_card', 'paypal', 'cash') NOT NULL,
            transaction_id VARCHAR(100),
            status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
            INDEX idx_payment_id (payment_id),
            INDEX idx_booking_id (booking_id),
            INDEX idx_status (status)
        )";
        $pdo->exec($sql);
        
        // Reviews table
        $sql = "CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id VARCHAR(20) NOT NULL,
            user_id VARCHAR(20) NOT NULL,
            van_id VARCHAR(20) NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (van_id) REFERENCES vans(van_id) ON DELETE CASCADE,
            UNIQUE KEY unique_booking_review (booking_id),
            INDEX idx_van_id (van_id),
            INDEX idx_rating (rating)
        )";
        $pdo->exec($sql);
        
        // Admin users table
        $sql = "CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id VARCHAR(20) UNIQUE NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            role ENUM('super_admin', 'admin', 'manager') DEFAULT 'admin',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            INDEX idx_admin_id (admin_id),
            INDEX idx_username (username),
            INDEX idx_email (email)
        )";
        $pdo->exec($sql);
        
        // System settings table
        $sql = "CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_setting_key (setting_key)
        )";
        $pdo->exec($sql);
        
        // Contact messages table
        $sql = "CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            subject VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        )";
        $pdo->exec($sql);
        
        return true;
    } catch (PDOException $e) {
        error_log("Table creation failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Insert sample data for testing
 * @return bool Success status
 */
function insertSampleData() {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        return false;
    }
    
    try {
        // Insert sample vans
        $vans = [
            [
                'van_id' => 'VAN001',
                'name' => 'Economy Van',
                'type' => 'Economy',
                'model' => 'Toyota Hiace',
                'registration_number' => 'VG-001-ECO',
                'year' => 2023,
                'seats' => 4,
                'capacity' => 4,
                'daily_rate' => 80.00,
                'hourly_rate' => 15.00,
                'description' => 'Affordable and reliable van for small groups and short trips.',
                'features' => json_encode(['air_conditioning', 'comfortable_seating', 'storage_space']),
                'images' => json_encode(['van1.jpg', 'van1_interior.jpg']),
                'status' => 'available',
                'location' => 'Downtown Hub'
            ],
            [
                'van_id' => 'VAN002',
                'name' => 'Standard Van',
                'type' => 'Standard',
                'model' => 'Ford Transit',
                'registration_number' => 'VG-002-STD',
                'year' => 2023,
                'seats' => 6,
                'capacity' => 6,
                'daily_rate' => 120.00,
                'hourly_rate' => 20.00,
                'description' => 'Comfortable van perfect for family trips and group travel.',
                'features' => json_encode(['climate_control', 'comfortable_seating', 'wifi', 'storage']),
                'images' => json_encode(['van2.jpg', 'van2_interior.jpg']),
                'status' => 'available',
                'location' => 'Airport Terminal'
            ],
            [
                'van_id' => 'VAN003',
                'name' => 'Premium Van',
                'type' => 'Premium',
                'model' => 'Mercedes Sprinter',
                'registration_number' => 'VG-003-PRM',
                'year' => 2023,
                'seats' => 8,
                'capacity' => 8,
                'daily_rate' => 180.00,
                'hourly_rate' => 25.00,
                'description' => 'Premium van with luxury features for business and special occasions.',
                'features' => json_encode(['leather_seats', 'climate_control', 'entertainment', 'wifi', 'refreshments']),
                'images' => json_encode(['van3.jpg', 'van3_interior.jpg']),
                'status' => 'available',
                'location' => 'City Center'
            ],
            [
                'van_id' => 'VAN004',
                'name' => 'Luxury Van',
                'type' => 'Luxury',
                'model' => 'Mercedes V-Class',
                'registration_number' => 'VG-004-LUX',
                'year' => 2023,
                'seats' => 6,
                'capacity' => 6,
                'daily_rate' => 250.00,
                'hourly_rate' => 35.00,
                'description' => 'Ultimate luxury van with premium amenities and professional chauffeur service.',
                'features' => json_encode(['premium_leather', 'climate_control', 'entertainment_system', 'wifi', 'refreshments', 'chauffeur']),
                'images' => json_encode(['van4.jpg', 'van4_interior.jpg']),
                'status' => 'available',
                'location' => 'Luxury Hub'
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO vans (van_id, name, type, model, registration_number, year, seats, capacity, daily_rate, hourly_rate, 
                             description, features, images, status, location) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($vans as $van) {
            $stmt->execute([
                $van['van_id'], $van['name'], $van['type'], $van['model'], $van['registration_number'], $van['year'],
                $van['seats'], $van['capacity'], $van['daily_rate'], $van['hourly_rate'], $van['description'],
                $van['features'], $van['images'], $van['status'], $van['location']
            ]);
        }
        
        // Insert admin user with specified credentials
        $adminPassword = password_hash('admin1234', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO admin_users (admin_id, username, email, password, first_name, last_name, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            password = VALUES(password),
            first_name = VALUES(first_name),
            last_name = VALUES(last_name),
            role = VALUES(role)
        ");
        $stmt->execute(['ADMIN001', 'admin', 'admin@gmail.com', $adminPassword, 'System', 'Administrator', 'super_admin']);
        
        // Insert system settings
        $settings = [
            ['site_name', 'VanGo', 'Website name'],
            ['site_description', 'Premium Van Booking Service', 'Website description'],
            ['contact_email', 'info@vango.com', 'Contact email address'],
            ['contact_phone', '+1 (555) 123-4567', 'Contact phone number'],
            ['booking_advance_days', '30', 'Maximum days in advance for booking'],
            ['cancellation_hours', '24', 'Hours before pickup for free cancellation'],
            ['tax_rate', '8.5', 'Tax rate percentage']
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value, description) 
            VALUES (?, ?, ?)
        ");
        
        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Sample data insertion failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if database exists and has required tables
 * @return bool Success status
 */
function checkDatabaseSetup() {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $tables = ['users', 'vans', 'bookings', 'payments', 'reviews', 'admin_users', 'system_settings', 'contact_messages'];
        
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if (!$stmt->fetch()) {
                return false;
            }
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Database setup check failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get system setting value
 * @param string $key Setting key
 * @param mixed $default Default value if setting not found
 * @return mixed Setting value or default
 */
function getSystemSetting($key, $default = null) {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        return $default;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        error_log("Failed to get system setting: " . $e->getMessage());
        return $default;
    }
}

/**
 * Update system setting
 * @param string $key Setting key
 * @param string $value Setting value
 * @return bool Success status
 */
function updateSystemSetting($key, $value) {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        return $stmt->execute([$key, $value]);
    } catch (PDOException $e) {
        error_log("Failed to update system setting: " . $e->getMessage());
        return false;
    }
}
?> 