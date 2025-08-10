<?php
/**
 * Analytics Demo
 * Simple demonstration of the analytics system
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
    <title>Analytics Demo - VanGo</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .demo-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .demo-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .demo-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .chart-grid {
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
        
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .chart-canvas {
            width: 100%;
            height: 300px;
        }
        
        .summary-cards {
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
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1>Analytics Dashboard Demo</h1>
            <p>Bar charts and analytics for the last 2 months</p>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <h4 id="totalBookings">112</h4>
                <p>Total Bookings</p>
            </div>
            <div class="summary-card">
                <h4 id="totalRevenue">$13,600</h4>
                <p>Total Revenue</p>
            </div>
            <div class="summary-card">
                <h4 id="avgBookingValue">$121.43</h4>
                <p>Avg Booking Value</p>
            </div>
            <div class="summary-card">
                <h4 id="uniqueCustomers">90</h4>
                <p>Unique Customers</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="chart-grid">
            <!-- Monthly Revenue Bar Chart -->
            <div class="chart-container">
                <h3 class="chart-title">Monthly Revenue Trend</h3>
                <div class="chart-canvas">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Peak Days Bar Chart -->
            <div class="chart-container">
                <h3 class="chart-title">Peak Booking Days</h3>
                <div class="chart-canvas">
                    <canvas id="peakDaysChart"></canvas>
                </div>
            </div>

            <!-- Van Performance Chart -->
            <div class="chart-container">
                <h3 class="chart-title">Van Performance</h3>
                <div class="chart-canvas">
                    <canvas id="vanPerformanceChart"></canvas>
                </div>
            </div>

            <!-- Booking Status Chart -->
            <div class="chart-container">
                <h3 class="chart-title">Booking Status Distribution</h3>
                <div class="chart-canvas">
                    <canvas id="bookingStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sample data for the last 2 months
        const sampleData = {
            months: ['Dec 2024', 'Jan 2025'],
            revenue: [5400, 8200],
            peakDays: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            bookings: [12, 15, 18, 20, 25, 30, 22],
            vanTypes: ['Premium', 'Luxury', 'Standard', 'Economy'],
            vanRevenue: [3200, 2800, 1800, 1200],
            statusLabels: ['Confirmed', 'Pending', 'Cancelled', 'Completed'],
            statusData: [85, 15, 8, 4]
        };

        // Create Monthly Revenue Bar Chart
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: sampleData.months,
                datasets: [{
                    label: 'Revenue ($)',
                    data: sampleData.revenue,
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

        // Create Peak Days Bar Chart
        new Chart(document.getElementById('peakDaysChart'), {
            type: 'bar',
            data: {
                labels: sampleData.peakDays,
                datasets: [{
                    label: 'Bookings',
                    data: sampleData.bookings,
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

        // Create Van Performance Doughnut Chart
        new Chart(document.getElementById('vanPerformanceChart'), {
            type: 'doughnut',
            data: {
                labels: sampleData.vanTypes,
                datasets: [{
                    data: sampleData.vanRevenue,
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(118, 75, 162, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ],
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

        // Create Booking Status Pie Chart
        new Chart(document.getElementById('bookingStatusChart'), {
            type: 'pie',
            data: {
                labels: sampleData.statusLabels,
                datasets: [{
                    data: sampleData.statusData,
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
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
    </script>
</body>
</html> 