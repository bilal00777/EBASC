<?php
// Include config.php for database connection
include '../config/config.php';

// Initialize response
$response = ['success' => false, 'message' => ''];

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get sponsor ID from hidden form input (add this input in your form if needed)
    $sponsor_id = isset($_POST['id']) ? intval($_POST['id']) : null;

    // Validate sponsor ID
    if (!$sponsor_id) {
        $response['message'] = 'Invalid sponsor ID.';
        echo json_encode($response);
        exit;
    }

    // Collect form inputs
    $company_name = htmlspecialchars($_POST['company_name']);
    $social_media_link = htmlspecialchars($_POST['social_media_link']);

    // Handle file upload for the logo (if a new one is provided)
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        // Get file information
        $logo = $_FILES['logo']['name'];
        $logo_tmp = $_FILES['logo']['tmp_name'];

        // Set the upload directory to an absolute path
        $upload_dir =  "admin/uploads/";

        // Check if the uploads directory exists, create it if not
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Set the full path of the file to be uploaded
        $upload_file = $upload_dir . basename($logo);

        // Move the uploaded file to the upload directory
        if (move_uploaded_file($logo_tmp, $upload_file)) {
            // Update the database with new logo
            $stmt = $pdo->prepare("UPDATE sponsors SET company_name = :company_name, logo = :logo, social_media_link = :social_media_link WHERE id = :id");
            $stmt->bindParam(':company_name', $company_name);
            $stmt->bindParam(':logo', $upload_file);
            $stmt->bindParam(':social_media_link', $social_media_link);
            $stmt->bindParam(':id', $sponsor_id);
        } else {
            $response['message'] = 'Error uploading the logo.';
            echo json_encode($response);
            exit;
        }
    } else {
        // If no new logo is uploaded, update without changing the logo
        $stmt = $pdo->prepare("UPDATE sponsors SET company_name = :company_name, social_media_link = :social_media_link WHERE id = :id");
        $stmt->bindParam(':company_name', $company_name);
        $stmt->bindParam(':social_media_link', $social_media_link);
        $stmt->bindParam(':id', $sponsor_id);
    }

    // Execute the SQL query to update the sponsor
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Sponsor updated successfully.';
    } else {
        // Log and show SQL error message
        $errorInfo = $stmt->errorInfo();
        $response['message'] = 'Error updating sponsor in the database: ' . $errorInfo[2];
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Return the response in JSON format
echo json_encode($response);

// Close the PDO connection (optional, but good practice)
$pdo = null;
?>
