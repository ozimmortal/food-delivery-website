<?php
session_start();
require_once '../../includes/dbh.inc.php';

// Check if user is logged in as a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];


// Get customer's address if set
$customerAddress = null;
$stmt = $pdo->prepare("SELECT address, image FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
$image = $user['image'];
if ($user && !empty($user['address'])) {
    $customerAddress = $user['address'];
}

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $restaurantId = $_POST['restaurant_id'];
    $menuItemId = $_POST['menu_item_id'];
    $quantity = $_POST['quantity'] ?? 1;

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [
            'restaurant_id' => $restaurantId,
            'items' => []
        ];
    }

    // Check if adding from different restaurant
    if ($_SESSION['cart']['restaurant_id'] != $restaurantId) {
        $_SESSION['cart_error'] = "You can only order from one restaurant at a time. Please clear your cart or complete your current order first.";
        header("Location: index.php?restaurant_id=$restaurantId");
        exit();
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
                'image' => $menuItem['image']
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
        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, restaurant_id, total, status, created_at) VALUES (?, ?, ?, 'placed', NOW())");
        $stmt->execute([$userId, $_SESSION['cart']['restaurant_id'], $total]);
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
                            <img src="../<?php echo $image ?>" alt="User Avatar" class="w-10 h-10 rounded-full">
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
                <p class="text-green-700"><?= $_SESSION['cart_success'] ?></p>
            </div>
            <?php unset($_SESSION['cart_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['cart_error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-red-700"><?= $_SESSION['cart_error'] ?></p>
            </div>
            <?php unset($_SESSION['cart_error']); ?>
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
                                <a href="index.php?restaurant_id=<?= $restaurant['id'] ?>" class="restaurant-card bg-white rounded-lg shadow overflow-hidden">
                                    <div class="relative">
                                        <img src="../../<?= $restaurant['image'] ?: 'assets/default-restaurant.jpg' ?>" 
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
                                                <?= $restaurant['menu_items_count'] ?> items
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
                                            <img src="../../<?= $item['image'] ?: 'assets/default-food.jpg' ?>" 
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
                                                <input type="hidden" name="restaurant_id" value="<?= $selectedRestaurant['id'] ?>">
                                                <input type="hidden" name="menu_item_id" value="<?= $item['id'] ?>">
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
            
        </div>
    </div>

    <script>
        // Quantity buttons functionality
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
        
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // You can add any initialization code here if needed
        });
    </script>
</body>
</html>