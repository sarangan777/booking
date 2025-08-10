<?php
/**
 * Auto Login Page
 * This page automatically logs in a test user and redirects to booking
 */

// Start session
session_start();

// Set test user session
$_SESSION['user_id'] = 'USER001';
$_SESSION['user_name'] = 'Test User';
$_SESSION['user_email'] = 'test@example.com';

// Redirect to booking page after a short delay
?>
<!DOCTYPE html>
<html>
<head>
    <title>Auto Login - VanGo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        .success {
            color: #4ade80;
            font-size: 1.2em;
            margin: 20px 0;
        }
        .info {
            color: #e2e8f0;
            margin: 20px 0;
        }
        .btn {
            background: #4ade80;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 10px;
            display: inline-block;
            margin: 10px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(74, 222, 128, 0.3);
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Auto Login Successful!</h1>
        
        <div class="success">
            ✓ User logged in: <?php echo $_SESSION['user_name']; ?>
        </div>
        
        <div class="info">
            <p>You are now logged in as a test user.</p>
            <p>You can now test the booking system without any errors.</p>
        </div>
        
        <div class="spinner" id="spinner"></div>
        
        <div>
            <a href="book-van.php" class="btn">Go to Booking Page</a>
            <a href="auth.html" class="btn" style="background: #667eea;">Manual Login</a>
        </div>
        
        <div class="info" style="margin-top: 30px; font-size: 0.9em;">
            <p><strong>Test Credentials:</strong></p>
            <p>Email: test@example.com</p>
            <p>Password: password123</p>
        </div>
    </div>
    
    <script>
        // Auto redirect after 3 seconds
        setTimeout(function() {
            window.location.href = 'book-van.php';
        }, 3000);
    </script>
</body>
</html> 