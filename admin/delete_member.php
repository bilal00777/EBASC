<?php
// Include config.php for database connection
include '../config/config.php';

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Check if member ID is passed via GET
if (isset($_GET['id'])) {
    $member_id = intval($_GET['id']);

    // Delete the member from the database
    $stmt = $pdo->prepare("DELETE FROM members WHERE id = :id");
    $stmt->bindParam(':id', $member_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // If successful, set response success to true
        $response['success'] = true;
        $response['message'] = 'Member successfully deleted.';
    } else {
        // If execution failed, provide an error message
        $response['message'] = 'Failed to delete member. Please try again.';
    }
} else {
    // If no ID is passed via GET
    $response['message'] = 'No member ID provided.';
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
