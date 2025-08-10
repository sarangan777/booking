<?php
/**
 * Van Booking System - Admin Panel
 * Manage bookings, view statistics, and update booking status
 */

require_once 'database.php';

// Simple authentication (in production, use proper authentication)
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        // Simple login check (replace with proper authentication)
        if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
        } else {
            $login_error = 'Invalid credentials';
        }
    }
    
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Show login form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>VanGo Admin - Login</title>
            <link rel="stylesheet" href="styles.css">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style>
                .login-container {
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .login-form {
                    background: white;
                    padding: 2rem;
                    border-radius: 20px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                    width: 100%;
                    max-width: 400px;
                    text-align: center;
                }
                .login-form h1 {
                    color: #333;
                    margin-bottom: 2rem;
                }
                .login-form input {
                    width: 100%;
                    padding: 12px 15px;
                    margin-bottom: 1rem;
                    border: 2px solid #e1e5e9;
                    border-radius: 10px;
                    font-size: 1rem;
                }
                .login-form button {
                    width: 100%;
                    padding: 12px;
                    background: linear-gradient(45deg, #667eea, #764ba2);
                    color: white;
                    border: none;
                    border-radius: 10px;
                    font-size: 1rem;
                    cursor: pointer;
                }
                .error {
                    color: #dc3545;
                    margin-bottom: 1rem;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <div class="login-form">
                    <h1><i class="fas fa-van"></i> VanGo Admin</h1>
                    <?php if (isset($login_error)): ?>
                        <div class="error"><?php echo $login_error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <button type="submit" name="login">Login</button>
                    </form>
                    <p style="margin-top: 1rem; color: #666;">
                        Default: admin / admin123
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit();
}

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $bookingId = $_POST['booking_id'];
    $status = $_POST['status'];
    
    if (updateBookingStatus($bookingId, $status)) {
        $success_message = 'Booking status updated successfully';
    } else {
        $error_message = 'Failed to update booking status';
    }
}

// Get all bookings
$bookings = getAllBookings();

// Calculate statistics
$totalBookings = count($bookings);
$pendingBookings = count(array_filter($bookings, fn($b) => $b['status'] === 'pending'));
$confirmedBookings = count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed'));
$cancelledBookings = count(array_filter($bookings, fn($b) => $b['status'] === 'cancelled'));
$totalRevenue = array_sum(array_column($bookings, 'total_price'));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VanGo Admin - Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #667eea;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .stat-card p {
            color: #666;
            margin: 0;
        }
        .bookings-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1rem;
        }
        .table-header h2 {
            margin: 0;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background: #d1edff;
            color: #0c5460;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .search-filter {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: center;
        }
        .search-filter input,
        .search-filter select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-van"></i> VanGo Admin Dashboard</h1>
                <p>Manage bookings and view system statistics</p>
            </div>
            <a href="?logout=1" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $totalBookings; ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $pendingBookings; ?></h3>
                <p>Pending Bookings</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $confirmedBookings; ?></h3>
                <p>Confirmed Bookings</p>
            </div>
            <div class="stat-card">
                <h3>$<?php echo number_format($totalRevenue, 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="bookings-table">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> All Bookings</h2>
            </div>
            
            <div class="search-filter">
                <input type="text" id="searchInput" placeholder="Search by name, email, or reference...">
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="table-container">
                <table id="bookingsTable">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Van Type</th>
                            <th>Pickup Date</th>
                            <th>Return Date</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                        <tr data-status="<?php echo $booking['status']; ?>">
                            <td>
                                <strong><?php echo htmlspecialchars($booking['booking_reference']); ?></strong>
                                <br>
                                <small><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($booking['name']); ?></strong>
                                <br>
                                <small><?php echo htmlspecialchars($booking['email']); ?></small>
                                <br>
                                <small><?php echo htmlspecialchars($booking['phone']); ?></small>
                            </td>
                            <td>
                                <strong><?php echo ucfirst($booking['van_type']); ?> Van</strong>
                                <br>
                                <small><?php echo htmlspecialchars($booking['pickup_location']); ?></small>
                            </td>
                            <td>
                                <?php echo date('M j, Y', strtotime($booking['pickup_date'])); ?>
                                <br>
                                <small><?php echo date('g:i A', strtotime($booking['pickup_time'])); ?></small>
                            </td>
                            <td>
                                <?php echo date('M j, Y', strtotime($booking['return_date'])); ?>
                                <br>
                                <small><?php echo date('g:i A', strtotime($booking['return_time'])); ?></small>
                            </td>
                            <td>
                                <strong>$<?php echo number_format($booking['total_price'], 2); ?></strong>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-primary" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <?php if ($booking['status'] === 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" name="update_status" class="btn btn-success">
                                            <i class="fas fa-check"></i> Confirm
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" name="update_status" class="btn btn-danger">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);

        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const table = document.getElementById('bookingsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                const status = row.getAttribute('data-status');
                
                const matchesSearch = text.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;
                
                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            }
        }

        function viewBooking(bookingId) {
            // In a real application, this would open a modal or redirect to a detailed view
            alert('View booking details for ID: ' + bookingId);
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html> 