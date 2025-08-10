<?php
/**
 * VanGo Admin Dashboard
 * Protected admin dashboard with session management
 */

session_start();
require_once 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.html');
    exit;
}

// Get admin info
$admin_id = $_SESSION['admin_id'] ?? '';
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_email = $_SESSION['admin_email'] ?? '';
$admin_role = $_SESSION['admin_role'] ?? 'admin';

// Fetch all bookings
$pdo = getDatabaseConnection();
$bookings = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC");
    $bookings = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VanGo</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .admin-nav h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .admin-nav .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .admin-nav .admin-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .admin-nav .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .admin-nav .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .admin-tabs {
            display: flex;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .admin-tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .admin-tab.active {
            background: #667eea;
            color: white;
        }
        
        .admin-tab:hover:not(.active) {
            background: #e9ecef;
        }
        
        .admin-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            min-height: 500px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 600;
        }
        
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        
        .van-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .van-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .van-card h4 {
            margin: 0 0 1rem 0;
            color: #333;
        }
        
        .van-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn-edit, .btn-delete {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background: #28a745;
            color: white;
        }
        
        .btn-edit:hover {
            background: #218838;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .status-new {
            background: #007bff;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .status-read {
            background: #6c757d;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .status-replied {
            background: #28a745;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .status-closed {
            background: #dc3545;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .messages-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: center;
        }
        
        .messages-controls select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .btn-refresh {
            background: #007bff;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-refresh:hover {
            background: #0056b3;
        }
        
        .add-van-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }
        
        .add-van-btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
            z-index: 1001;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
            position: relative;
            z-index: 1002;
            min-height: 40px;
            align-items: center;
            padding: 0.5rem;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block !important;
            visibility: visible !important;
            font-size: 1rem;
            line-height: 1.5;
            opacity: 1 !important;
            z-index: 1003;
        }
        
        .btn-save {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block !important;
            visibility: visible !important;
            position: relative;
            z-index: 1004;
            font-size: 1rem;
            line-height: 1.5;
            opacity: 1 !important;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
        }
        
        .btn-save:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .analytics-period {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .period-selector {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            cursor: pointer;
        }
        
        .refresh-analytics {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .refresh-analytics:hover {
            background: #5a6fd8;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .chart-container {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .chart-subtitle {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
        }
        
        .chart-canvas {
            width: 100%;
            height: 300px;
            position: relative;
        }
        
        .analytics-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .summary-card h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .summary-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .summary-card.trend-up {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .summary-card.trend-down {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        }
        
        .summary-card.trend-neutral {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }
        
        .loading-chart {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 300px;
            color: #666;
        }
        
        .loading-chart i {
            font-size: 2rem;
            margin-right: 1rem;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .no-data {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 300px;
            color: #666;
            text-align: center;
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ddd;
        }
        
        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
            justify-content: center;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }
        
        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .admin-tabs {
                flex-direction: column;
            }
            
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .van-grid {
                grid-template-columns: 1fr;
            }
            
            .analytics-grid {
                grid-template-columns: 1fr;
            }
            
            .analytics-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .analytics-period {
                justify-content: center;
            }
            
            .chart-container {
                padding: 1rem;
            }
            
            .chart-canvas {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <nav class="admin-nav">
            <h1><i class="fas fa-tachometer-alt"></i> VanGo Admin Dashboard</h1>
            <div class="admin-info">
                <div class="admin-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <div><strong><?php echo htmlspecialchars($admin_name); ?></strong></div>
                    <div style="font-size: 0.9rem; opacity: 0.8;"><?php echo htmlspecialchars($admin_email); ?></div>
                </div>
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </nav>
    </header>

    <div class="admin-container">
        <!-- Admin Tabs -->
        <div class="admin-tabs">
            <button class="admin-tab active" onclick="showTab('dashboard')">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </button>
            <button class="admin-tab" onclick="showTab('analytics')">
                <i class="fas fa-chart-line"></i> Analytics
            </button>
            <button class="admin-tab" onclick="showTab('bookings')">
                <i class="fas fa-calendar-check"></i> Bookings
            </button>
            <button class="admin-tab" onclick="showTab('vans')">
                <i class="fas fa-van-shuttle"></i> Vans
            </button>
            <button class="admin-tab" onclick="showTab('customers')">
                <i class="fas fa-users"></i> Customers
            </button>
            <button class="admin-tab" onclick="showTab('messages')">
                <i class="fas fa-envelope"></i> Messages
            </button>
            <button class="admin-tab" onclick="showTab('settings')">
                <i class="fas fa-cog"></i> Settings
            </button>
        </div>

        <!-- Dashboard Content -->
        <div class="admin-content">
            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active">
                <h2><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h2>
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3 id="total-bookings">0</h3>
                        <p>Total Bookings</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="total-vans">0</h3>
                        <p>Available Vans</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="total-customers">0</h3>
                        <p>Registered Customers</p>
                    </div>
                    <div class="stat-card">
                        <h3 id="total-revenue">$0</h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                
                <h3>Recent Bookings</h3>
                <div id="recent-bookings">
                    <p>Loading recent bookings...</p>
                </div>
            </div>

            <!-- Analytics Tab -->
            <div id="analytics" class="tab-content">
                <div class="analytics-header">
                    <div>
                        <h2><i class="fas fa-chart-line"></i> Analytics Dashboard</h2>
                        <p>Comprehensive insights for the last 2 months</p>
                    </div>
                    <div class="analytics-period">
                        <select class="period-selector" id="analyticsPeriod" onchange="loadAnalytics()">
                            <option value="2">Last 2 Months</option>
                            <option value="1">Last Month</option>
                            <option value="3">Last 3 Months</option>
                        </select>
                        <button class="refresh-analytics" onclick="loadAnalytics()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Analytics Summary Cards -->
                <div class="analytics-summary" id="analyticsSummary">
                    <div class="summary-card">
                        <h4 id="totalBookings">0</h4>
                        <p>Total Bookings</p>
                    </div>
                    <div class="summary-card">
                        <h4 id="totalRevenue">$0</h4>
                        <p>Total Revenue</p>
                    </div>
                    <div class="summary-card">
                        <h4 id="avgBookingValue">$0</h4>
                        <p>Avg Booking Value</p>
                    </div>
                    <div class="summary-card">
                        <h4 id="uniqueCustomers">0</h4>
                        <p>Unique Customers</p>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="analytics-grid">
                    <!-- Monthly Revenue Chart -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <div>
                                <h3 class="chart-title">Monthly Revenue Trend</h3>
                                <p class="chart-subtitle">Revenue comparison over the last 2 months</p>
                            </div>
                        </div>
                        <div class="chart-canvas">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <!-- Booking Volume Chart -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <div>
                                <h3 class="chart-title">Booking Volume</h3>
                                <p class="chart-subtitle">Daily booking trends</p>
                            </div>
                        </div>
                        <div class="chart-canvas">
                            <canvas id="bookingVolumeChart"></canvas>
                        </div>
                    </div>

                    <!-- Van Performance Chart -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <div>
                                <h3 class="chart-title">Van Performance</h3>
                                <p class="chart-subtitle">Top performing vans by revenue</p>
                            </div>
                        </div>
                        <div class="chart-canvas">
                            <canvas id="vanPerformanceChart"></canvas>
                        </div>
                    </div>

                    <!-- Peak Days Chart -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <div>
                                <h3 class="chart-title">Peak Booking Days</h3>
                                <p class="chart-subtitle">Bookings by day of week</p>
                            </div>
                        </div>
                        <div class="chart-canvas">
                            <canvas id="peakDaysChart"></canvas>
                        </div>
                    </div>

                    <!-- Customer Growth Chart -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <div>
                                <h3 class="chart-title">Customer Growth</h3>
                                <p class="chart-subtitle">New vs returning customers</p>
                            </div>
                        </div>
                        <div class="chart-canvas">
                            <canvas id="customerGrowthChart"></canvas>
                        </div>
                    </div>

                    <!-- Booking Status Distribution -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <div>
                                <h3 class="chart-title">Booking Status</h3>
                                <p class="chart-subtitle">Distribution of booking statuses</p>
                            </div>
                        </div>
                        <div class="chart-canvas">
                            <canvas id="bookingStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings Tab -->
            <div id="bookings" class="tab-content">
                <h2><i class="fas fa-calendar-check"></i> Manage Bookings</h2>
                <div id="bookings-list">
                    <p>Loading bookings...</p>
                </div>
            </div>

            <!-- Vans Tab -->
            <div id="vans" class="tab-content">
                <h2><i class="fas fa-van-shuttle"></i> Manage Vans</h2>
                <button class="add-van-btn" onclick="showAddVanModal()">
                    <i class="fas fa-plus"></i> Add New Van
                </button>
                <div id="vans-list">
                    <p>Loading vans...</p>
                </div>
            </div>

            <!-- Customers Tab -->
            <div id="customers" class="tab-content">
                <h2><i class="fas fa-users"></i> Manage Customers</h2>
                <div id="customers-list">
                    <p>Loading customers...</p>
                </div>
            </div>

            <!-- Contact Messages Tab -->
            <div id="messages" class="tab-content">
                <h2><i class="fas fa-envelope"></i> Contact Messages</h2>
                <div class="messages-controls">
                    <select id="messageStatusFilter" onchange="filterMessages()">
                        <option value="">All Messages</option>
                        <option value="new">New</option>
                        <option value="read">Read</option>
                        <option value="replied">Replied</option>
                        <option value="closed">Closed</option>
                    </select>
                    <button class="btn-refresh" onclick="loadMessagesData()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div id="messages-list">
                    <p>Loading messages...</p>
                </div>
            </div>

            <!-- Settings Tab -->
            <div id="settings" class="tab-content">
                <h2><i class="fas fa-cog"></i> System Settings</h2>
                <p>System settings and configuration options will be displayed here.</p>
            </div>
        </div>
    </div>

    <!-- Add/Edit Van Modal -->
    <div id="vanModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeVanModal()">&times;</span>
            <h3 id="modalTitle">Add New Van</h3>
            <form id="vanForm">
                <div class="form-group">
                    <label for="vanRegNo">Registration Number</label>
                    <input type="text" id="vanRegNo" name="reg_no" required>
                </div>
                <div class="form-group">
                    <label for="vanName">Van Name</label>
                    <input type="text" id="vanName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="vanType">Van Type</label>
                    <select id="vanType" name="type" required>
                        <option value="">Select Type</option>
                        <option value="Economy">Economy</option>
                        <option value="Standard">Standard</option>
                        <option value="Premium">Premium</option>
                        <option value="Luxury">Luxury</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="vanSeats">Number of Seats</label>
                    <input type="number" id="vanSeats" name="seats" min="1" max="20" value="4" required>
                </div>
                <div class="form-group">
                    <label for="vanPrice">Price per Day ($)</label>
                    <input type="number" id="vanPrice" name="daily_rate" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="vanDescription">Description</label>
                    <textarea id="vanDescription" name="description" placeholder="Enter van description..."></textarea>
                </div>
                <div class="form-group">
                    <label for="vanStatus">Status</label>
                    <select id="vanStatus" name="status" required>
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="booked">Booked</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Features</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="vanAC" name="ac">
                            <span class="checkmark"></span>
                            Air Conditioning
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="vanWifi" name="wifi">
                            <span class="checkmark"></span>
                            WiFi
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="vanGPS" name="gps">
                            <span class="checkmark"></span>
                            GPS Navigation
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeVanModal()">Cancel</button>
                    <button type="submit" class="btn-save" id="saveVanBtn">Save Van</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab functionality
        function showTab(tabName) {
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            const tabs = document.querySelectorAll('.admin-tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            
            loadTabData(tabName);
        }
        
        // Load data for each tab
        function loadTabData(tabName) {
            switch(tabName) {
                case 'dashboard':
                    loadDashboardData();
                    break;
                case 'bookings':
                    loadBookingsData();
                    break;
                case 'vans':
                    loadVansData();
                    break;
                case 'customers':
                    loadCustomersData();
                    break;
                case 'messages':
                    loadMessagesData();
                    break;
                case 'analytics':
                    loadAnalytics();
                    break;
            }
        }
        
        // Load dashboard statistics
        function loadDashboardData() {
            document.getElementById('total-bookings').textContent = '24';
            document.getElementById('total-vans').textContent = '8';
            document.getElementById('total-customers').textContent = '156';
            document.getElementById('total-revenue').textContent = '$12,450';
            
            document.getElementById('recent-bookings').innerHTML = `
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                    <p><strong>Recent Bookings:</strong></p>
                    <ul>
                        <li>Booking #BK001 - Economy Van - John Doe - $120</li>
                        <li>Booking #BK002 - Premium Van - Jane Smith - $200</li>
                        <li>Booking #BK003 - Standard Van - Mike Johnson - $150</li>
                    </ul>
                </div>
            `;
        }
        
        // Load bookings data
        function loadBookingsData() {
            document.getElementById('bookings-list').innerHTML = `
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                    <p><strong>All Bookings:</strong></p>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #e9ecef;">
                                <th style="padding: 0.5rem; text-align: left;">Booking ID</th>
                                <th style="padding: 0.5rem; text-align: left;">Customer</th>
                                <th style="padding: 0.5rem; text-align: left;">Van</th>
                                <th style="padding: 0.5rem; text-align: left;">Date</th>
                                <th style="padding: 0.5rem; text-align: left;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td style="padding: 0.5rem;"><?= htmlspecialchars($booking['booking_id']) ?></td>
                                <td style="padding: 0.5rem;"><?= htmlspecialchars($booking['user_id']) ?></td>
                                <td style="padding: 0.5rem;"><?= htmlspecialchars($booking['van_id']) ?></td>
                                <td style="padding: 0.5rem;"><?= htmlspecialchars($booking['pickup_date']) ?></td>
                                <td style="padding: 0.5rem;"><?= htmlspecialchars($booking['status']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        // Load vans data
        function loadVansData() {
            document.getElementById('vans-list').innerHTML = '<p>Loading vans...</p>';
            
            fetch('get-vans.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayVans(data.vans);
                    } else {
                        document.getElementById('vans-list').innerHTML = `
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                                <p>Error loading vans: ${data.message}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('vans-list').innerHTML = `
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                            <p>Error loading vans. Please try again.</p>
                        </div>
                    `;
                });
        }
        
        // Display vans in the grid
        function displayVans(vans) {
            if (vans.length === 0) {
                document.getElementById('vans-list').innerHTML = `
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                        <p>No vans found. Add your first van to get started.</p>
                    </div>
                `;
                return;
            }
            
            let vansHTML = '<div class="van-grid">';
            
            vans.forEach(van => {
                const statusColor = van.status === 'available' ? '#28a745' : 
                                   van.status === 'maintenance' ? '#ffc107' : '#dc3545';
                const statusText = van.status.charAt(0).toUpperCase() + van.status.slice(1);
                
                vansHTML += `
                    <div class="van-card">
                        <h4>${van.name} (${van.type})</h4>
                        <p><strong>Reg No:</strong> ${van.reg_no}</p>
                        <p><strong>Type:</strong> ${van.type}</p>
                        <p><strong>Seats:</strong> ${van.seats}</p>
                        <p><strong>Price:</strong> $${van.daily_rate}/day</p>
                        <p><strong>Status:</strong> <span style="color: ${statusColor};">${statusText}</span></p>
                        <div class="van-features">
                            ${van.ac ? '<span class="feature-badge"><i class="fas fa-snowflake"></i> AC</span>' : ''}
                            ${van.wifi ? '<span class="feature-badge"><i class="fas fa-wifi"></i> WiFi</span>' : ''}
                            ${van.gps ? '<span class="feature-badge"><i class="fas fa-map-marker-alt"></i> GPS</span>' : ''}
                        </div>
                        <div class="van-actions">
                            <button class="btn-edit" onclick="editVan('${van.van_id}')">Edit</button>
                            <button class="btn-delete" onclick="deleteVan('${van.van_id}')">Delete</button>
                        </div>
                    </div>
                `;
            });
            
            vansHTML += '</div>';
            document.getElementById('vans-list').innerHTML = vansHTML;
        }
        
        // Load customers data
        function loadCustomersData() {
            document.getElementById('customers-list').innerHTML = `
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                    <p><strong>Registered Customers:</strong></p>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #e9ecef;">
                                <th style="padding: 0.5rem; text-align: left;">Name</th>
                                <th style="padding: 0.5rem; text-align: left;">Email</th>
                                <th style="padding: 0.5rem; text-align: left;">Phone</th>
                                <th style="padding: 0.5rem; text-align: left;">Join Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 0.5rem;">John Doe</td>
                                <td style="padding: 0.5rem;">john@example.com</td>
                                <td style="padding: 0.5rem;">+1234567890</td>
                                <td style="padding: 0.5rem;">2024-01-01</td>
                            </tr>
                            <tr>
                                <td style="padding: 0.5rem;">Jane Smith</td>
                                <td style="padding: 0.5rem;">jane@example.com</td>
                                <td style="padding: 0.5rem;">+1234567891</td>
                                <td style="padding: 0.5rem;">2024-01-05</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        // Load contact messages data
        function loadMessagesData() {
            fetch('get-messages.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMessages(data.messages);
                    } else {
                        document.getElementById('messages-list').innerHTML = `
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                                <p>Error loading messages: ${data.message}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('messages-list').innerHTML = `
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                            <p>Error loading messages. Please try again.</p>
                        </div>
                    `;
                });
        }
        
        // Display messages in the table
        function displayMessages(messages) {
            if (messages.length === 0) {
                document.getElementById('messages-list').innerHTML = `
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                        <p>No contact messages found.</p>
                    </div>
                `;
                return;
            }
            
            let tableHTML = `
                <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                    <thead>
                        <tr style="background: #e9ecef;">
                            <th style="padding: 0.5rem; text-align: left;">Name</th>
                            <th style="padding: 0.5rem; text-align: left;">Email</th>
                            <th style="padding: 0.5rem; text-align: left;">Subject</th>
                            <th style="padding: 0.5rem; text-align: left;">Status</th>
                            <th style="padding: 0.5rem; text-align: left;">Date</th>
                            <th style="padding: 0.5rem; text-align: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            messages.forEach(message => {
                const statusClass = getStatusClass(message.status);
                const statusText = message.status.charAt(0).toUpperCase() + message.status.slice(1);
                
                tableHTML += `
                    <tr>
                        <td style="padding: 0.5rem;">${message.first_name} ${message.last_name}</td>
                        <td style="padding: 0.5rem;">${message.email}</td>
                        <td style="padding: 0.5rem;">${message.subject}</td>
                        <td style="padding: 0.5rem;"><span class="${statusClass}">${statusText}</span></td>
                        <td style="padding: 0.5rem;">${formatDate(message.created_at)}</td>
                        <td style="padding: 0.5rem;">
                            <button class="btn-edit" onclick="viewMessage(${message.id})">View</button>
                            <button class="btn-edit" onclick="replyMessage(${message.id})">Reply</button>
                        </td>
                    </tr>
                `;
            });
            
            tableHTML += `
                    </tbody>
                </table>
            `;
            
            document.getElementById('messages-list').innerHTML = tableHTML;
        }
        
        // Get status class for styling
        function getStatusClass(status) {
            switch(status) {
                case 'new': return 'status-new';
                case 'read': return 'status-read';
                case 'replied': return 'status-replied';
                case 'closed': return 'status-closed';
                default: return 'status-default';
            }
        }
        
        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }
        
        // Message functions
        function viewMessage(messageId) {
            alert('View message ' + messageId + ' - This will show message details in a modal.');
        }
        
        function replyMessage(messageId) {
            alert('Reply to message ' + messageId + ' - This will open reply form.');
        }
        
        function filterMessages() {
            alert('Filter messages by status - This will filter the messages list.');
        }
        
        // Van modal functions
        function showAddVanModal() {
            console.log('Opening Add Van Modal');
            document.getElementById('modalTitle').textContent = 'Add New Van';
            document.getElementById('vanForm').reset();
            document.getElementById('vanSeats').value = '4';
            document.getElementById('vanModal').style.display = 'block';
            document.getElementById('vanForm').removeAttribute('data-van-id');
            const saveBtn = document.getElementById('saveVanBtn');
            saveBtn.style.display = 'inline-block !important';
            saveBtn.style.visibility = 'visible !important';
            saveBtn.style.opacity = '1 !important';
            saveBtn.style.transform = 'translateZ(0)';
            // Force DOM repaint
            saveBtn.offsetHeight;
            console.log('Save button styles:', {
                display: saveBtn.style.display,
                visibility: saveBtn.style.visibility,
                opacity: saveBtn.style.opacity
            });
        }
        
        function closeVanModal() {
            document.getElementById('vanModal').style.display = 'none';
            document.getElementById('vanForm').reset();
            document.getElementById('vanSeats').value = '4';
            const saveBtn = document.getElementById('saveVanBtn');
            saveBtn.style.display = 'inline-block !important';
            saveBtn.style.visibility = 'visible !important';
            saveBtn.style.opacity = '1 !important';
        }
        
        function editVan(vanId) {
            console.log('Opening Edit Van Modal for van_id:', vanId);
            document.getElementById('modalTitle').textContent = 'Edit Van';
            
            fetch(`manage-vans.php?id=${vanId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const van = data.van;
                        document.getElementById('vanRegNo').value = van.reg_no;
                        document.getElementById('vanName').value = van.name;
                        document.getElementById('vanType').value = van.type;
                        document.getElementById('vanSeats').value = van.seats;
                        document.getElementById('vanPrice').value = van.daily_rate;
                        document.getElementById('vanDescription').value = van.description || '';
                        document.getElementById('vanStatus').value = van.status;
                        document.getElementById('vanAC').checked = van.ac == 1;
                        document.getElementById('vanWifi').checked = van.wifi == 1;
                        document.getElementById('vanGPS').checked = van.gps == 1;
                        document.getElementById('vanForm').setAttribute('data-van-id', van.van_id);
                        document.getElementById('vanModal').style.display = 'block';
                        const saveBtn = document.getElementById('saveVanBtn');
                        saveBtn.style.display = 'inline-block !important';
                        saveBtn.style.visibility = 'visible !important';
                        saveBtn.style.opacity = '1 !important';
                        saveBtn.style.transform = 'translateZ(0)';
                        // Force DOM repaint
                        saveBtn.offsetHeight;
                        console.log('Save button styles:', {
                            display: saveBtn.style.display,
                            visibility: saveBtn.style.visibility,
                            opacity: saveBtn.style.opacity
                        });
                    } else {
                        alert('Error loading van data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching van data:', error);
                    alert('Error loading van data. Please try again.');
                });
        }
        
        function deleteVan(vanId) {
            if (confirm('Are you sure you want to delete this van? This action cannot be undone.')) {
                fetch(`manage-vans.php?id=${vanId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Van deleted successfully');
                        loadVansData();
                    } else {
                        alert('Error deleting van: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting van. Please try again.');
                });
            }
        }
        
        // Handle van form submission
        document.getElementById('vanForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const vanId = this.getAttribute('data-van-id');
            const isEdit = vanId !== null && vanId !== '';
            
            const formData = {
                reg_no: document.getElementById('vanRegNo').value,
                name: document.getElementById('vanName').value,
                type: document.getElementById('vanType').value,
                seats: parseInt(document.getElementById('vanSeats').value),
                daily_rate: parseFloat(document.getElementById('vanPrice').value),
                description: document.getElementById('vanDescription').value,
                status: document.getElementById('vanStatus').value,
                ac: document.getElementById('vanAC').checked,
                wifi: document.getElementById('vanWifi').checked,
                gps: document.getElementById('vanGPS').checked
            };
            
            console.log('Form data being sent:', formData);
            
            if (!formData.reg_no || !formData.name || !formData.type || !formData.seats || !formData.daily_rate || !formData.status) {
                alert('Please fill in all required fields');
                return;
            }
            
            const submitBtn = document.getElementById('saveVanBtn');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Saving...';
            submitBtn.disabled = true;
            
            const url = isEdit ? `manage-vans.php?id=${vanId}` : 'manage-vans.php';
            const method = isEdit ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(isEdit ? 'Van updated successfully' : 'Van added successfully');
                    closeVanModal();
                    loadVansData();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
        
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('vanModal');
            if (event.target === modal) {
                closeVanModal();
            }
        }
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            showTab('dashboard');
            // Ensure save button is initialized
            const saveBtn = document.getElementById('saveVanBtn');
            saveBtn.style.display = 'inline-block !important';
            saveBtn.style.visibility = 'visible !important';
            saveBtn.style.opacity = '1 !important';
            saveBtn.style.transform = 'translateZ(0)';
            console.log('Page loaded - Save button initialized:', {
                display: saveBtn.style.display,
                visibility: saveBtn.style.visibility,
                opacity: saveBtn.style.opacity
            });
        });
    </script>
</body>
</html>