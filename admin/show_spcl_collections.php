<?php
session_start(); // Start session

// Check if the admin is logged in; if not, redirect to login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Include config.php for database connection
include '../config/config.php';
include '../includes/header.php';

// Initialize messages
$success_message = "";
$error_message = "";

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM collect_money_society WHERE id = :id");
        $stmt->bindParam(':id', $delete_id);
        $stmt->execute();
        $success_message = "Collection deleted successfully!";
    } catch (Exception $e) {
        $error_message = "Error deleting collection: " . $e->getMessage();
    }
}

// Fetch collections data
try {
    $stmt = $pdo->prepare("SELECT * FROM collect_money_society");
    $stmt->execute();
    $collections = $stmt->fetchAll();
} catch (Exception $e) {
    $error_message = "Error fetching data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Collection List</h1>

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

    <!-- Collections Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Collection Name</th>
                <th>Total Amount</th>
                <th>Total Members</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($collections as $collection) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($collection['id']); ?></td>
                    <td><?php echo htmlspecialchars($collection['collection_name']); ?></td>
                    <td><?php echo htmlspecialchars($collection['total_amount']); ?></td>
                    <td><?php echo htmlspecialchars($collection['total_members']); ?></td>
                    <td>
                        <!-- View List button -->
                        <a href="view_spcl_list.php?collection_id=<?php echo $collection['id']; ?>" class="btn btn-info btn-sm">View List</a>
                        <!-- Delete button -->
                        <a href="?delete_id=<?php echo $collection['id']; ?>" onclick="return confirm('Are you sure you want to delete this collection?');" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
