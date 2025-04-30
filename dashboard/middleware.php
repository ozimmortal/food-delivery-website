<?php

    session_start();
    $role = "";
    if(!isset($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit();
    }else{
        $role = $_SESSION['user_role'];
    }

    if ($role == "restaurant") {
        header('Location: ./restaurant/index.php'); 
        exit();
    }elseif ($role == "customer") {
        header('Location: ./customer/index.php'); 
        exit();
    }elseif ($role == "delivery") {
        header('Location: ./delivery/index.php'); 
        exit();
    }