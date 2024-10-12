<?php
include '../config/config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $transactionId = intval($_POST['id']);
    $particulars = htmlspecialchars($_POST['particulars']);
    $transactionDate = $_POST['transaction_date'];
    $transactionType = $_POST['transaction_type'];
    $amount = $_POST['amount'];
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';

    // Handle file upload if a new one is provided
    $filePath = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . 'admin/upload_t_files/';
        $fileTmp = $_FILES['file']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['file']['name']);
        $filePath = $uploadDir . $fileName;

        // Move the file to the upload directory
        if (move_uploaded_file($fileTmp, $filePath)) {
            $filePath = 'admin/upload_t_files/' . $fileName;  // Store relative path in database
        } else {
            $response['message'] = 'Error uploading file.';
            echo json_encode($response);
            exit;
        }
    }

    // Update the transaction in the database
    $stmt = $pdo->prepare("UPDATE transactions SET particulars = :particulars, transaction_date = :transaction_date, 
                           transaction_type = :transaction_type, amount = :amount, description = :description" . 
                           ($filePath ? ", file_path = :file_path" : "") . " WHERE id = :id");
    
    $stmt->bindParam(':particulars', $particulars);
    $stmt->bindParam(':transaction_date', $transactionDate);
    $stmt->bindParam(':transaction_type', $transactionType);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':description', $description);
    if ($filePath) {
        $stmt->bindParam(':file_path', $filePath);
    }
    $stmt->bindParam(':id', $transactionId);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['message'] = 'Error updating transaction.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
