<?php
session_start();
require_once '../../includes/dbh.inc.php';

// Check if user is logged in and is a restaurant owner
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'restaurant') {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Get restaurant ID for the current user
$stmt = $pdo->prepare("SELECT id FROM restaurants WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$restaurant = $stmt->fetch();
$restaurantId = $restaurant['id'] ?? null;

if (!$restaurantId) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'error' => 'No restaurant found for this user']);
    exit();
}

// Handle different actions
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_item':
        // Get single menu item
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ? AND restaurant_id = ?");
        $stmt->execute([$id, $restaurantId]);
        $item = $stmt->fetch();
        
        if ($item) {
            echo json_encode(['success' => true, 'data' => $item]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Menu item not found']);
        }
        break;
        
    default:
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}