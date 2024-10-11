<?php
// Include config.php for database connection
include '../config/config.php';

// Check if the ID is provided through GET
if (isset($_GET['id'])) {
    // Sanitize the input
    $sponsor_id = htmlspecialchars($_GET['id']);

    try {
        // Prepare the query to fetch the sponsor data by ID
        $stmt = $pdo->prepare("SELECT * FROM sponsors WHERE id = :id");
        $stmt->bindParam(':id', $sponsor_id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the sponsor data
        $sponsor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sponsor) {
            // Return sponsor data in JSON format, directly using the stored logo path
            echo json_encode([
                'success' => true,
                'company_name' => htmlspecialchars($sponsor['company_name']),
                'social_media_link' => htmlspecialchars($sponsor['social_media_link']),
                'logo' => htmlspecialchars($sponsor['logo']) // Using the exact logo path stored in the DB
            ]);
        } else {
            // If no sponsor is found with the given ID
            echo json_encode(['success' => false, 'message' => 'Sponsor not found.']);
        }
    } catch (Exception $e) {
        // In case of a database error
        echo json_encode(['success' => false, 'message' => 'Error fetching sponsor data.']);
    }
} else {
    // If no ID is provided
    echo json_encode(['success' => false, 'message' => 'No sponsor ID provided.']);
}

// Close the PDO connection (optional, but good practice)
$pdo = null;
?>
