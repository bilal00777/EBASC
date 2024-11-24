<?php
session_start(); // Ensure session management

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Include database connection
include '../config/config.php';
include '../includes/header.php';

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate form inputs
    $title = isset($_POST['title']) ? htmlspecialchars($_POST['title']) : null;
    $image = isset($_FILES['image']) ? $_FILES['image'] : null;

    if (empty($title)) {
        $error_message = "Title is required.";
    } elseif ($image === null || $image['error'] == UPLOAD_ERR_NO_FILE) {
        $error_message = "Image is required.";
    } else {
        // Process the image upload
        $target_dir = "uploads_photo_g/";
        $target_file = $target_dir . basename($image['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image type
        $valid_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $valid_types)) {
            $error_message = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
        } elseif (move_uploaded_file($image['tmp_name'], $target_file)) {
            // Save data into the database
            try {
                $stmt = $pdo->prepare("INSERT INTO photogalleryone (title, image_path) VALUES (:title, :image_path)");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':image_path', $target_file);
                $stmt->execute();
                $success_message = "Form submitted successfully!";
            } catch (Exception $e) {
                $error_message = "Error submitting form: " . $e->getMessage();
            }
        } else {
            $error_message = "Error uploading image.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('imagePreview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Submit Form</h1>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Title:</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image:</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*" onchange="previewImage(event)" required>
            <img id="imagePreview" src="#" alt="Image Preview" style="display: none; max-width: 100%; margin-top: 10px;">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
</body>
</html>
