<?php
include '../config/config.php';

// Initialize response
$response = ['success' => false, 'message' => ''];

// Check if the transaction ID is passed via GET
if (isset($_GET['id'])) {
    $transactionId = intval($_GET['id']);

    // Prepare SQL query to delete the transaction
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = :id");
    $stmt->bindParam(':id', $transactionId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['message'] = 'Error deleting the transaction.';
    }
} else {
    $response['message'] = 'Invalid request.';
}

// Return the response in JSON format
echo json_encode($response);
?>
