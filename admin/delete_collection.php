<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Include database configuration
include '../config/config.php';

// Check if collection ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $collection_id = $_GET['id'];

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // First, delete associated members from `collection_members` table
        $deleteMembersQuery = "DELETE FROM collection_members WHERE collection_id = :collection_id";
        $deleteMembersStmt = $pdo->prepare($deleteMembersQuery);
        $deleteMembersStmt->bindValue(':collection_id', $collection_id, PDO::PARAM_INT);
        $deleteMembersStmt->execute();

        // Now delete the collection from `collections` table
        $deleteCollectionQuery = "DELETE FROM collections WHERE id = :collection_id";
        $deleteCollectionStmt = $pdo->prepare($deleteCollectionQuery);
        $deleteCollectionStmt->bindValue(':collection_id', $collection_id, PDO::PARAM_INT);
        $deleteCollectionStmt->execute();

        // Commit the transaction
        $pdo->commit();

        // Redirect back to collections list with success message
        header('Location: collection_list.php?message=Collection deleted successfully');
        exit();

    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $pdo->rollBack();
        // Log error or display an error message
        error_log("Failed to delete collection: " . $e->getMessage());
        header('Location: collection_list.php?error=Failed to delete collection');
        exit();
    }
} else {
    // Redirect if no valid ID is provided
    header('Location: collection_list.php?error=Invalid collection ID');
    exit();
}
?>
