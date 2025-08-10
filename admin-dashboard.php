<?php
/**
 * VanGo Admin Dashboard
 * Main admin interface for managing the van booking system
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.html');
    exit;
}

// Include database functions
require_once 'database.php';

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Administrator';
$adminEmail = $_SESSION['admin_email'] ?? 'admin@vango.com';
$adminRole = $_SESSION['admin_role'] ?? 'admin';

// Initialize database if needed
$pdo = getDatabaseConnection();
if (!$pdo) {
    // Try to create database and tables
    createDatabase();
    createTables();
    insertSampleData();
    $pdo = getDatabaseConnection();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VanGo Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .admin-sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .sidebar-header .logo i {
            font-size: 2rem;
            color: #667eea;
        }
        
        .sidebar-header .logo h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .sidebar-nav .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 25px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        
        .sidebar-nav .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-nav .nav-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-right: 4px solid #667eea;
        }
        
        .sidebar-nav .nav-item i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            color: #ff6b6b;
        }
        
        .admin-main {
            flex: 1;
            margin-left: 280px;
            transition: all 0.3s ease;
        }
        
        .admin-header {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #666;
            cursor: pointer;
            padding: 8px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            background: #f8f9fa;
            color: #333;
        }
        
        .header-left h1 {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .admin-profile:hover {
            background: #e9ecef;
        }
        
        .admin-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .admin-name {
            font-weight: 500;
            color: #333;
        }
        
        .tab-content {
            display: none;
            padding: 30px;
            animation: fadeIn 0.3s ease;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 30px 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-info h3 {
            margin: 0 0 5px 0;
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }
        
        .stat-info p {
            margin: 0;
            color: #666;
            font-size: 0.95rem;
        }
        
        .dashboard-charts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .chart-card h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .content-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .search-input {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95rem;
            width: 250px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95rem;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .add-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th {
            background: #f8f9fa;
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #e9ecef;
        }
        
        .admin-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f3f4;
            color: #666;
        }
        
        .admin-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .vans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            overflow-y: auto;
        }
        
        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 700px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease;
            position: relative;
            max-height: 95vh;
            overflow-y: auto;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            padding: 25px 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .close-button {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #666;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .close-button:hover {
            background: #f8f9fa;
            color: #333;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 15px;
            background: #f8f9fa;
            border-radius: 0 0 15px 15px;
        }
        
        .van-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .cancel-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }
        
        .cancel-btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .save-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            min-width: 120px;
            justify-content: center;
        }
        
        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .save-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            background: #6c757d;
        }
        
        .delete-btn {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        
        .delete-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                transform: translateX(-100%);
            }
            
            .admin-sidebar.active {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .dashboard-charts {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-van-shuttle"></i>
                    <h2>VanGo Admin</h2>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active" data-tab="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#bookings" class="nav-item" data-tab="bookings">
                    <i class="fas fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>
                <a href="#vans" class="nav-item" data-tab="vans">
                    <i class="fas fa-van-shuttle"></i>
                    <span>Van Management</span>
                </a>
                <a href="#customers" class="nav-item" data-tab="customers">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <a href="#reports" class="nav-item" data-tab="reports">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="#settings" class="nav-item" data-tab="settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Header -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 id="page-title">Dashboard</h1>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <img src="https://via.placeholder.com/40" alt="Admin" class="admin-avatar">
                        <span class="admin-name"><?php echo htmlspecialchars($adminName); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </header>

            <!-- Dashboard Tab -->
            <div class="tab-content active" id="dashboard">
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="total-bookings">0</h3>
                            <p>Total Bookings</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-van-shuttle"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="total-vans">0</h3>
                            <p>Total Vans</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="total-customers">0</h3>
                            <p>Total Customers</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="total-revenue">$0</h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-charts">
                    <div class="chart-card">
                        <h3>Recent Bookings</h3>
                        <div class="recent-bookings" id="recent-bookings">
                            <p>Loading recent bookings...</p>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h3>Van Availability</h3>
                        <div class="van-availability" id="van-availability">
                            <p>Loading van availability...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings Tab -->
            <div class="tab-content" id="bookings">
                <div class="content-header">
                    <h2>Booking Management</h2>
                    <div class="header-actions">
                        <input type="text" placeholder="Search bookings..." class="search-input" id="booking-search">
                        <select class="filter-select" id="booking-status-filter">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="admin-table" id="bookings-table">
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
                        <tbody id="bookings-tbody">
                            <tr><td colspan="8">Loading bookings...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Van Management Tab -->
            <div class="tab-content" id="vans">
                <div class="content-header">
                    <h2>Van Management</h2>
                    <button class="add-btn" onclick="openAddVanModal()">
                        <i class="fas fa-plus"></i>
                        Add New Van
                    </button>
                </div>
                
                <div class="vans-grid" id="vans-grid">
                    <p>Loading vans...</p>
                </div>
            </div>

            <!-- Customers Tab -->
            <div class="tab-content" id="customers">
                <div class="content-header">
                    <h2>Customer Management</h2>
                    <input type="text" placeholder="Search customers..." class="search-input" id="customer-search">
                </div>
                
                <div class="table-container">
                    <table class="admin-table" id="customers-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Total Bookings</th>
                                <th>Total Spent</th>
                                <th>Last Booking</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="customers-tbody">
                            <tr><td colspan="7">Loading customers...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reports Tab -->
            <div class="tab-content" id="reports">
                <div class="content-header">
                    <h2>Reports & Analytics</h2>
                    <div class="report-filters">
                        <select class="filter-select" id="report-period">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="365">Last Year</option>
                        </select>
                        <button class="export-btn">
                            <i class="fas fa-download"></i>
                            Export Report
                        </button>
                    </div>
                </div>
                
                <div class="reports-grid">
                    <div class="report-card">
                        <h3>Revenue Overview</h3>
                        <div class="report-chart" id="revenue-chart">
                            <p>Revenue analytics coming soon...</p>
                        </div>
                    </div>
                    <div class="report-card">
                        <h3>Popular Van Types</h3>
                        <div class="report-chart" id="van-popularity-chart">
                            <p>Van popularity analytics coming soon...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div class="tab-content" id="settings">
                <div class="content-header">
                    <h2>System Settings</h2>
                </div>
                
                <div class="settings-grid">
                    <div class="settings-card">
                        <h3>General Settings</h3>
                        <form class="settings-form">
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" value="VanGo" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Contact Email</label>
                                <input type="email" value="info@vango.com" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" value="+1 (555) 123-4567" class="form-input">
                            </div>
                            <button type="submit" class="save-btn">Save Changes</button>
                        </form>
                    </div>
                    
                    <div class="settings-card">
                        <h3>Booking Settings</h3>
                        <form class="settings-form">
                            <div class="form-group">
                                <label>Minimum Booking Notice (hours)</label>
                                <input type="number" value="24" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Cancellation Policy (hours)</label>
                                <input type="number" value="24" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Deposit Amount ($)</label>
                                <input type="number" value="200" class="form-input">
                            </div>
                            <button type="submit" class="save-btn">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Van Modal -->
    <div class="modal" id="addVanModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Van</h3>
                <button class="close-button" onclick="closeAddVanModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form class="van-form" id="addVanForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vanName">Van Name *</label>
                            <input type="text" id="vanName" name="vanName" placeholder="e.g., Premium Van" required>
                        </div>
                        <div class="form-group">
                            <label for="vanType">Van Type *</label>
                            <select id="vanType" name="vanType" required>
                                <option value="">Select Type</option>
                                <option value="Economy">Economy</option>
                                <option value="Standard">Standard</option>
                                <option value="Premium">Premium</option>
                                <option value="Luxury">Luxury</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vanModel">Van Model *</label>
                            <input type="text" id="vanModel" name="vanModel" placeholder="e.g., Toyota Hiace" required>
                        </div>
                        <div class="form-group">
                            <label for="registrationNumber">Registration Number *</label>
                            <input type="text" id="registrationNumber" name="registrationNumber" placeholder="e.g., ABC-1234" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vanYear">Year</label>
                            <input type="number" id="vanYear" name="vanYear" min="2000" max="2030" placeholder="2024">
                        </div>
                        <div class="form-group">
                            <label for="vanSeats">Number of Seats *</label>
                            <input type="number" id="vanSeats" name="vanSeats" min="1" max="20" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vanCapacity">Capacity</label>
                            <input type="number" id="vanCapacity" name="vanCapacity" min="1" max="20" placeholder="Same as seats">
                        </div>
                        <div class="form-group">
                            <label for="vanDailyRate">Daily Rate ($) *</label>
                            <input type="number" id="vanDailyRate" name="vanDailyRate" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="vanDescription">Description</label>
                        <textarea id="vanDescription" name="vanDescription" rows="3" placeholder="Describe the van's features and benefits"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="vanFeatures">Features (comma separated)</label>
                        <input type="text" id="vanFeatures" name="vanFeatures" placeholder="e.g., Air Conditioning, WiFi, GPS">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vanLocation">Location</label>
                            <input type="text" id="vanLocation" name="vanLocation" placeholder="e.g., Main Hub">
                        </div>
                        <div class="form-group">
                            <label for="vanStatus">Status</label>
                            <select id="vanStatus" name="vanStatus">
                                <option value="available">Available</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="booked">Booked</option>
                                <option value="out_of_service">Out of Service</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn" onclick="closeAddVanModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" form="addVanForm" class="save-btn" id="addVanSaveBtn">
                    <i class="fas fa-save"></i> Save Van
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Van Modal -->
    <div class="modal" id="editVanModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Van</h3>
                <button class="close-button" onclick="closeEditVanModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form class="van-form" id="editVanForm">
                    <input type="hidden" id="editVanId" name="vanId">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editVanName">Van Name *</label>
                            <input type="text" id="editVanName" name="vanName" required>
                        </div>
                        <div class="form-group">
                            <label for="editVanType">Van Type *</label>
                            <select id="editVanType" name="vanType" required>
                                <option value="Economy">Economy</option>
                                <option value="Standard">Standard</option>
                                <option value="Premium">Premium</option>
                                <option value="Luxury">Luxury</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editVanModel">Van Model *</label>
                            <input type="text" id="editVanModel" name="vanModel" required>
                        </div>
                        <div class="form-group">
                            <label for="editRegistrationNumber">Registration Number *</label>
                            <input type="text" id="editRegistrationNumber" name="registrationNumber" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editVanYear">Year</label>
                            <input type="number" id="editVanYear" name="vanYear" min="2000" max="2030">
                        </div>
                        <div class="form-group">
                            <label for="editVanSeats">Number of Seats *</label>
                            <input type="number" id="editVanSeats" name="vanSeats" min="1" max="20" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editVanCapacity">Capacity</label>
                            <input type="number" id="editVanCapacity" name="vanCapacity" min="1" max="20">
                        </div>
                        <div class="form-group">
                            <label for="editVanDailyRate">Daily Rate ($) *</label>
                            <input type="number" id="editVanDailyRate" name="vanDailyRate" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editVanDescription">Description</label>
                        <textarea id="editVanDescription" name="vanDescription" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editVanFeatures">Features</label>
                        <input type="text" id="editVanFeatures" name="vanFeatures">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editVanLocation">Location</label>
                            <input type="text" id="editVanLocation" name="vanLocation">
                        </div>
                        <div class="form-group">
                            <label for="editVanStatus">Status</label>
                            <select id="editVanStatus" name="vanStatus">
                                <option value="available">Available</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="booked">Booked</option>
                                <option value="out_of_service">Out of Service</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" onclick="closeEditVanModal()">Cancel</button>
                <button type="submit" form="editVanForm" class="save-btn">Update Van</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <button class="close-button" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
                <button class="delete-btn" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    <script src="admin-dashboard.js"></script>
</body>
</html>