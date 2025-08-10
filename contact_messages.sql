-- Contact Messages Table Schema
-- For VanGo Booking System

USE vango_booking;

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

-- Add comment for documentation
ALTER TABLE contact_messages COMMENT = 'Customer contact form submissions and inquiries';

-- Sample contact message (optional)
-- INSERT INTO contact_messages (first_name, last_name, email, phone, subject, message) VALUES
-- ('John', 'Doe', 'john.doe@example.com', '+1 (555) 123-4567', 'General Inquiry', 'I would like to know more about your van booking services.'); 