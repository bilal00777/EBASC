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
    // Handle file uploads
    if (isset($_POST['title']) && isset($_FILES['image'])) {
        $title = htmlspecialchars($_POST['title']);
        $image = $_FILES['image'];

        if ($image['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads_photo_g/";
            $original_file_name = pathinfo($image['name'], PATHINFO_FILENAME);
            $imageFileType = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $validTypes = ['jpg', 'jpeg', 'png', 'gif'];
            $maxSize = 5 * 1024 * 1024; // 5 MB

            if (in_array($imageFileType, $validTypes) && $image['size'] <= $maxSize) {
                $unique_file_name = $original_file_name . '_' . time() . '.' . $imageFileType;
                $target_file = $target_dir . $unique_file_name;

                if (move_uploaded_file($image['tmp_name'], $target_file)) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO photogalleryone (title, image_path) VALUES (:title, :image_path)");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':image_path', $unique_file_name);
                        $stmt->execute();
                        $success_message = "Form submitted successfully!";
                    } catch (Exception $e) {
                        $error_message = "Error saving to database: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Error uploading the image.";
                }
            } else {
                $error_message = "Invalid file type or size exceeds 5 MB.";
            }
        } else {
            $error_message = "Error in file upload.";
        }
    }

    // Handle delete functionality
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM photogalleryone WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $success_message = "Image deleted successfully!";
        } catch (Exception $e) {
            $error_message = "Error deleting image: " . $e->getMessage();
        }
    }
}

// Fetch existing records from the database
$query = $pdo->query("SELECT id, title, image_path FROM photogalleryone ORDER BY id DESC");
$records = $query->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
            <img id="imagePreview" src="#" alt="Image Preview" style="display: none; max-width: 50%;height:200px;object-fit:cover; margin-top: 10px;">
            <p id="imageMessage" style="display: none; margin-top: 10px; font-weight: bold;"></p>
            <p id="imageReason" style="display: none; margin-top: 5px; color: red;"></p>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
 <!-- Display Uploaded Files -->
 <div class="row">
        <?php foreach ($records as $record): ?>
            <div class="col-md-6">
                <div class="card mb-4">
                    <img src="uploads_photo_g/<?php echo htmlspecialchars($record['image_path']); ?>" class="card-img-top" style="width: 50%; height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($record['title']); ?></h5>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                            <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>


<script>
        function previewImage(event) {
    const fileInput = event.target.files[0];
    const output = document.getElementById('imagePreview');
    const message = document.getElementById('imageMessage');
    const reason = document.getElementById('imageReason');
    const jsValidField = document.getElementById('jsValid');

    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
    const maxSize = 3 * 1024 * 1024; // 5 MB

    if (!fileInput) {
        output.style.display = 'none';
        message.style.display = 'none';
        reason.style.display = 'none';
        jsValidField.value = '0'; // Not valid
        return;
    }

    // Check file type
    if (!validTypes.includes(fileInput.type)) {
        output.style.display = 'block';
        message.style.display = 'block';
        message.textContent = "The image is not applicable for update.";
        reason.style.display = 'block';
        reason.textContent = "Reason: Unsupported file type. Allowed types are JPG, JPEG, PNG, and GIF.";
        jsValidField.value = '0'; // Not valid
        output.src = '';
        return;
    }

    // Check file size
    if (fileInput.size > maxSize) {
        output.style.display = 'block';
        message.style.display = 'block';
        message.textContent = "The image is not applicable for update.";
        reason.style.display = 'block';
        reason.textContent = "Reason: File size exceeds the maximum limit of 3 MB.";
        jsValidField.value = '0'; // Not valid
        output.src = '';
        return;
    }

    // If valid
    const reader = new FileReader();
    reader.onload = function () {
        output.src = reader.result;
        output.style.display = 'block';
        message.style.display = 'block';
        message.textContent = "The image is applicable for update.";
        reason.style.display = 'none'; // No reason to display if valid
        jsValidField.value = '1'; // Valid
    };
    reader.readAsDataURL(fileInput);
}
 // Auto-hide success message
 setTimeout(() => {
            const successMessage = document.getElementById('successMessage');
            if (successMessage) successMessage.style.display = 'none';
        }, 3000); // 3 seconds
    </script>
</body>
</html>
