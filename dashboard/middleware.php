<?php

    session_start();
    $role = "";
    if(!isset($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit();
    }else{
        $role = $_SESSION['user_role'];
    }
