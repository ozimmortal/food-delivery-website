<?php
session_start();
require_once '../../includes/dbh.inc.php';

// Check if user is logged in as a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get all orders for this customer with restaurant details
$orders = [];
try {
    $stmt = $pdo->prepare("SELECT o.*, r.name as restaurant_name, r.image as restaurant_image 
                          FROM orders o
                          JOIN restaurants r ON o.restaurant_id = r.id
                          WHERE o.customer_id = ?
                          ORDER BY o.created_at DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();

    // Get order items for each order
    foreach ($orders as &$order) {
        $stmt = $pdo->prepare("SELECT oi.*, mi.name as item_name, mi.image as item_image
                              FROM order_items oi
                              JOIN menu_items mi ON oi.menu_item_id = mi.id
                              WHERE oi.order_id = ?");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    $_SESSION['error'] = "Error loading your orders. Please try again later.";
}

// Function to get status badge class based on order status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'placed':
            return 'bg-yellow-100 text-yellow-800';
        case 'ready':
            return 'bg-blue-100 text-blue-800';
        case 'picked_up':
            return 'bg-purple-100 text-purple-800';
        case 'delivered':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Sweet Bite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .order-card {
            transition: all 0.2s ease;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .order-item:hover {
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-xl font-bold text-orange-500 flex items-center">
                        <i class="fas fa-utensils mr-2"></i>
                        Sweet Bite 
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-orange-500">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                    <a href="../../auth/logout.php" class="text-gray-600 hover:text-orange-500">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-clipboard-list text-orange-500 mr-2"></i>
                My Orders
            </h1>
        </div>

        <!-- Error Message -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-red-700"><?= $_SESSION['error'] ?></p>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if (isset($_SESSION['order_success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 p-4 mb-6">
                <p class="text-green-700"><?= $_SESSION['order_success'] ?></p>
            </div>
            <?php unset($_SESSION['order_success']); ?>
        <?php endif; ?>

        <!-- Orders List -->
        <?php if (empty($orders)): ?>
            <div class="bg-white p-8 rounded-lg shadow text-center">
                <i class="fas fa-clipboard-list text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500">You haven't placed any orders yet.</p>
                <a href="index.php" class="inline-block mt-4 bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                    Order Now
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card bg-white rounded-lg shadow overflow-hidden">
                        <!-- Order Header -->
                        <div class="p-4 border-b flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded overflow-hidden mr-4">
                                    <img src="../../<?= htmlspecialchars($order['restaurant_image'] ?: 'assets/default-restaurant.jpg') ?>" 
                                         alt="<?= htmlspecialchars($order['restaurant_name']) ?>" 
                                         class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h3 class="font-bold"><?= htmlspecialchars($order['restaurant_name']) ?></h3>
                                    <p class="text-sm text-gray-500">
                                        Order #<?= $order['id'] ?> • 
                                        <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <span class="px-3 py-1 rounded-full text-sm font-medium <?= getStatusBadgeClass($order['status']) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="order-item p-4 flex">
                                    <div class="w-16 h-16 rounded overflow-hidden mr-4">
                                        <img src="../../<?= htmlspecialchars($item['item_image'] ?: 'assets/default-food.jpg') ?>" 
                                             alt="<?= htmlspecialchars($item['item_name']) ?>" 
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between">
                                            <h4 class="font-medium"><?= htmlspecialchars($item['item_name']) ?></h4>
                                            <span class="font-bold">$<?= number_format($item['price'], 2) ?></span>
                                        </div>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?></span>
                                            <span class="text-sm font-bold">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Order Footer -->
                        <div class="p-4 border-t bg-gray-50">
                            <div class="flex justify-between items-center">
                                <div>
                                    <?php if ($order['delivery_id']): ?>
                                        <p class="text-sm text-gray-500">
                                            <i class="fas fa-motorcycle mr-1"></i>
                                            Delivery in progress
                                        </p>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500">
                                            <i class="fas fa-clock mr-1"></i>
                                            Preparing your order
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Total</p>
                                    <p class="text-xl font-bold text-orange-500">$<?= number_format($order['total'], 2) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Any JavaScript functionality can be added here
        document.addEventListener('DOMContentLoaded', function() {
            // You could add order tracking functionality here
        });
    </script>
</body>
</html>