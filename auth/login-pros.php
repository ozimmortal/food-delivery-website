<?php


    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];

        try {
            require_once '../includes/dbh.inc.php';

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if($stmt->rowCount() > 0) {
                $user = $stmt->fetch();

                if(password_verify($password, $user['password'])) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    $pdo = null;
                    $stmt = null;

                    header("Location: ../dashboard/home.php");
                    die();
                }else{
                    $pdo = null;
                    $stmt = null;

                    header("Location: ../auth/login.php?error=wrong_password");
                    die();
                }
            }else{
                $pdo = null;
                $stmt = null;

                header("Location: ../auth/login.php?error=user_not_found");
                die();
            }
        } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }else{
            header("Location: ../auth/signup.php");
        }