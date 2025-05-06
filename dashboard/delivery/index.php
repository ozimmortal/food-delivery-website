<?php
session_start();
require_once '../../includes/dbh.inc.php';

// Check if user is logged in as a delivery person
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'delivery') {
    header('Location: ../../auth/login.php');
    exit();
}

$deliveryUserId = $_SESSION['user_id'];

// Get current user data
$userData = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$deliveryUserId]);
    $userData = $stmt->fetch();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error loading user data: " . $e->getMessage();
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Handle file upload
    $imagePath = $userData['image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $fileName = 'delivery_' . $deliveryUserId . '_' . time() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
            // Delete old image if it exists and isn't the default
            if ($imagePath && !str_contains($imagePath, 'assets/default-profile.jpg')) {
                @unlink('../../' . $imagePath);
            }
            $imagePath = 'uploads/profiles/' . $fileName;
        } else {
            $_SESSION['error'] = "Failed to upload profile image";
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ?, image = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $imagePath, $deliveryUserId]);
        
        // Update delivery location if coordinates are provided
        if ($latitude && $longitude) {
            $stmt = $pdo->prepare("INSERT INTO delivery_locations (delivery_id, latitude, longitude) 
                                  VALUES (?, ?, ?)
                                  ON DUPLICATE KEY UPDATE 
                                  latitude = VALUES(latitude), 
                                  longitude = VALUES(longitude)");
            $stmt->execute([$deliveryUserId, $latitude, $longitude]);
        }
        
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating profile: " . $e->getMessage();
    }
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $orderId = $_POST['order_id'];
        $newStatus = $_POST['status'];
        
        try {
            // Verify the order is assigned to this delivery person
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND delivery_id = ?");
            $stmt->execute([$newStatus, $orderId, $deliveryUserId]);
            
            $_SESSION['success'] = "Order status updated successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating order status: " . $e->getMessage();
        }
    } elseif (isset($_POST['accept_order'])) {
        $orderId = $_POST['order_id'];
        
        try {
            // Assign order to this delivery person
            $stmt = $pdo->prepare("UPDATE orders SET delivery_id = ?, status = 'picked_up' WHERE id = ? AND status = 'ready' AND delivery_id IS NULL");
            $stmt->execute([$deliveryUserId, $orderId]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "Order #$orderId has been assigned to you!";
            } else {
                $_SESSION['error'] = "Order could not be assigned. It may have been taken by another delivery person.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error accepting order: " . $e->getMessage();
        }
    }
    
    header("Location: index.php");
    exit();
}

