<?php
session_start(); // Make sure session_start() is the first thing in the script

// Check if the admin is logged in, if not, redirect to the login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Include config.php for database connection
include '../config/config.php';
include '../includes/header.php';

// Initialize variables for success and error messages
$success_message = "";
$error_message = "";

// Handle form submission to create a new collection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input
    $heading = htmlspecialchars($_POST['heading']);
    
    if (empty($heading)) {
        $error_message = "The heading field is required.";
    } else {
        try {
            // Insert a new collection into collect_money_society with heading only
            $stmt = $pdo->prepare("INSERT INTO collect_money_society (collection_name) VALUES (:heading)");
            $stmt->bindParam(':heading', $heading);
            $stmt->execute();

            $success_message = "New collection created successfully!";
        } catch (Exception $e) {
            $error_message = "Error creating collection: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Create Collection</h1>

    <!-- Display Success and Error Messages -->
    <?php if (!empty($success_message)) : ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)) : ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Collection Form -->
    <form method="POST">
        <div class="mb-3">
            <label for="heading" class="form-label">Purpose:</label>
            <input type="text" name="heading" id="heading" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Create Collection</button>
    </form>
</div>
</body>
</html>
