<?php
session_start();
require_once '../../includes/dbh.inc.php'; 


$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id FROM restaurants WHERE user_id = ?");
$stmt->execute([$userId]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    header('Location: ./createRestaurant.php');
}

$restaurantId = $restaurant['id'];

$stmt = $pdo->prepare("
    SELECT orders.*, users.name AS customer_name
    FROM orders
    JOIN users ON users.id = orders.customer_id
    WHERE orders.restaurant_id = ?
    ORDER BY orders.created_at DESC
");
$stmt->execute([$restaurantId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../../public/css/all.min.css" />
    <link rel="stylesheet" href="../../public/css/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link
      href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;500&amp;display=swap"
      rel="stylesheet"
    />
    <title>Dashboard</title>
  </head>
  <body>
    <div class="loader">
      <h1>Loading<span>....</span></h1>
    </div>
    <div class="page-content index-page">
      <?php include('sidebar.php'); ?>
      
      <main>
        <?php include('navbar.php'); ?>
        <h1>Orders</h1>
      
      <?php if (empty($orders)): ?>
        <p class="no-orders">No orders found.</p>
      <?php else: ?>
        <div class="orders-table">
          <table border="1" cellpadding="10" cellspacing="0">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Placed At</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row["id"]) ?></td>
                  <td><?= htmlspecialchars($row["customer_name"]) ?></td>
                  <td>$<?= number_format($row["total"], 2) ?></td>
                  <td><?= ucfirst($row['status']) ?></td>
                  <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>


      </main>
    </div>
    <script src="../../public/js/script.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
      lucide.createIcons();
    </script>
  </body>
</html>
