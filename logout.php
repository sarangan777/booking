<?php
/**
 * VanGo Logout Handler
 * Handles both user and admin logout and session cleanup
 */

// Start session
session_start();

// Log the logout
if (isset($_SESSION['user_email'])) {
    error_log("User logout: {$_SESSION['user_email']} at " . date('Y-m-d H:i:s'));
} elseif (isset($_SESSION['admin_email'])) {
    error_log("Admin logout: {$_SESSION['admin_email']} at " . date('Y-m-d H:i:s'));
}

// Check if it's an admin logout
$isAdminLogout = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Destroy all session data
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect based on user type
if ($isAdminLogout) {
    header('Location: admin-login.html');
} else {
    header('Location: index.php');
}
exit;
?> 