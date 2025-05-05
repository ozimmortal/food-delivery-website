<?php
ob_start();
session_start();
require_once '../../includes/dbh.inc.php';

// Check if user is logged in as a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get customer's details
$customerAddress = null;
$customerLat = null;
$customerLng = null;
$stmt = $pdo->prepare("SELECT address, phone, image FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
$image = $user['image'] ?? 'assets/default-user.jpg';
$phone = $user['phone'] ?? '';
if ($user && !empty($user['address'])) {
    $customerAddress = $user['address'];
}

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $restaurantId = $_POST['restaurant_id'];
    $menuItemId = $_POST['menu_item_id'];
    $quantity = $_POST['quantity'] ?? 1;

    // Initialize cart with proper structure if not exists
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [
            'restaurant_id' => $restaurantId,
            'items' => []
        ];
    }

    // Check if adding from different restaurant
    if (isset($_SESSION['cart']['restaurant_id']) && $_SESSION['cart']['restaurant_id'] != $restaurantId) {
        $_SESSION['cart_error'] = "You can only order from one restaurant at a time. Please clear your cart or complete your current order first.";
        header("Location: index.php?restaurant_id=$restaurantId");
        exit();
    }

    // Ensure items array exists
    if (!isset($_SESSION['cart']['items'])) {
        $_SESSION['cart']['items'] = [];
    }

    // Get menu item details
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ? AND restaurant_id = ?");
    $stmt->execute([$menuItemId, $restaurantId]);
    $menuItem = $stmt->fetch();

    if ($menuItem) {
        // Add or update item in cart
        $itemFound = false;
        foreach ($_SESSION['cart']['items'] as &$item) {
            if ($item['id'] == $menuItemId) {
                $item['quantity'] += $quantity;
                $itemFound = true;
                break;
            }
        }

        if (!$itemFound) {
            $_SESSION['cart']['items'][] = [
                'id' => $menuItemId,
                'name' => $menuItem['name'],
                'price' => $menuItem['price'],
                'quantity' => $quantity,
                'image' => $menuItem['image'] ?? 'assets/default-food.jpg'
            ];
        }

        $_SESSION['cart_success'] = "Item added to cart!";
    }

    header("Location: index.php?restaurant_id=$restaurantId");
    exit();
}

// Handle removing from cart
if (isset($_GET['remove_item'])) {
    $itemId = $_GET['remove_item'];
    
    if (isset($_SESSION['cart']['items'])) {
        foreach ($_SESSION['cart']['items'] as $key => $item) {
            if ($item['id'] == $itemId) {
                unset($_SESSION['cart']['items'][$key]);
                $_SESSION['cart']['items'] = array_values($_SESSION['cart']['items']);
                $_SESSION['cart_success'] = "Item removed from cart!";
                break;
            }
        }
        
        // If cart is empty, clear restaurant_id
        if (empty($_SESSION['cart']['items'])) {
            unset($_SESSION['cart']['restaurant_id']);
        }
    }
    
    header("Location: index.php" . (isset($_GET['restaurant_id']) ? "?restaurant_id=" . $_GET['restaurant_id'] : ""));
    exit();
}

// Handle clearing cart
if (isset($_GET['clear_cart'])) {
    unset($_SESSION['cart']);
    $_SESSION['cart_success'] = "Cart cleared successfully!";
    header("Location: index.php" . (isset($_GET['restaurant_id']) ? "?restaurant_id=" . $_GET['restaurant_id'] : ""));
    exit();
}

// Handle saving location
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_location'])) {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $address = $_POST['address'] ?? '';
    
    // Validate coordinates
    if (is_numeric($lat) && is_numeric($lng) && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
        // Update user's address
        $stmt = $pdo->prepare("UPDATE users SET address = ? WHERE id = ?");
        $stmt->execute([$address, $userId]);
        
        $_SESSION['lat'] = $lat;
        $_SESSION['lng'] = $lng;
        $_SESSION['location_success'] = "Location updated successfully!";
    } else {
        $_SESSION['location_error'] = "Invalid coordinates received.";
    }
    
    header("Location: index.php" . (isset($_GET['restaurant_id']) ? "?restaurant_id=" . $_GET['restaurant_id'] : ""));
    exit();
}

