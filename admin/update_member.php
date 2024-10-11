<?php
// Include config.php for database connection
include '../config/config.php';

// Initialize response for AJAX call
$response = ['success' => false, 'message' => ''];

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get member ID
    $member_id = isset($_POST['id']) ? intval($_POST['id']) : null;

    // Validate member ID
    if (!$member_id) {
        $response['message'] = 'Invalid member ID.';
        echo json_encode($response);
        exit;
    }

    // Collect form inputs and sanitize them
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $phone_number = htmlspecialchars($_POST['phone_number']);
    $role = htmlspecialchars($_POST['role']);
    $address = htmlspecialchars($_POST['address']);

   // Check if a new photo is uploaded
$photo = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    
    // Validate file type (only images are allowed)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($_FILES['photo']['type'], $allowed_types)) {
        $response['message'] = 'Invalid file type. Only JPG, PNG, and GIF files are allowed.';
        echo json_encode($response);
        exit;
    }

    // Validate file size (limit to 2MB)
    $max_file_size = 2 * 1024 * 1024; // 2MB
    if ($_FILES['photo']['size'] > $max_file_size) {
        $response['message'] = 'File size exceeds 2MB limit.';
        echo json_encode($response);
        exit;
    }

    // Set the upload directory (ensure this folder exists)
    $upload_dir = 'admin/upload_member/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true); // Create the directory if it doesn't exist
    }

    // Generate a unique file name to avoid overwriting
    $photo_name = time() . '_' . basename($_FILES['photo']['name']);
    $photo_tmp = $_FILES['photo']['tmp_name'];
    $photo_path = $upload_dir . $photo_name;

    // Move the uploaded file to the desired directory
if (move_uploaded_file($photo_tmp, $photo_path)) {
    $photo = 'admin/upload_member/' . $photo_name;  // Store the relative path in the database
} else {
    $response['message'] = 'Error uploading the photo.';
    echo json_encode($response);
    exit;
}

}


    // Prepare SQL for updating the member details
    $sql = "UPDATE members 
            SET first_name = :first_name, last_name = :last_name, username = :username, email = :email, phone_number = :phone_number, role = :role, address = :address";

    // Add photo update if a new one was uploaded
    if ($photo) {
        $sql .= ", photo = :photo";
    }

    $sql .= " WHERE id = :id";

    // Prepare the statement
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone_number', $phone_number);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':id', $member_id, PDO::PARAM_INT);

    // Bind the photo parameter if it was updated
    if ($photo) {
        $stmt->bindParam(':photo', $photo);
    }

    // Execute the statement
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Member updated successfully!';
    } else {
        $response['message'] = 'Error updating member.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Return the response as JSON
echo json_encode($response);
