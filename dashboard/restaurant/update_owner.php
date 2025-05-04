<?php
session_start();
require_once '../../includes/dbh.inc.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate user is updating their own profile
        if ($_POST['id'] != $_SESSION['user_id']) {
            throw new Exception("You can only update your own profile");
        }

        // Handle file upload
        $imagePath = null;
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
                $imagePath = 'uploads/profiles/' . $filename;
            }
        }

        // Handle password update if provided
        $passwordUpdate = '';
        $passwordParams = [];
        if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                throw new Exception("New passwords don't match");
            }

            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!password_verify($_POST['current_password'], $user['password'])) {
                throw new Exception("Current password is incorrect");
            }

            $passwordUpdate = ", password = ?";
            $passwordParams = [password_hash($_POST['new_password'], PASSWORD_DEFAULT)];
        }

        // Update user
        $sql = "UPDATE users 
                SET name = ?,
                    phone = ?,
                    image = COALESCE(?, image)";
        
        // Add password update if needed
        if (!empty($passwordUpdate)) {
            $sql .= $passwordUpdate;
        }
        
        $sql .= " WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        
        // Build parameters array
        $params = [
            htmlspecialchars($_POST['name']),
            htmlspecialchars($_POST['phone']),
            $imagePath
        ];
        
        // Add password if being updated
        if (!empty($passwordParams)) {
            $params = array_merge($params, $passwordParams);
        }
        
        // Add user ID for WHERE clause
        $params[] = $_SESSION['user_id'];
        
        $stmt->execute($params);
        
        // Update session email if changed
        if ($_SESSION['user_email'] !== $_POST['email']) {
            $_SESSION['user_email'] = $_POST['email'];
        }
        
        header("Location: settings.php?tab=owner&success=Profile+updated+successfully");
        exit();
    } catch (Exception $e) {
        header("Location: settings.php?tab=owner&error=" . urlencode($e->getMessage()));
        exit();
    }
}

header('Location: settings.php?tab=owner');