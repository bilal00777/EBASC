<?php
session_start(); // Start session at the beginning

// Check if the admin is logged in; if not, redirect to the login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Include config.php for database connection
include '../config/config.php';
include '../includes/header.php';

// Set the directory for image uploads
$uploadDir = '/photo_gallery';
$maxFileSize = 5764341; // 5,764,341 bytes
$allowedAspectRatio = 3 / 2;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the 'heading' and 'image' fields are set in the POST data
    if (isset($_POST['heading']) && isset($_FILES['image'])) {
        $heading = $_POST['heading'];
        $image = $_FILES['image'];

       

        

        // Generate unique file name and path
        $fileName = uniqid() . '-' . basename($image['name']);
        $targetFile = $uploadDir.'/' . $fileName;

        // Move the file to the target directory
        if (move_uploaded_file($image['tmp_name'], $targetFile)) {
            // Prepare and execute the database insertion
            $stmt = $conn->prepare("INSERT INTO photo_gallery_one (heading, image_path) VALUES (?, ?)");
            $stmt->bind_param("ss", $heading, $targetFile);
            $stmt->execute();
            $stmt->close();
            $conn->close();

            echo "Image uploaded successfully!<br>";
            echo "<img src='$targetFile' alt='Image preview' style='max-width: 100%; height: auto;'>";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "Please ensure both heading and image are provided.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image</title>
</head>
<body>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="heading">Image Heading:</label>
        <input type="text" name="heading" id="heading" required><br><br>
        
        <label for="image">Select Image:</label>
        <input type="file" name="image" id="image" accept="image/*" required><br><br>
        
        <input type="submit" value="Upload Image">
    </form>
</body>
</html>
