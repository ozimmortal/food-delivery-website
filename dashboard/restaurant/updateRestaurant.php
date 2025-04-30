<?php
session_start();
require_once '../../includes/dbh.inc.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Process form when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required = ['id', 'name', 'address', 'phone'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields");
            }
        }

        // Get restaurant ID
        $restaurantId = $_POST['id'];

        // Verify user owns this restaurant
        $stmt = $pdo->prepare("SELECT id FROM restaurants WHERE id = ? AND user_id = ?");
        $stmt->execute([$restaurantId, $_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            throw new Exception("Restaurant not found or you don't have permission to edit it");
        }

        // Handle file upload if present
        $imagePath = $_POST['current_image'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Configuration
            $uploadDir = '../../uploads/restaurants/';
            $maxFileSize = 2 * 1024 * 1024; // 2MB
            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp'
            ];

            // Get file info
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileType = mime_content_type($fileTmpPath);

            // Validate file
            if ($_FILES['image']['size'] > $maxFileSize) {
                throw new Exception('Image size must be less than 2MB');
            }

            if (!array_key_exists($fileType, $allowedTypes)) {
                throw new Exception('Only JPG, PNG, GIF, and WebP images are allowed');
            }

            // Generate new filename
            $fileExt = $allowedTypes[$fileType];
            $newFileName = 'rest_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExt;
            $destination = $uploadDir . $newFileName;

            // Move the file
            if (!move_uploaded_file($fileTmpPath, $destination)) {
                throw new Exception('Failed to move uploaded file');
            }

            // Delete old image if it exists
            if ($imagePath && file_exists('../../' . $imagePath)) {
                unlink('../../' . $imagePath);
            }

            // Set new image path
            $imagePath = 'uploads/restaurants/' . $newFileName;
        }

        // Update restaurant data
        $stmt = $pdo->prepare("
            UPDATE restaurants SET 
                name = ?,
                address = ?,
                phone = ?,
                image = COALESCE(?, image)
            WHERE id = ?;
        ");
        
        $stmt->execute([
            htmlspecialchars($_POST['name']),
            htmlspecialchars($_POST['address']),
            htmlspecialchars($_POST['phone']),
            $imagePath,
            $restaurantId
        ]);

        // Redirect to success page
        header("Location: ./settings.php?id=$restaurantId&success=1");
        exit();
        
    } catch (Exception $e) {
        // Log the error
        error_log('Restaurant update error: ' . $e->getMessage());
        
        // Redirect back with error
        $restaurantId = $_POST['id'] ?? '';
        header("Location: settings.php?id=$restaurantId&error=" . urlencode($e->getMessage()));
        exit();
    }
}

// If not a POST request, redirect back
header('Location: settings.php');
exit();