// Handle placing order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart']['items'])) {
        $_SESSION['cart_error'] = "Your cart is empty!";
        header("Location: index.php");
        exit();
    }

    // Validate address
    $address = $_POST['delivery_address'];
    if (empty($address)) {
        $_SESSION['cart_error'] = "Please enter a delivery address";
        header("Location: index.php");
        exit();
    }

    // Get coordinates if available
    $lat = $_POST['lat'] ?? $_SESSION['lat'] ?? null;
    $lng = $_POST['lng'] ?? $_SESSION['lng'] ?? null;
    
    // Validate coordinates
    if ($lat === null || $lng === null || !is_numeric($lat) || !is_numeric($lng)) {
        $_SESSION['cart_error'] = "Please set your delivery location on the map";
        header("Location: index.php");
        exit();
    }

    // Save address to user profile if changed
    if ($address !== $customerAddress) {
        $stmt = $pdo->prepare("UPDATE users SET address = ? WHERE id = ?");
        $stmt->execute([$address, $userId]);
    }

    // Calculate total
    $total = 0;
    foreach ($_SESSION['cart']['items'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Create order
    try {
        $pdo->beginTransaction();

        // Insert order
        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, restaurant_id, total, status, customer_latitude, customer_longitude, created_at) VALUES (?, ?, ?, 'placed', ?, ?, NOW())");
        $stmt->execute([
            $userId, 
            $_SESSION['cart']['restaurant_id'], 
            $total,
            $lat,
            $lng
        ]);
        $orderId = $pdo->lastInsertId();

        // Insert order items
        foreach ($_SESSION['cart']['items'] as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
        }

        $pdo->commit();

        // Clear cart and show success
        unset($_SESSION['cart']);
        $_SESSION['order_success'] = "Order placed successfully! Your order number is #$orderId";

        // Redirect to orders page or show success
        header("Location: orders.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['cart_error'] = "Error placing order: " . $e->getMessage();
        header("Location: index.php");
        exit();
    }
}

// Get all restaurants
$restaurants = [];
$stmt = $pdo->prepare("SELECT r.*, COUNT(mi.id) as menu_items_count 
                       FROM restaurants r 
                       LEFT JOIN menu_items mi ON r.id = mi.restaurant_id AND mi.available = 1
                       GROUP BY r.id");
$stmt->execute();
$restaurants = $stmt->fetchAll();

// Get menu items if a restaurant is selected
$menuItems = [];
$selectedRestaurant = null;
if (isset($_GET['restaurant_id'])) {
    $restaurantId = $_GET['restaurant_id'];
    
    // Get restaurant details
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $selectedRestaurant = $stmt->fetch();
    
    // Get available menu items
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE restaurant_id = ? AND available = 1 ORDER BY name");
    $stmt->execute([$restaurantId]);
    $menuItems = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Food | Savory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .restaurant-card, .menu-item-card {
            transition: all 0.3s ease;
        }
        .restaurant-card:hover, .menu-item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .cart-item {
            transition: all 0.2s ease;
        }
        .cart-item:hover {
            background-color: #f8fafc;
        }
        .tab-button {
            transition: all 0.2s ease;
        }
        .tab-button.active {
            border-bottom: 2px solid #f97316;
            color: #f97316;
            font-weight: 500;
        }
        #map {
            height: 300px;
            width: 100%;
            border-radius: 0.5rem;
            margin-top: 1rem;
            z-index: 0;
        }
        .location-btn {
            transition: all 0.2s ease;
        }
        .location-btn:hover {
            background-color: #f0f0f0;
        }
        .leaflet-control-attribution {
            font-size: 10px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="#" class="text-xl font-bold text-orange-500 flex items-center">
                        <i class="fas fa-utensils mr-2"></i>
                        Sweet Bite
                    </a>
                    <div class="user-avatar w-10 h-10 rounded-full ml-4 border-blue-100">
                            <img src="../../<?php echo htmlspecialchars($image) ?>" alt="User Avatar" class="w-10 h-10 rounded-full">
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="orders.php" class="text-gray-600 hover:text-orange-500">
                        <i class="fas fa-clipboard-list mr-1"></i> My Orders
                    </a>
                    <a href="../auth/logout.php" class="text-gray-600 hover:text-orange-500">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-8">
            <div class="flex space-x-8">
                <a href="index.php" class="tab-button py-4 px-1 active">
                    <i class="fas fa-utensils mr-2"></i> Order Food
                </a>
                <a href="orders.php" class="tab-button py-4 px-1 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-clipboard-list mr-2"></i> My Orders
                </a>
                <a href="account.php" class="tab-button py-4 px-1 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-user mr-2"></i> My Account
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['cart_success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 p-4 mb-6">
                <p class="text-green-700"><?= htmlspecialchars($_SESSION['cart_success']) ?></p>
            </div>
            <?php unset($_SESSION['cart_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['cart_error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-red-700"><?= htmlspecialchars($_SESSION['cart_error']) ?></p>
            </div>
            <?php unset($_SESSION['cart_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['location_success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 p-4 mb-6">
                <p class="text-green-700"><?= htmlspecialchars($_SESSION['location_success']) ?></p>
            </div>
            <?php unset($_SESSION['location_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['location_error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-red-700"><?= htmlspecialchars($_SESSION['location_error']) ?></p>
            </div>
            <?php unset($_SESSION['location_error']); ?>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left Column - Restaurants & Menu -->
            <div class="lg:w-2/3">
                <?php if (!isset($_GET['restaurant_id'])): ?>
                    <!-- Restaurant List -->
                    <h2 class="text-2xl font-bold mb-6">Choose a Restaurant</h2>
                    
                    <?php if (empty($restaurants)): ?>
                        <div class="bg-white p-8 rounded-lg shadow text-center">
                            <i class="fas fa-utensils text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">No restaurants available at the moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($restaurants as $restaurant): ?>
                                <a href="index.php?restaurant_id=<?= htmlspecialchars($restaurant['id']) ?>" class="restaurant-card bg-white rounded-lg shadow overflow-hidden">
                                    <div class="relative">
                                        <img src="../../<?= htmlspecialchars($restaurant['image'] ?? 'assets/default-restaurant.jpg') ?>" 
                                             alt="<?= htmlspecialchars($restaurant['name']) ?>" 
                                             class="w-full h-48 object-cover">
                                    </div>
                                    <div class="p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="font-bold text-lg"><?= htmlspecialchars($restaurant['name']) ?></h3>
                                                <p class="text-gray-600 text-sm mt-1">
                                                    <i class="fas fa-map-marker-alt mr-1 text-orange-500"></i>
                                                    <?= htmlspecialchars($restaurant['address']) ?>
                                                </p>
                                            </div>
                                            <span class="bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full">
                                                <?= htmlspecialchars($restaurant['menu_items_count']) ?> items
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Restaurant Menu -->
                    <div class="flex items-center mb-6">
                        <a href="index.php" class="text-orange-500 hover:text-orange-600 mr-4">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h2 class="text-2xl font-bold"><?= htmlspecialchars($selectedRestaurant['name']) ?></h2>
                    </div>
                    
                    <p class="text-gray-600 mb-6">
                        <i class="fas fa-map-marker-alt mr-1 text-orange-500"></i>
                        <?= htmlspecialchars($selectedRestaurant['address']) ?>
                    </p>
                    
                    <?php if (empty($menuItems)): ?>
                        <div class="bg-white p-8 rounded-lg shadow text-center">
                            <i class="fas fa-utensils text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">This restaurant has no available menu items at the moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($menuItems as $item): ?>
                                <div class="menu-item-card bg-white rounded-lg shadow overflow-hidden">
                                    <div class="flex flex-col md:flex-row">
                                        <div class="md:w-1/3">
                                            <img src="../../<?= htmlspecialchars($item['image'] ?? 'assets/default-food.jpg') ?>" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                                 class="w-full h-48 object-cover">
                                        </div>
                                        <div class="md:w-2/3 p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h3 class="font-bold text-lg"><?= htmlspecialchars($item['name']) ?></h3>
                                                    <p class="text-orange-500 font-bold mt-1">$<?= number_format($item['price'], 2) ?></p>
                                                </div>
                                            </div>
                                            <p class="text-gray-600 mt-2"><?= htmlspecialchars($item['description']) ?></p>
                                            <form method="POST" class="mt-4 flex items-center">
                                                <input type="hidden" name="restaurant_id" value="<?= htmlspecialchars($selectedRestaurant['id']) ?>">
                                                <input type="hidden" name="menu_item_id" value="<?= htmlspecialchars($item['id']) ?>">
                                                <div class="flex items-center mr-4">
                                                    <button type="button" class="quantity-btn bg-gray-200 px-3 py-1 rounded-l" onclick="decrementQuantity(this)">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" name="quantity" value="1" min="1" class="w-12 text-center border-t border-b border-gray-300 py-1">
                                                    <button type="button" class="quantity-btn bg-gray-200 px-3 py-1 rounded-r" onclick="incrementQuantity(this)">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                <button type="submit" name="add_to_cart" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                                                    Add to Cart
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Right Column - Cart -->
            <div class="lg:w-1/3">
                <div class="bg-white rounded-lg shadow sticky top-4">
                    <div class="p-4 border-b">
                        <h3 class="font-bold text-lg flex items-center">
                            <i class="fas fa-shopping-cart mr-2 text-orange-500"></i>
                            Your Order
                            <?php if (isset($_SESSION['cart']['restaurant_id']) && isset($selectedRestaurant)): ?>
                                <span class="ml-auto text-sm font-normal">
                                    <?= htmlspecialchars($selectedRestaurant['name']) ?>
                                </span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    
                    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart']['items'])): ?>
                        <div class="p-4">
                            <div class="space-y-3 mb-4 max-h-96 overflow-y-auto">
                                <?php 
                                $subtotal = 0;
                                foreach ($_SESSION['cart']['items'] as $item): 
                                    $itemTotal = $item['price'] * $item['quantity'];
                                    $subtotal += $itemTotal;
                                ?>
                                    <div class="cart-item flex items-start p-2 border-b border-gray-100">
                                        <img src="../../<?= htmlspecialchars($item['image'] ?? 'assets/default-food.jpg') ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>" 
                                             class="w-12 h-12 object-cover rounded mr-3">
                                        <div class="flex-grow">
                                            <h4 class="font-medium"><?= htmlspecialchars($item['name']) ?></h4>
                                            <div class="flex justify-between text-sm text-gray-600">
                                                <span>$<?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?></span>
                                                <span>$<?= number_format($itemTotal, 2) ?></span>
                                            </div>
                                        </div>
                                        <a href="index.php?remove_item=<?= $item['id'] ?>&restaurant_id=<?= $_GET['restaurant_id'] ?? '' ?>" 
                                           class="text-red-500 hover:text-red-700 ml-2">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>$<?= number_format($subtotal, 2) ?></span>
                                </div>
                                <div class="flex justify-between font-bold text-lg">
                                    <span>Total:</span>
                                    <span>$<?= number_format($subtotal, 2) ?></span>
                                </div>
                                
                                <!-- Delivery Address Form -->
                                <form method="POST" class="mt-4" id="orderForm">
                                    <div class="mb-4">
                                        <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-1">
                                            Delivery Address
                                        </label>
                                        <textarea id="delivery_address" name="delivery_address" rows="3" required
                                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"><?= 
                                            htmlspecialchars($customerAddress ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                            Phone Number
                                        </label>
                                        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" required
                                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    </div>
                                    
                                    <!-- Location Section -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Delivery Location
                                        </label>
                                        <div class="flex items-center space-x-2 mb-2">
                                            <button type="button" id="getLocationBtn" class="location-btn bg-gray-100 px-3 py-2 rounded flex items-center text-sm">
                                                <i class="fas fa-location-arrow mr-2"></i> Use Current Location
                                            </button>
                                            <span id="locationStatus" class="text-sm text-gray-500"></span>
                                        </div>
                                        <div id="map"></div>
                                        <input type="hidden" id="lat" name="lat" value="<?= isset($_SESSION['lat']) ? htmlspecialchars($_SESSION['lat']) : '' ?>">
                                        <input type="hidden" id="lng" name="lng" value="<?= isset($_SESSION['lng']) ? htmlspecialchars($_SESSION['lng']) : '' ?>">
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <a href="index.php?clear_cart=1&restaurant_id=<?= $_GET['restaurant_id'] ?? '' ?>" 
                                           class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 flex-grow text-center">
                                            Clear Cart
                                        </a>
                                        <button type="submit" name="place_order" 
                                                class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 flex-grow">
                                            Place Order
                                        </button>
                                    </div>
                                </form>
                                
                                <!-- Separate form for saving location without placing order -->
                                <form method="POST" id="locationForm" class="hidden">
                                    <input type="hidden" id="save_lat" name="lat">
                                    <input type="hidden" id="save_lng" name="lng">
                                    <input type="hidden" id="save_address" name="address">
                                    <input type="hidden" name="save_location" value="1">
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-shopping-cart text-4xl mb-3 text-gray-300"></i>
                            <p>Your cart is empty</p>
                            <?php if (!isset($_GET['restaurant_id'])): ?>
                                <p class="text-sm mt-2">Select a restaurant to start ordering</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <script>
        let map;
        let marker;
        let geocoder = L.Control.Geocoder.nominatim();

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map with default view
            initMap(0, 0, 2);
            
            // Check if we have previous location in session
            <?php if (isset($_SESSION['lat']) && isset($_SESSION['lng'])): ?>
                initMap(<?= $_SESSION['lat'] ?>, <?= $_SESSION['lng'] ?>, 15);
                updateLocation(<?= $_SESSION['lat'] ?>, <?= $_SESSION['lng'] ?>, "<?= addslashes($customerAddress ?? '') ?>");
            <?php endif; ?>

            // Set up location button
            const locationBtn = document.getElementById('getLocationBtn');
            locationBtn.addEventListener('click', requestLocationPermission);
        });

        function initMap(lat, lng, zoom) {
            const mapElement = document.getElementById('map');
            
            if (map) {
                map.remove();
            }

            map = L.map(mapElement).setView([lat, lng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 18
            }).addTo(map);

            // Add geocoder control
            L.Control.geocoder({
                defaultMarkGeocode: false,
                geocoder: geocoder,
                position: 'topright',
                placeholder: 'Search address...',
                errorMessage: 'Address not found.'
            })
            .on('markgeocode', function(e) {
                const center = e.geocode.center;
                updateLocation(center.lat, center.lng, e.geocode.name);
            })
            .addTo(map);

            // Add click handler for map
            map.on('click', function(e) {
                updateLocation(e.latlng.lat, e.latlng.lng);
            });

            // Set reasonable bounds if we have a location
            if (lat && lng && zoom > 10) {
                addMarker(lat, lng);
                map.setView([lat, lng], zoom);
            }
        }

        function requestLocationPermission() {
            const statusElement = document.getElementById('locationStatus');
            
            // First check if we already have permission
            if (navigator.permissions) {
                navigator.permissions.query({name: 'geolocation'})
                    .then(function(permissionStatus) {
                        if (permissionStatus.state === 'granted') {
                            getLocation();
                        } else if (permissionStatus.state === 'prompt') {
                            showLocationPrompt();
                        } else {
                            showPermissionDenied();
                        }
                        
                        permissionStatus.onchange = function() {
                            if (this.state === 'granted') {
                                getLocation();
                            } else {
                                showPermissionDenied();
                            }
                        };
                    });
            } else {
                // Fallback for browsers that don't support permissions API
                showLocationPrompt();
            }
        }

        function showLocationPrompt() {
            const statusElement = document.getElementById('locationStatus');
            statusElement.innerHTML = `
                <span class="text-sm text-gray-600">Please allow location access in your browser</span>
                <button onclick="getLocation()" class="ml-2 text-sm text-blue-500 hover:text-blue-700">
                    Try Again
                </button>
            `;
            
            // Directly try to get location (will trigger browser prompt)
            getLocation();
        }

        function showPermissionDenied() {
            const statusElement = document.getElementById('locationStatus');
            statusElement.innerHTML = `
                <span class="text-sm text-red-500">Location access was denied. 
                <button onclick="showLocationHelp()" class="text-blue-500 hover:text-blue-700">
                    How to enable?
                </button>
                </span>
            `;
        }

        function showLocationHelp() {
            alert("To enable location access:\n\n1. Click the padlock icon in your browser's address bar\n2. Select 'Site settings'\n3. Change 'Location' to 'Allow'\n4. Refresh the page");
        }

        function getLocation() {
            const statusElement = document.getElementById('locationStatus');
            statusElement.innerHTML = '<span class="text-sm text-gray-600">Detecting your location...</span>';

            if (!navigator.geolocation) {
                statusElement.innerHTML = '<span class="text-sm text-red-500">Geolocation not supported by your browser</span>';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    updateLocation(lat, lng);
                    saveLocation(lat, lng);
                    
                    statusElement.innerHTML = '<span class="text-sm text-green-500">Location detected!</span>';
                },
                function(error) {
                    let errorMessage = "Error: ";
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            showPermissionDenied();
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += "Location unavailable";
                            statusElement.innerHTML = `<span class="text-sm text-red-500">${errorMessage}</span>`;
                            break;
                        case error.TIMEOUT:
                            errorMessage += "Request timed out";
                            statusElement.innerHTML = `<span class="text-sm text-red-500">${errorMessage}</span>`;
                            break;
                        default:
                            errorMessage += "Unknown error";
                            statusElement.innerHTML = `<span class="text-sm text-red-500">${errorMessage}</span>`;
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function addMarker(lat, lng) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(map);

                marker.on('dragend', function() {
                    const newPosition = marker.getLatLng();
                    updateLocation(newPosition.lat, newPosition.lng);
                });
            }

            map.flyTo([lat, lng], 15, { animate: true, duration: 1.5 });
        }

        function updateLocation(lat, lng, address = null) {
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            addMarker(lat, lng);

            if (address) {
                document.getElementById('delivery_address').value = address;
            } else {
                reverseGeocode(lat, lng);
            }
        }

        function reverseGeocode(lat, lng) {
            geocoder.reverse(
                { lat: lat, lng: lng },
                map.getZoom(),
                function(results) {
                    if (results && results.length > 0) {
                        document.getElementById('delivery_address').value = results[0].name;
                    }
                }
            );
        }

        function saveLocation(lat, lng) {
            const address = document.getElementById('delivery_address').value;
            document.getElementById('save_lat').value = lat;
            document.getElementById('save_lng').value = lng;
            document.getElementById('save_address').value = address;
            document.getElementById('locationForm').submit();
        }

        // Quantity button functionality
        function incrementQuantity(button) {
            const input = button.parentElement.querySelector('input[type="number"]');
            input.value = parseInt(input.value) + 1;
        }

        function decrementQuantity(button) {
            const input = button.parentElement.querySelector('input[type="number"]');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }
    </script>
</body>
</html>
<?php ob_end_flush(); ?>