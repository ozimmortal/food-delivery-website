<?php
session_start();
require_once '../../includes/dbh.inc.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address'])) {
    try {
        // First check if address column exists
        $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'address'");
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            // Create the column if it doesn't exist
            $pdo->exec("ALTER TABLE users ADD COLUMN address TEXT AFTER phone");
        }
        
        // Update the address
        $stmt = $pdo->prepare("UPDATE users SET address = ? WHERE id = ?");
        $stmt->execute([$_POST['address'], $_SESSION['user_id']]);
        
        $_SESSION['address_success'] = "Address updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['address_error'] = "Error updating address: " . $e->getMessage();
    }
}

header("Location: index.php");
exit();