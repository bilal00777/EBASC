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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize inputs
    $heading = htmlspecialchars($_POST['heading']);
    $amount = htmlspecialchars($_POST['amount']);
    $category = htmlspecialchars($_POST['category']);
    
    // Validate form data
    if (empty($heading) || empty($amount) || empty($category)) {
        $error_message = "All fields are required.";
    } else {
        try {
            // Start a transaction
            $pdo->beginTransaction();
            
            // Insert the new collection purpose into the 'collections' table
            $stmt = $pdo->prepare("INSERT INTO collections (heading, amount, category_id) VALUES (:heading, :amount, :category_id)");
            $stmt->bindParam(':heading', $heading);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':category_id', $category);
            $stmt->execute();

            // Get the ID of the newly created collection
            $collection_id = $pdo->lastInsertId();

            // Fetch members from the 'members' table who match the selected category
            $members_stmt = $pdo->prepare("SELECT id, CONCAT(first_name, ' ', last_name) AS member_name FROM members WHERE category = :category");
            $members_stmt->bindParam(':category', $category);
            $members_stmt->execute();
            $members = $members_stmt->fetchAll();

            // Insert each member into the 'collection_members' table
            $insert_member_stmt = $pdo->prepare("INSERT INTO collection_members (collection_id, member_id, member_name, status, paid_amount) VALUES (:collection_id, :member_id, :member_name, 'pending', 0)");
            foreach ($members as $member) {
                $insert_member_stmt->bindParam(':collection_id', $collection_id);
                $insert_member_stmt->bindParam(':member_id', $member['id']);
                $insert_member_stmt->bindParam(':member_name', $member['member_name']);
                $insert_member_stmt->execute();
            }

            // Commit the transaction
            $pdo->commit();
            $success_message = "Collection created successfully and member list updated!";
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $pdo->rollBack();
            $error_message = "Error creating collection and updating members: " . $e->getMessage();
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

        <div class="mb-3">
            <label for="amount" class="form-label">Amount:</label>
            <input type="number" name="amount" id="amount" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Member Category:</label>
            <select name="category" id="category" class="form-control" required>
                <?php
                // Fetch categories from the 'members' table
                $categories = $pdo->query("SELECT DISTINCT category FROM members")->fetchAll();
                foreach ($categories as $cat) {
                    echo "<option value='{$cat['category']}'>{$cat['category']}</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Create Collection</button>
    </form>
</div>
</body>
</html>
