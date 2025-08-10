-- VanGo Booking System Database Schema
-- Complete database structure for van booking application

-- Create database
CREATE DATABASE IF NOT EXISTS vango_booking 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE vango_booking;

-- Users table
CREATE TABLE IF NOT EXISTS users (
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
);

-- Vans table
CREATE TABLE IF NOT EXISTS vans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    van_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('Economy', 'Standard', 'Premium', 'Luxury') NOT NULL,
    model VARCHAR(100) NOT NULL,
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
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_seats (seats),
    INDEX idx_capacity (capacity)
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
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
    conduct_details TEXT,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    contact_name VARCHAR(100),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(30),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (van_id) REFERENCES vans(van_id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_id (user_id),
    INDEX idx_van_id (van_id),
    INDEX idx_status (status),
    INDEX idx_pickup_date (pickup_date)
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
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
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
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
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
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
);

-- System settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
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
);

-- Insert 5 sample users data
INSERT INTO users (user_id, first_name, last_name, email, phone, password, address, date_of_birth, newsletter_subscription, status) VALUES
('USER001', 'John', 'Smith', 'john.smith@email.com', '+1 (555) 123-4567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Main St, New York, NY 10001', '1990-05-15', TRUE, 'active'),
('USER002', 'Sarah', 'Johnson', 'sarah.johnson@email.com', '+1 (555) 234-5678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '456 Oak Ave, Los Angeles, CA 90210', '1985-08-22', FALSE, 'active'),
('USER003', 'Michael', 'Brown', 'michael.brown@email.com', '+1 (555) 345-6789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '789 Pine Rd, Chicago, IL 60601', '1992-12-10', TRUE, 'active'),
('USER004', 'Emily', 'Davis', 'emily.davis@email.com', '+1 (555) 456-7890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '321 Elm St, Miami, FL 33101', '1988-03-28', FALSE, 'inactive'),
('USER005', 'David', 'Wilson', 'david.wilson@email.com', '+1 (555) 567-8901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '654 Maple Dr, Seattle, WA 98101', '1995-07-14', TRUE, 'active');

-- Insert 5 sample vans data
INSERT INTO vans (van_id, name, type, model, year, seats, capacity, daily_rate, hourly_rate, description, features, images, status, location) VALUES
('VAN001', 'Economy Van', 'Economy', 'Toyota Hiace', 2023, 4, 4, 80.00, 15.00, 'Affordable and reliable van for small groups and short trips.', '["air_conditioning", "comfortable_seating", "storage_space"]', '["van1.jpg", "van1_interior.jpg"]', 'available', 'Downtown Hub'),
('VAN002', 'Standard Van', 'Standard', 'Ford Transit', 2023, 6, 6, 120.00, 20.00, 'Comfortable van perfect for family trips and group travel.', '["climate_control", "comfortable_seating", "wifi", "storage"]', '["van2.jpg", "van2_interior.jpg"]', 'available', 'Airport Terminal'),
('VAN003', 'Premium Van', 'Premium', 'Mercedes Sprinter', 2023, 8, 8, 180.00, 25.00, 'Premium van with luxury features for business and special occasions.', '["leather_seats", "climate_control", "entertainment", "wifi", "refreshments"]', '["van3.jpg", "van3_interior.jpg"]', 'available', 'City Center'),
('VAN004', 'Luxury Van', 'Luxury', 'Mercedes V-Class', 2023, 6, 6, 250.00, 35.00, 'Ultimate luxury van with premium amenities and professional chauffeur service.', '["premium_leather", "climate_control", "entertainment_system", "wifi", "refreshments", "chauffeur"]', '["van4.jpg", "van4_interior.jpg"]', 'available', 'Luxury Hub'),
('VAN005', 'Family Van', 'Standard', 'Honda Odyssey', 2023, 7, 7, 140.00, 22.00, 'Spacious family van with excellent safety features and entertainment options.', '["safety_features", "entertainment_system", "climate_control", "storage", "child_seats"]', '["van5.jpg", "van5_interior.jpg"]', 'maintenance', 'Family Hub');

-- Insert 5 sample bookings data
INSERT INTO bookings (booking_id, user_id, van_id, pickup_location, dropoff_location, pickup_date, pickup_time, return_date, return_time, passengers, total_amount, special_requests, status, payment_status) VALUES
('BK001', 'USER001', 'VAN001', 'Downtown Hub', 'Airport Terminal', '2024-01-15', '09:00:00', '2024-01-17', '18:00:00', 3, 240.00, 'Please have child seat available', 'completed', 'paid'),
('BK002', 'USER002', 'VAN003', 'City Center', 'Downtown Hub', '2024-01-20', '14:00:00', '2024-01-22', '16:00:00', 6, 360.00, 'Business trip - need professional driver', 'confirmed', 'paid'),
('BK003', 'USER003', 'VAN002', 'Airport Terminal', 'City Center', '2024-01-25', '10:00:00', '2024-01-26', '20:00:00', 4, 240.00, 'Airport pickup with luggage assistance', 'in_progress', 'paid'),
('BK004', 'USER004', 'VAN004', 'Luxury Hub', 'Downtown Hub', '2024-02-01', '16:00:00', '2024-02-03', '12:00:00', 4, 500.00, 'Wedding transportation - need elegant setup', 'pending', 'pending'),
('BK005', 'USER005', 'VAN005', 'Family Hub', 'Airport Terminal', '2024-02-05', '08:00:00', '2024-02-07', '22:00:00', 5, 280.00, 'Family vacation - need extra storage', 'confirmed', 'paid');

-- Insert 5 sample payments data
INSERT INTO payments (payment_id, booking_id, amount, payment_method, transaction_id, status) VALUES
('PAY001', 'BK001', 240.00, 'credit_card', 'TXN123456789', 'completed'),
('PAY002', 'BK002', 360.00, 'paypal', 'TXN987654321', 'completed'),
('PAY003', 'BK003', 240.00, 'debit_card', 'TXN456789123', 'completed'),
('PAY004', 'BK004', 500.00, 'credit_card', 'TXN789123456', 'pending'),
('PAY005', 'BK005', 280.00, 'cash', 'TXN321654987', 'completed');

-- Insert 5 sample reviews data
INSERT INTO reviews (booking_id, user_id, van_id, rating, comment) VALUES
('BK001', 'USER001', 'VAN001', 5, 'Excellent service! The van was clean and the driver was very professional. Highly recommend!'),
('BK002', 'USER002', 'VAN003', 4, 'Great experience for our business trip. The premium van was comfortable and well-maintained.'),
('BK003', 'USER003', 'VAN002', 5, 'Perfect airport pickup service. Driver was on time and helped with luggage.'),
('BK004', 'USER004', 'VAN004', 5, 'Luxury van was perfect for our wedding transportation. Elegant and comfortable.'),
('BK005', 'USER005', 'VAN005', 4, 'Family van was spacious and safe. Great for our vacation with kids.');

-- Insert 5 sample admin users data
INSERT INTO admin_users (admin_id, username, email, password, first_name, last_name, role, status) VALUES
('ADMIN001', 'admin', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'super_admin', 'active'),
('ADMIN002', 'manager', 'manager@vango.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Manager', 'admin', 'active'),
('ADMIN003', 'supervisor', 'supervisor@vango.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Supervisor', 'manager', 'active'),
('ADMIN004', 'coordinator', 'coordinator@vango.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike', 'Coordinator', 'manager', 'active'),
('ADMIN005', 'assistant', 'assistant@vango.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lisa', 'Assistant', 'admin', 'inactive');

-- Insert 5 sample system settings data
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'VanGo', 'Website name'),
('site_description', 'Premium Van Booking Service', 'Website description'),
('contact_email', 'info@vango.com', 'Contact email address'),
('contact_phone', '+1 (555) 123-4567', 'Contact phone number'),
('booking_advance_days', '30', 'Maximum days in advance for booking'),
('cancellation_hours', '24', 'Hours before pickup for free cancellation'),
('tax_rate', '8.5', 'Tax rate percentage'),
('maintenance_email', 'maintenance@vango.com', 'Maintenance team email'),
('support_email', 'support@vango.com', 'Customer support email'),
('emergency_phone', '+1 (555) 999-8888', 'Emergency contact number');

-- Insert 5 sample contact messages data
INSERT INTO contact_messages (first_name, last_name, email, phone, subject, message, status, ip_address) VALUES
('Alice', 'Johnson', 'alice.johnson@email.com', '+1 (555) 111-2222', 'General Inquiry', 'I would like to know more about your van booking services and pricing options.', 'new', '192.168.1.100'),
('Bob', 'Williams', 'bob.williams@email.com', '+1 (555) 222-3333', 'Booking Question', 'What is your cancellation policy for same-day bookings?', 'read', '192.168.1.101'),
('Carol', 'Miller', 'carol.miller@email.com', '+1 (555) 333-4444', 'Service Request', 'Do you provide airport pickup services for international flights?', 'replied', '192.168.1.102'),
('Daniel', 'Taylor', 'daniel.taylor@email.com', '+1 (555) 444-5555', 'Complaint', 'I had an issue with my recent booking. The van was late and not clean.', 'closed', '192.168.1.103'),
('Eva', 'Anderson', 'eva.anderson@email.com', '+1 (555) 555-6666', 'Partnership', 'I represent a travel agency and would like to discuss partnership opportunities.', 'new', '192.168.1.104');

-- Insert a sample booking record
INSERT INTO bookings (
    booking_id, user_id, van_id, pickup_location, dropoff_location,
    pickup_date, pickup_time, return_date, return_time, passengers,
    total_amount, special_requests, status, payment_status, created_at
) VALUES (
    'BK0001', 'USER001', 'VAN001', 'Default Pickup', 'Default Dropoff',
    '2024-07-01', '09:00', '2024-07-02', '18:00', 2,
    100.00, 'No special requests', 'confirmed', 'paid', NOW()
);

-- Create indexes for better performance
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_vans_created_at ON vans(created_at);
CREATE INDEX idx_bookings_created_at ON bookings(created_at);
CREATE INDEX idx_payments_created_at ON payments(payment_date);
CREATE INDEX idx_reviews_created_at ON reviews(created_at);
CREATE INDEX idx_admin_users_created_at ON admin_users(created_at);

-- Add comments for documentation
ALTER TABLE users COMMENT = 'Customer user accounts and profiles';
ALTER TABLE vans COMMENT = 'Available vans for booking with specifications and rates';
ALTER TABLE bookings COMMENT = 'Van booking records and reservation details';
ALTER TABLE payments COMMENT = 'Payment transactions for bookings';
ALTER TABLE reviews COMMENT = 'Customer reviews and ratings for completed bookings';
ALTER TABLE admin_users COMMENT = 'Administrative user accounts';
ALTER TABLE system_settings COMMENT = 'System configuration and settings';
ALTER TABLE contact_messages COMMENT = 'Customer contact form submissions';

-- Grant permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON vango_booking.* TO 'root'@'localhost';
-- FLUSH PRIVILEGES;
