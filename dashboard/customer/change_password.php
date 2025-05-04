<?php
session_start();
require_once '../../includes/dbh.inc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validate current password
    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }
    } catch (PDOException $e) {
        $errors[] = "Database error";
    }
    
    // Validate new password
    if (empty($newPassword)) {
        $errors[] = "New password is required";
    } elseif (strlen($newPassword) < 8) {
        $errors[] = "Password must be at least 8 characters";
    } elseif ($newPassword !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // Update password if no errors
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            
            $_SESSION['success'] = "Password changed successfully!";
            header("Location: account.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Error updating password";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: account.php");
        exit();
    }
}