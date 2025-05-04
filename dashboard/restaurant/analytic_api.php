<?php
// Start output buffering to catch any accidental output
ob_start();

try {
    // Set headers first
    header('Content-Type: application/json');
    
    // Start session if needed for authentication
    session_start();
    
    // Include database connection
    require_once '../../includes/dbh.inc.php';
    
    // Check required parameters
    if (!isset($_GET['restaurant_id'])) {
        throw new Exception('Restaurant ID is required', 400);
    }

    $restaurantId = (int)$_GET['restaurant_id'];
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');

    // Validate restaurant ownership
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'restaurant') {
        throw new Exception('Unauthorized', 401);
    }

    $stmt = $pdo->prepare("SELECT id FROM restaurants WHERE id = ? AND user_id = ?");
    $stmt->execute([$restaurantId, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        throw new Exception('Restaurant not found or access denied', 404);
    }

    // 1. Get summary statistics
    $stmt = $pdo->prepare("
        SELECT 
            SUM(o.total) as total_revenue,
            COUNT(*) as total_orders,
            AVG(o.total) as avg_order_value
        FROM orders o
        WHERE o.restaurant_id = ? 
        AND o.created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$restaurantId, $startDate, $endDate]);
    $summary = $stmt->fetch();

    // 2. Get most popular item
    $stmt = $pdo->prepare("
        SELECT mi.name, SUM(oi.quantity) as quantity
        FROM order_items oi
        JOIN menu_items mi ON oi.menu_item_id = mi.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.restaurant_id = ? AND o.created_at BETWEEN ? AND ?
        GROUP BY mi.id
        ORDER BY quantity DESC
        LIMIT 1
    ");
    $stmt->execute([$restaurantId, $startDate, $endDate]);
    $popularItem = $stmt->fetch() ?: ['name' => null, 'quantity' => 0];

    // 3. Get revenue trend data
    $revenueTrend = ['labels' => [], 'data' => []];
    $date = new DateTime($startDate);
    $end = new DateTime($endDate);
    
    while ($date <= $end) {
        $currentDate = $date->format('Y-m-d');
        $revenueTrend['labels'][] = $date->format('M j');
        
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total), 0) as daily_revenue
            FROM orders
            WHERE restaurant_id = ? AND DATE(created_at) = ?
        ");
        $stmt->execute([$restaurantId, $currentDate]);
        $result = $stmt->fetch();
        $revenueTrend['data'][] = (float)$result['daily_revenue'];
        $date->modify('+1 day');
    }

    // 4. Get orders trend data
    $ordersTrend = ['data' => []];
    $date = new DateTime($startDate);
    $end = new DateTime($endDate);
    
    while ($date <= $end) {
        $currentDate = $date->format('Y-m-d');
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as daily_orders
            FROM orders
            WHERE restaurant_id = ? AND DATE(created_at) = ?
        ");
        $stmt->execute([$restaurantId, $currentDate]);
        $result = $stmt->fetch();
        $ordersTrend['data'][] = (int)$result['daily_orders'];
        $date->modify('+1 day');
    }

    // 5. Get status distribution
    $statusDistribution = ['labels' => [], 'values' => []];
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count
        FROM orders
        WHERE restaurant_id = ? AND created_at BETWEEN ? AND ?
        GROUP BY status
    ");
    $stmt->execute([$restaurantId, $startDate, $endDate]);
    $statusData = $stmt->fetchAll();
    
    foreach ($statusData as $status) {
        $statusDistribution['labels'][] = ucfirst(str_replace('_', ' ', $status['status']));
        $statusDistribution['values'][] = (int)$status['count'];
    }

    // 6. Get top selling items
    $stmt = $pdo->prepare("
        SELECT 
            mi.name,
            SUM(oi.quantity) as quantity,
            SUM(oi.quantity * oi.price) as revenue
        FROM order_items oi
        JOIN menu_items mi ON oi.menu_item_id = mi.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.restaurant_id = ? AND o.created_at BETWEEN ? AND ?
        GROUP BY mi.id
        ORDER BY revenue DESC
        LIMIT 5
    ");
    $stmt->execute([$restaurantId, $startDate, $endDate]);
    $topItems = $stmt->fetchAll();

    // Clear any output buffer
    ob_end_clean();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => [
            'total_revenue' => (float)$summary['total_revenue'] ?? 0,
            'total_orders' => (int)$summary['total_orders'] ?? 0,
            'avg_order_value' => (float)$summary['avg_order_value'] ?? 0,
            'popular_item' => $popularItem['name'],
            'revenue_trend' => $revenueTrend,
            'orders_trend' => $ordersTrend,
            'status_distribution' => $statusDistribution,
            'top_items' => $topItems
        ]
    ]);

} catch (Exception $e) {
    // Clean any output buffer
    ob_end_clean();
    
    http_response_code($e->getCode() >= 400 ? $e->getCode() : 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}