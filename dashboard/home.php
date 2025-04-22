<?php
    include './middleware.php';

    require_once '../includes/dbh.inc.php';

    $restaurant_id = $_SESSION['user_id'];
    $name = $_SESSION['user_name'];

    // Fetch food items
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE restaurant_id = ?");
    $stmt->execute([$restaurant_id]);
    $foods = $stmt->fetchAll();

    // Fetch orders
    $orderStmt = $pdo->prepare("SELECT o.id, o.customer_id, o.status, f.name AS food_name FROM orders o JOIN menu_items f ON o.id = f.id WHERE f.restaurant_id = ?");
    $orderStmt->execute([$restaurant_id]);
    $orders = $orderStmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Restaurant Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <style>
    .dashboard-section { margin-bottom: 2rem; }
    .tabs.is-toggle a { font-weight: bold; }
  </style>
</head>
<body>
<section class="section">
  <div class="container">
    <h1 class="title">Welcome, <?= htmlspecialchars($name) ?>!</h1>
    <nav class="tabs is-toggle is-toggle-rounded">
      <ul>
        <li class="is-active"><a href="#profile">Profile</a></li>
        <li><a href="#add">Add Menu Item</a></li>
        <li><a href="#menu">Menu</a></li>
        <li><a href="#orders">Orders</a></li>
        <li><a href="logout.php" class="has-text-danger">Logout</a></li>
      </ul>
    </nav>

    <!-- Profile Section -->
    <div id="profile" class="dashboard-section box">
      <h2 class="subtitle">Restaurant Profile</h2>
      <form action="update_profile.php" method="POST">
        <div class="field">
          <label class="label">Restaurant Name</label>
          <div class="control">
            <input class="input" type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
          </div>
        </div>
        <div class="field">
          <label class="label">Description</label>
          <div class="control">
            <textarea class="textarea" name="description" placeholder="Short description"></textarea>
          </div>
        </div>
        <button class="button is-link">Update Profile</button>
      </form>
    </div>

    <!-- Add Menu Item -->
    <div id="add" class="dashboard-section box">
      <h2 class="subtitle">Add New Food Item</h2>
      <form action="add_food.php" method="POST">
        <div class="field">
          <label class="label">Food Name</label>
          <div class="control">
            <input class="input" type="text" name="name" required>
          </div>
        </div>
        <div class="field">
          <label class="label">Price</label>
          <div class="control">
            <input class="input" type="number" name="price" step="0.01" required>
          </div>
        </div>
        <div class="field">
          <label class="label">Category</label>
          <div class="control">
            <input class="input" type="text" name="category">
          </div>
        </div>
        <button class="button is-primary">Add Food</button>
      </form>
    </div>

    <!-- Menu List -->
    <div id="menu" class="dashboard-section box">
      <h2 class="subtitle">Your Food Items</h2>
      <table class="table is-fullwidth is-hoverable">
        <thead>
          <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Category</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($foods as $food): ?>
            <tr>
              <td><?= htmlspecialchars($food['name']) ?></td>
              <td>$<?= number_format($food['price'], 2) ?></td>
              <td><?= htmlspecialchars($food['category'] ?? 'N/A') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Orders -->
    <div id="orders" class="dashboard-section box">
      <h2 class="subtitle">Incoming Orders</h2>
      <table class="table is-fullwidth is-striped">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Food</th>
            <th>Customer</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><?= $order['id'] ?></td>
              <td><?= htmlspecialchars($order['food_name']) ?></td>
              <td><?= htmlspecialchars($order['customer_id']) ?></td>
              <td><?= htmlspecialchars($order['status']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>
</section>
</body>
</html>
