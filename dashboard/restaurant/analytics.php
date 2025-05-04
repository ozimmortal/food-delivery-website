<?php
session_start();
require_once '../../includes/dbh.inc.php';

// Check if user is logged in and is a restaurant owner
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'restaurant') {
    header('Location: ../../auth/login.php');
    exit();
}

// Get restaurant data
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$restaurant = $stmt->fetch();
$restaurantId = $restaurant['id'] ?? null;

// Set default date range (last 7 days)
$endDate = date('Y-m-d');
$startDate = date('Y-m-d', strtotime('-7 days'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); }
        .chart-container { height: 300px; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Include your sidebar -->
    <?php include('./sidebar.php'); ?>
    
    <div class="ml-64 p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-chart-line text-orange-500 mr-2"></i>
                    Restaurant Analytics
                </h1>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <input type="date" id="start-date" value="<?= $startDate ?>" class="border rounded px-3 py-2">
                        <span>to</span>
                        <input type="date" id="end-date" value="<?= $endDate ?>" class="border rounded px-3 py-2">
                        <button id="apply-date-range" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                            Apply
                        </button>
                    </div>
                </div>
            </div>

            <?php if (!$restaurantId): ?>
                <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-6">
                    <p class="text-red-700">You need to create a restaurant first to view analytics.</p>
                </div>
            <?php else: ?>
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Total Revenue -->
                    <div class="stat-card bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                                <p class="mt-1 text-3xl font-semibold text-gray-900" id="total-revenue">$0.00</p>
                            </div>
                            <div class="bg-orange-100 p-3 rounded-full">
                                <i class="fas fa-dollar-sign text-orange-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total Orders -->
                    <div class="stat-card bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Orders</p>
                                <p class="mt-1 text-3xl font-semibold text-gray-900" id="total-orders">0</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-shopping-bag text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Average Order Value -->
                    <div class="stat-card bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Avg. Order Value</p>
                                <p class="mt-1 text-3xl font-semibold text-gray-900" id="avg-order-value">$0.00</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-calculator text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Popular Item -->
                    <div class="stat-card bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Most Popular Item</p>
                                <p class="mt-1 text-xl font-semibold text-gray-900 truncate" id="popular-item">-</p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-star text-purple-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Revenue Chart -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Revenue Trend</h3>
                        <div class="chart-container">
                            <canvas id="revenue-chart"></canvas>
                        </div>
                    </div>

                    <!-- Order Volume Chart -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Order Volume</h3>
                        <div class="chart-container">
                            <canvas id="orders-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Order Status Distribution -->
                <div class="bg-white p-6 rounded-lg shadow mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Order Status Distribution</h3>
                    <div class="chart-container">
                        <canvas id="status-chart"></canvas>
                    </div>
                </div>

                <!-- Top Selling Items -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Top Selling Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="top-items-table">
                                <!-- Will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Global chart references
        let revenueChart, ordersChart, statusChart;

        // Format currency
        function formatCurrency(amount) {
            return '$' + parseFloat(amount).toFixed(2);
        }

        // Load analytics data
        async function loadAnalyticsData(startDate, endDate) {
            try {
                // Show loading state
                document.getElementById('total-revenue').textContent = 'Loading...';
                document.getElementById('total-orders').textContent = 'Loading...';
                
                // Fetch data from API
                const response = await fetch(`./analytic_api.php?restaurant_id=<?= $restaurantId ?>&start_date=${startDate}&end_date=${endDate}`);
                
                // First check if response is OK
                if (!response.ok) {
                    const errorData = await response.json().catch(() => null);
                    console.log(errorData);
                    throw new Error(errorData?.error || `HTTP error! status: ${response.status}`);
                }
                
                // Then try to parse as JSON
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'API request failed');
                }

                // Update summary cards
                document.getElementById('total-revenue').textContent = formatCurrency(data.data.total_revenue);
                document.getElementById('total-orders').textContent = data.data.total_orders;
                document.getElementById('avg-order-value').textContent = formatCurrency(data.data.avg_order_value);
                document.getElementById('popular-item').textContent = data.data.popular_item || 'No data';

                // Update top items table
                const topItemsTable = document.getElementById('top-items-table');
                topItemsTable.innerHTML = data.data.top_items.map(item => `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">${item.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${item.quantity}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${formatCurrency(item.revenue)}</td>
                    </tr>
                `).join('');

                // Initialize charts
                initCharts(
                    data.data.revenue_trend.labels,
                    data.data.revenue_trend.data,
                    data.data.orders_trend.data,
                    data.data.status_distribution
                );

            } catch (error) {
                console.error('Error loading analytics:', error);
                alert('Failed to load analytics data: ' + error.message);
                
                // If it's an authentication error, redirect to login
                if (error.message.includes('Unauthorized')) {
                    window.location.href = '../../auth/login.php';
                }
            }
        }

        // Initialize charts
        function initCharts(dateLabels, revenueData, ordersData, statusData) {
            const ctx1 = document.getElementById('revenue-chart').getContext('2d');
            const ctx2 = document.getElementById('orders-chart').getContext('2d');
            const ctx3 = document.getElementById('status-chart').getContext('2d');
            
            // Destroy existing charts if they exist
            if (revenueChart) revenueChart.destroy();
            if (ordersChart) ordersChart.destroy();
            if (statusChart) statusChart.destroy();
            
            // Revenue Chart
            revenueChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: dateLabels,
                    datasets: [{
                        label: 'Revenue ($)',
                        data: revenueData,
                        backgroundColor: 'rgba(249, 115, 22, 0.1)',
                        borderColor: 'rgba(249, 115, 22, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Orders Chart
            ordersChart = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: dateLabels,
                    datasets: [{
                        label: 'Number of Orders',
                        data: ordersData,
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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

            // Status Distribution Chart
            statusChart = new Chart(ctx3, {
                type: 'doughnut',
                data: {
                    labels: statusData.labels,
                    datasets: [{
                        data: statusData.values,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Load initial data
            loadAnalyticsData('<?= $startDate ?>', '<?= $endDate ?>');
            
            // Apply date range button
            document.getElementById('apply-date-range').addEventListener('click', function() {
                const startDate = document.getElementById('start-date').value;
                const endDate = document.getElementById('end-date').value;
                loadAnalyticsData(startDate, endDate);
            });
        });
    </script>
</body>
</html>