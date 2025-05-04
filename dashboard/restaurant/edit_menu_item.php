<?php
session_start();
require_once '../../includes/dbh.inc.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $requiredFields = ['id', 'name', 'price', 'restaurant_id'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields.");
            }
        }

        $itemId = $_POST['id'];
        $restaurantId = $_POST['restaurant_id'];
        $name = htmlspecialchars($_POST['name']);
        $description = htmlspecialchars($_POST['description'] ?? '');
        $price = floatval($_POST['price']);
        $available = isset($_POST['available']) ? 1 : 0;

        // Check if user owns this restaurant
        $stmt = $pdo->prepare("SELECT user_id FROM restaurants WHERE id = ?");
        $stmt->execute([$restaurantId]);
        $restaurant = $stmt->fetch();

        if (!$restaurant || $restaurant['user_id'] != $_SESSION['user_id']) {
            throw new Exception("You don't have permission to edit this item.");
        }

        // Handle file upload if new image was provided
        $imageUpdate = '';
        $params = [$name, $description, $price, $available, $itemId];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Validate image file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = $_FILES['image']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Only JPG, PNG, and GIF images are allowed.");
            }

            // Check file size (max 2MB)
            if ($_FILES['image']['size'] > 2097152) {
                throw new Exception("Image size must be less than 2MB.");
            }

            // Create upload directory if it doesn't exist
            $uploadDir = '../../uploads/menu_items/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'menu_item_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $imagePath = 'uploads/menu_items/' . $filename;
                $imageUpdate = ", image = ?";
                array_unshift($params, $imagePath);
            } else {
                throw new Exception("Failed to upload image.");
            }
        }

        // Update menu item in database
        $stmt = $pdo->prepare("
            UPDATE menu_items 
            SET name = ?, description = ?, price = ?, available = ? $imageUpdate
            WHERE id = ? AND restaurant_id = ?
        ");
        
        $params[] = $restaurantId; // Add restaurant_id for WHERE clause
        $stmt->execute($params);

        // Redirect back with success message
        header("Location: settings.php?tab=menu&success=Menu+item+updated+successfully");
        exit();

    } catch (Exception $e) {
        // Redirect back with error message
        header("Location: settings.php?tab=menu&error=" . urlencode($e->getMessage()));
        exit();
    }
}

// If not a POST request or direct access, redirect back
header('Location: settings.php?tab=menu');
exit();
?>