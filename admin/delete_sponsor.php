<?php
// Include config.php for database connection
include '../config/config.php';

// Initialize response
$response = ['success' => false, 'message' => ''];

// Check if the sponsor ID is provided via GET
if (isset($_GET['id'])) {
    $sponsor_id = intval($_GET['id']);

    // Ensure sponsor ID is valid
    if ($sponsor_id > 0) {
        // Prepare the SQL delete query
        $stmt = $pdo->prepare("DELETE FROM sponsors WHERE id = :id");
        $stmt->bindParam(':id', $sponsor_id, PDO::PARAM_INT);

        // Execute the query
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Sponsor deleted successfully.';
        } else {
            $errorInfo = $stmt->errorInfo();
            $response['message'] = 'Error deleting sponsor from the database: ' . $errorInfo[2];
        }
    } else {
        $response['message'] = 'Invalid sponsor ID.';
    }
} else {
    $response['message'] = 'Sponsor ID not provided.';
}

// Return response as JSON
echo json_encode($response);

// Close the PDO connection (optional, but good practice)
$pdo = null;
?>