// Get assigned orders
$assignedOrders = [];
try {
    $stmt = $pdo->prepare("SELECT o.*, r.name as restaurant_name, r.address as restaurant_address, 
                                  r.image as restaurant_image, u.name as customer_name, 
                                  u.phone as customer_phone, u.address as customer_address
                           FROM orders o
                           JOIN restaurants r ON o.restaurant_id = r.id
                           JOIN users u ON o.customer_id = u.id
                           WHERE o.delivery_id = ? AND o.status IN ('ready', 'picked_up')
                           ORDER BY 
                             CASE o.status 
                               WHEN 'ready' THEN 1 
                               WHEN 'picked_up' THEN 2 
                               ELSE 3 
                             END, o.created_at");
    $stmt->execute([$deliveryUserId]);
    $assignedOrders = $stmt->fetchAll();

    // Get order items for each order
    foreach ($assignedOrders as &$order) {
        $stmt = $pdo->prepare("SELECT oi.*, mi.name as item_name 
                              FROM order_items oi
                              JOIN menu_items mi ON oi.menu_item_id = mi.id
                              WHERE oi.order_id = ?");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log("Error fetching assigned orders: " . $e->getMessage());
    $_SESSION['error'] = "Error loading your assigned orders";
}

// Get available orders (ready but not assigned)
$availableOrders = [];
try {
    $stmt = $pdo->prepare("SELECT o.*, r.name as restaurant_name, r.address as restaurant_address, 
                                  r.image as restaurant_image, u.name as customer_name
                           FROM orders o
                           JOIN restaurants r ON o.restaurant_id = r.id
                           JOIN users u ON o.customer_id = u.id
                           WHERE o.status = 'ready' AND o.delivery_id IS NULL
                           ORDER BY o.created_at");
    $stmt->execute();
    $availableOrders = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching available orders: " . $e->getMessage());
    $_SESSION['error'] = "Error loading available orders";
}

// Get delivery location if exists
$deliveryLocation = [];
try {
    $stmt = $pdo->prepare("SELECT latitude, longitude FROM delivery_locations WHERE delivery_id = ? ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$deliveryUserId]);
    $deliveryLocation = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error fetching delivery location: " . $e->getMessage());
}

// Function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
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
    <title>Delivery Dashboard | Savory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        .order-card {
            transition: all 0.2s ease;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .tab-button {
            transition: all 0.2s ease;
        }
        .tab-button.active {
            border-bottom: 2px solid #f97316;
            color: #f97316;
            font-weight: 500;
        }
        #map, #profile-map {
            height: 300px;
            width: 100%;
            border-radius: 0.5rem;
        }
        .map-container {
            margin-top: 1rem;
        }
        .profile-image-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto;
            border: 3px solid #f97316;
        }
        .profile-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
                        <i class="fas fa-motorcycle mr-2"></i>
                        Savory Delivery
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../../auth/logout.php" class="text-gray-600 hover:text-orange-500">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-red-700"><?= $_SESSION['error'] ?></p>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 p-4 mb-6">
                <p class="text-green-700"><?= $_SESSION['success'] ?></p>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-8">
            <div class="flex space-x-8">
                <button id="assigned-tab" class="tab-button py-4 px-1 active">
                    <i class="fas fa-clipboard-list mr-2"></i> My Deliveries
                </button>
                <button id="available-tab" class="tab-button py-4 px-1 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-list-alt mr-2"></i> Available Orders
                </button>
                <button id="profile-tab" class="tab-button py-4 px-1 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-user mr-2"></i> My Profile
                </button>
            </div>
        </div>

        <!-- Assigned Orders Tab -->
        <div id="assigned-orders" class="tab-content">
            <h2 class="text-2xl font-bold mb-6">My Deliveries</h2>
            
            <?php if (empty($assignedOrders)): ?>
                <div class="bg-white p-8 rounded-lg shadow text-center">
                    <i class="fas fa-clipboard-list text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500">You don't have any assigned deliveries at the moment.</p>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($assignedOrders as $order): ?>
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
                                        <h3 class="font-bold">Order #<?= $order['id'] ?></h3>
                                        <p class="text-sm text-gray-500">
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

                            <!-- Order Details -->
                            <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Restaurant Info -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">
                                        <i class="fas fa-store mr-2 text-orange-500"></i> Restaurant
                                    </h4>
                                    <p class="font-bold"><?= htmlspecialchars($order['restaurant_name']) ?></p>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($order['restaurant_address']) ?></p>
                                </div>

                                <!-- Customer Info -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">
                                        <i class="fas fa-user mr-2 text-orange-500"></i> Customer
                                    </h4>
                                    <p class="font-bold"><?= htmlspecialchars($order['customer_name']) ?></p>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($order['customer_phone']) ?></p>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($order['customer_address']) ?></p>
                                </div>

                                <!-- Order Items -->
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">
                                        <i class="fas fa-utensils mr-2 text-orange-500"></i> Items
                                    </h4>
                                    <ul class="space-y-1">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <li class="text-sm">
                                                <?= htmlspecialchars($item['item_name']) ?> 
                                                <span class="font-medium">(x<?= $item['quantity'] ?>)</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>

                            <!-- Map Section -->
                            <?php if ($order['customer_latitude'] && $order['customer_longitude']): ?>
                                <div class="p-4 border-t">
                                    <h4 class="font-medium text-gray-700 mb-2">
                                        <i class="fas fa-map-marker-alt mr-2 text-orange-500"></i> Delivery Location
                                    </h4>
                                    <div id="map-<?= $order['id'] ?>" class="map-container">
                                        <!-- Map will be rendered here -->
                                    </div>
                                    <div class="mt-2 flex justify-between">
                                        <button onclick="getCurrentLocation(<?= $order['id'] ?>, <?= $order['customer_latitude'] ?>, <?= $order['customer_longitude'] ?>)" 
                                                class="bg-orange-500 text-white px-3 py-1 rounded text-sm hover:bg-orange-600">
                                            Show Route from My Location
                                        </button>
                                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $order['customer_latitude'] ?>,<?= $order['customer_longitude'] ?>" 
                                           target="_blank" class="text-orange-500 hover:text-orange-700 text-sm flex items-center">
                                            Open in Google Maps <i class="fas fa-external-link-alt ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Status Update -->
                            <div class="p-4 border-t bg-gray-50">
                                <form method="POST" class="flex justify-between items-center">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    
                                    <div class="flex-1">
                                        <select name="status" class="border rounded px-3 py-2 mr-4">
                                            <?php if ($order['status'] === 'ready'): ?>
                                                <option value="picked_up">Mark as Picked Up</option>
                                            <?php elseif ($order['status'] === 'picked_up'): ?>
                                                <option value="delivered">Mark as Delivered</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <button type="submit" name="update_status" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                                            Update Status
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Available Orders Tab -->
        <div id="available-orders" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-6">Available Orders</h2>
            
            <?php if (empty($availableOrders)): ?>
                <div class="bg-white p-8 rounded-lg shadow text-center">
                    <i class="fas fa-clipboard-list text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500">No available orders at the moment.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($availableOrders as $order): ?>
                        <div class="order-card bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-4 border-b">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-bold">Order #<?= $order['id'] ?></h3>
                                    <span class="px-3 py-1 rounded-full text-sm font-medium <?= getStatusBadgeClass($order['status']) ?>">
                                        Ready for pickup
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?>
                                </p>
                            </div>
                            
                            <div class="p-4">
                                <div class="mb-4">
                                    <h4 class="font-medium text-gray-700 mb-1">
                                        <i class="fas fa-store mr-2 text-orange-500"></i> Restaurant
                                    </h4>
                                    <p><?= htmlspecialchars($order['restaurant_name']) ?></p>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($order['restaurant_address']) ?></p>
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="font-medium text-gray-700 mb-1">
                                        <i class="fas fa-user mr-2 text-orange-500"></i> Customer
                                    </h4>
                                    <p><?= htmlspecialchars($order['customer_name']) ?></p>
                                </div>
                                
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" name="accept_order" class="w-full bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                                        Accept Delivery
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Profile Tab -->
        <div id="profile-tab-content" class="tab-content hidden">
            <h2 class="text-2xl font-bold mb-6">My Profile</h2>
            
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <form method="POST" enctype="multipart/form-data">
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Profile Image -->
                            <div class="text-center">
                                <div class="profile-image-container mb-4">
                                    <img src="../../<?= htmlspecialchars($userData['image'] ?: 'assets/default-profile.jpg') ?>" 
                                         alt="Profile Image" id="profile-image-preview">
                                </div>
                                <div class="flex justify-center">
                                    <label class="cursor-pointer bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                                        <i class="fas fa-camera mr-2"></i> Change Photo
                                        <input type="file" name="profile_image" id="profile-image-input" class="hidden" accept="image/*">
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Personal Info -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 mb-4">Personal Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($userData['name'] ?? '') ?>" 
                                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" 
                                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100 cursor-not-allowed" disabled>
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>" 
                                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                    </div>
                                    <div>
                                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                        <textarea id="address" name="address" rows="3" 
                                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500"><?= htmlspecialchars($userData['address'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Location Map -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 mb-4">Delivery Location</h3>
                                <p class="text-sm text-gray-500 mb-2">Set your current location for better order assignments</p>
                                <div id="profile-map" class="map-container mb-4"></div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                                        <input type="text" id="latitude" name="latitude" 
                                               value="<?= htmlspecialchars($deliveryLocation['latitude'] ?? '') ?>" 
                                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                    </div>
                                    <div>
                                        <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                                        <input type="text" id="longitude" name="longitude" 
                                               value="<?= htmlspecialchars($deliveryLocation['longitude'] ?? '') ?>" 
                                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                    </div>
                                </div>
                                <button type="button" id="get-location-btn" class="mt-4 bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                                    <i class="fas fa-location-arrow mr-2"></i> Use My Current Location
                                </button>
                            </div>
                            
                            <!-- Save Button -->
                            <div class="pt-6 border-t border-gray-200">
                                <button type="submit" name="update_profile" class="w-full bg-orange-500 text-white px-4 py-2 rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Tab functionality
        const tabs = {
            'assigned-tab': 'assigned-orders',
            'available-tab': 'available-orders',
            'profile-tab': 'profile-tab-content'
        };

        Object.entries(tabs).forEach(([tabId, contentId]) => {
            document.getElementById(tabId).addEventListener('click', function() {
                // Hide all tab contents and deactivate all tabs
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                document.querySelectorAll('.tab-button').forEach(tab => {
                    tab.classList.remove('active');
                    tab.classList.add('text-gray-500');
                });

                // Show selected tab content and activate tab
                document.getElementById(contentId).classList.remove('hidden');
                this.classList.add('active');
                this.classList.remove('text-gray-500');
            });
        });
// Store map instances by order ID
const orderMaps = {};

// Initialize maps for orders with coordinates
function initMap(orderId, customerLat, customerLng) {
    if (orderMaps[orderId]) {
        // Map already exists, just return it
        return orderMaps[orderId];
    }
    
    const map = L.map(`map-${orderId}`).setView([customerLat, customerLng], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Add customer location marker
    L.marker([customerLat, customerLng]).addTo(map)
        .bindPopup('Customer Location')
        .openPopup();
    
    // Store the map instance
    orderMaps[orderId] = map;
    
    return map;
}

// Show route from current location to customer
function showRoute(orderId, customerLat, customerLng) {
    if (!navigator.geolocation) {
        alert("Geolocation is not supported by this browser.");
        initMap(orderId, customerLat, customerLng);
        return;
    }

    // Show loading state
    const button = document.querySelector(`button[onclick="showRoute(${orderId}, ${customerLat}, ${customerLng})"]`);
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Getting Location...';
    button.disabled = true;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            
            // Initialize or get existing map
            const map = orderMaps[orderId] || initMap(orderId, customerLat, customerLng);
            
            // Clear existing layers except base tile layer
            map.eachLayer(layer => {
                if (!layer._url || !layer._url.includes('tile.openstreetmap.org')) {
                    map.removeLayer(layer);
                }
            });
            
            // Add markers
            const userMarker = L.marker([userLat, userLng]).addTo(map)
                .bindPopup('Your Location')
                .openPopup();
            
            const customerMarker = L.marker([customerLat, customerLng]).addTo(map)
                .bindPopup('Customer Location');
            
            // Add routing line
            const route = L.polyline(
                [[userLat, userLng], [customerLat, customerLng]],
                {color: 'blue', dashArray: '5, 5', weight: 3}
            ).addTo(map);
            
            // Fit map to show both points
            map.fitBounds([
                [userLat, userLng],
                [customerLat, customerLng]
            ]);
            
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        },
        (error) => {
            alert(`Error getting your location: ${error.message}`);
            // Just show customer location if geolocation fails
            initMap(orderId, customerLat, customerLng);
            
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

        // Initialize profile map
        let profileMap;
        let profileMarker;
        
        function initProfileMap(lat, lng) {
            profileMap = L.map('profile-map').setView([lat || 0, lng || 0], lat && lng ? 13 : 2);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(profileMap);
            
            if (lat && lng) {
                profileMarker = L.marker([lat, lng]).addTo(profileMap)
                    .bindPopup('Your Location')
                    .openPopup();
            }
            
            // Add click event to update location
            profileMap.on('click', function(e) {
                const { lat, lng } = e.latlng;
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);
                
                if (profileMarker) {
                    profileMarker.setLatLng([lat, lng]);
                } else {
                    profileMarker = L.marker([lat, lng]).addTo(profileMap)
                        .bindPopup('Your Location')
                        .openPopup();
                }
            });
        }

        // Get current location for profile
        document.getElementById('get-location-btn').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        document.getElementById('latitude').value = lat.toFixed(6);
                        document.getElementById('longitude').value = lng.toFixed(6);
                        
                        if (profileMarker) {
                            profileMarker.setLatLng([lat, lng]);
                            profileMap.setView([lat, lng], 13);
                        } else {
                            profileMarker = L.marker([lat, lng]).addTo(profileMap)
                                .bindPopup('Your Location')
                                .openPopup();
                            profileMap.setView([lat, lng], 13);
                        }
                    },
                    (error) => {
                        alert(`Error getting your location: ${error.message}`);
                    }
                );
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        });

        // Profile image preview
        document.getElementById('profile-image-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('profile-image-preview').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Initialize all maps on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize order maps
            <?php foreach ($assignedOrders as $order): ?>
                <?php if ($order['customer_latitude'] && $order['customer_longitude']): ?>
                    initMap(<?= $order['id'] ?>, <?= $order['customer_latitude'] ?>, <?= $order['customer_longitude'] ?>);
                <?php endif; ?>
            <?php endforeach; ?>
            
            // Initialize profile map
            const initialLat = <?= $deliveryLocation['latitude'] ?? 'null' ?>;
            const initialLng = <?= $deliveryLocation['longitude'] ?? 'null' ?>;
            initProfileMap(initialLat, initialLng);
        });
    </script>
</body>
</html>