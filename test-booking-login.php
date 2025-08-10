<?php
session_start();
$_SESSION["user_id"] = "USER001";
$_SESSION["user_name"] = "Test User";
$_SESSION["user_email"] = "test@example.com";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Booking</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Test Booking Page</h1>
    <p class="success">✓ User logged in: <?php echo $_SESSION["user_name"]; ?></p>
    <p class="info">You can now test the booking form.</p>
    <p><a href="book-van.php" style="background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Booking Page</a></p>
</body>
</html>