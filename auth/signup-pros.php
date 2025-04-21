<?php


if($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $fname = $_POST['fname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        require_once '../includes/dbh.inc.php';

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fname, $email, password_hash($password, PASSWORD_DEFAULT), $role]);

        $pdo = null;
        $stmt = null;

        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        header("Location: ../dashboard/home.php");
        die();
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }

}else{
    header("Location: onboarding.php");
}