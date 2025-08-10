<?php
/**
 * Test Analytics System
 * Demonstrates the analytics functionality with sample data
 */

session_start();

// Simulate admin login for testing
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = 'admin_001';
$_SESSION['admin_name'] = 'Test Admin';
$_SESSION['admin_email'] = 'admin@test.com';
$_SESSION['admin_role'] = 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Test - VanGo</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .test-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .test-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .test-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .test-header p {
            color: #666;
            font-size: 1.1rem;
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
        
        .test-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 40px;
        }
        
        .test-info h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .test-info ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .test-info li {
            margin-bottom: 8px;
            color: #666;
        }
        
        .navigation-links {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        
        .navigation-links a {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .navigation-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
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
    <div class="test-container">
        <div class="test-header">
            <h1>Analytics System Test</h1>
            <p>Comprehensive analytics dashboard with bar charts for the last 2 months</p>
        </div>
        
        <div class="test-info">
            <h3>📊 Analytics Features</h3>
            <ul>
                <li><strong>Monthly Revenue Bar Chart:</strong> Revenue comparison over the last 2 months</li>
                <li><strong>Booking Volume Line Chart:</strong> Daily booking trends with smooth curves</li>
                <li><strong>Van Performance Doughnut Chart:</strong> Top performing vans by revenue</li>
                <li><strong>Peak Days Bar Chart:</strong> Bookings by day of the week</li>
                <li><strong>Customer Growth Line Chart:</strong> New vs returning customers</li>
                <li><strong>Booking Status Pie Chart:</strong> Distribution of booking statuses</li>
                <li><strong>Summary Cards:</strong> Key metrics at a glance</li>
                <li><strong>Responsive Design:</strong> Works on all devices</li>
            </ul>
        </div>

        <!-- Analytics Summary Cards -->
        <div class="analytics-summary">
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
        
        <div class="navigation-links">
            <a href="admin-dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
            </a>
            <a href="get-analytics.php">
                <i class="fas fa-code"></i> Analytics API
            </a>
            <a href="index.php">
                <i class="fas fa-home"></i> Home Page
            </a>
        </div>
    </div>

    <script>
        // Sample data for demonstration
        const sampleData = {
            booking_stats: [
                {
                    month: 'Dec 2024',
                    total_bookings: 45,
                    total_revenue: 5400,
                    unique_customers: 38
                },
                {
                    month: 'Jan 2025',
                    total_bookings: 67,
                    total_revenue: 8200,
                    unique_customers: 52
                }
            ],
            revenue_trends: [
                { date: '2024-12-01', daily_revenue: 180, daily_bookings: 3 },
                { date: '2024-12-02', daily_revenue: 240, daily_bookings: 4 },
                { date: '2024-12-03', daily_revenue: 300, daily_bookings: 5 },
                { date: '2024-12-04', daily_revenue: 360, daily_bookings: 6 },
                { date: '2024-12-05', daily_revenue: 420, daily_bookings: 7 },
                { date: '2024-12-06', daily_revenue: 480, daily_bookings: 8 },
                { date: '2024-12-07', daily_revenue: 540, daily_bookings: 9 },
                { date: '2025-01-01', daily_revenue: 200, daily_bookings: 4 },
                { date: '2025-01-02', daily_revenue: 280, daily_bookings: 5 },
                { date: '2025-01-03', daily_revenue: 360, daily_bookings: 6 },
                { date: '2025-01-04', daily_revenue: 440, daily_bookings: 7 },
                { date: '2025-01-05', daily_revenue: 520, daily_bookings: 8 },
                { date: '2025-01-06', daily_revenue: 600, daily_bookings: 9 },
                { date: '2025-01-07', daily_revenue: 680, daily_bookings: 10 }
            ],
            top_vans: [
                { type: 'Premium', seats: 8, revenue: 3200 },
                { type: 'Luxury', seats: 6, revenue: 2800 },
                { type: 'Standard', seats: 6, revenue: 1800 },
                { type: 'Economy', seats: 4, revenue: 1200 },
                { type: 'Minibus', seats: 12, revenue: 800 }
            ],
            peak_days: [
                { day_name: 'Monday', bookings: 12, revenue: 1440 },
                { day_name: 'Tuesday', bookings: 15, revenue: 1800 },
                { day_name: 'Wednesday', bookings: 18, revenue: 2160 },
                { day_name: 'Thursday', bookings: 20, revenue: 2400 },
                { day_name: 'Friday', bookings: 25, revenue: 3000 },
                { day_name: 'Saturday', bookings: 30, revenue: 3600 },
                { day_name: 'Sunday', bookings: 22, revenue: 2640 }
            ],
            customer_stats: {
                total_customers: 90,
                new_customers_30d: 25,
                new_customers_7d: 8
            },
            booking_status: [
                { status: 'Confirmed', count: 85, percentage: 75.9 },
                { status: 'Pending', count: 15, percentage: 13.4 },
                { status: 'Cancelled', count: 8, percentage: 7.1 },
                { status: 'Completed', count: 4, percentage: 3.6 }
            ]
        };

        // Update summary cards
        function updateAnalyticsSummary(data) {
            const totalBookings = data.booking_stats.reduce((sum, month) => sum + month.total_bookings, 0);
            const totalRevenue = data.booking_stats.reduce((sum, month) => sum + month.total_revenue, 0);
            const avgBookingValue = totalBookings > 0 ? totalRevenue / totalBookings : 0;
            const uniqueCustomers = data.customer_stats.total_customers;
            
            document.getElementById('totalBookings').textContent = totalBookings.toLocaleString();
            document.getElementById('totalRevenue').textContent = '$' + totalRevenue.toLocaleString();
            document.getElementById('avgBookingValue').textContent = '$' + avgBookingValue.toFixed(2);
            document.getElementById('uniqueCustomers').textContent = uniqueCustomers.toLocaleString();
        }

        // Create Revenue Chart
        function createRevenueChart(data) {
            const ctx = document.getElementById('revenueChart');
            if (!ctx) return;
            
            const labels = data.booking_stats.map(month => month.month);
            const revenueData = data.booking_stats.map(month => month.total_revenue);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue ($)',
                        data: revenueData,
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderColor: 'rgba(102, 126, 234, 1)',
                        borderWidth: 2,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Create Booking Volume Chart
        function createBookingVolumeChart(data) {
            const ctx = document.getElementById('bookingVolumeChart');
            if (!ctx) return;
            
            const labels = data.revenue_trends.map(item => new Date(item.date).toLocaleDateString());
            const bookingData = data.revenue_trends.map(item => item.daily_bookings);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Daily Bookings',
                        data: bookingData,
                        borderColor: 'rgba(118, 75, 162, 1)',
                        backgroundColor: 'rgba(118, 75, 162, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Create Van Performance Chart
        function createVanPerformanceChart(data) {
            const ctx = document.getElementById('vanPerformanceChart');
            if (!ctx) return;
            
            const labels = data.top_vans.map(van => `${van.type} (${van.seats} seats)`);
            const revenueData = data.top_vans.map(van => van.revenue);
            const colors = [
                'rgba(102, 126, 234, 0.8)',
                'rgba(118, 75, 162, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ];
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: revenueData,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Create Peak Days Chart
        function createPeakDaysChart(data) {
            const ctx = document.getElementById('peakDaysChart');
            if (!ctx) return;
            
            const labels = data.peak_days.map(day => day.day_name);
            const bookingData = data.peak_days.map(day => day.bookings);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bookings',
                        data: bookingData,
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 2,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Create Customer Growth Chart
        function createCustomerGrowthChart(data) {
            const ctx = document.getElementById('customerGrowthChart');
            if (!ctx) return;
            
            const labels = data.booking_stats.map(month => month.month);
            const customerData = data.booking_stats.map(month => month.unique_customers);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Unique Customers',
                        data: customerData,
                        borderColor: 'rgba(255, 193, 7, 1)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Create Booking Status Chart
        function createBookingStatusChart(data) {
            const ctx = document.getElementById('bookingStatusChart');
            if (!ctx) return;
            
            const labels = data.booking_status.map(status => status.status);
            const statusData = data.booking_status.map(status => status.count);
            const colors = [
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(108, 117, 125, 0.8)'
            ];
            
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: statusData,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateAnalyticsSummary(sampleData);
            createRevenueChart(sampleData);
            createBookingVolumeChart(sampleData);
            createVanPerformanceChart(sampleData);
            createPeakDaysChart(sampleData);
            createCustomerGrowthChart(sampleData);
            createBookingStatusChart(sampleData);
        });
    </script>
</body>
</html> 