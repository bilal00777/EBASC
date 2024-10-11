<?php
// Include config.php for database connection
include '../config/config.php';

// Check if the member ID is provided
if (isset($_GET['id'])) {
    $member_id = intval($_GET['id']);

    // Fetch member details
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = :id");
    $stmt->bindParam(':id', $member_id, PDO::PARAM_INT);
    $stmt->execute();
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        // Send the data as a JSON response
        echo json_encode([
            'id' => $member['id'],
            'first_name' => htmlspecialchars($member['first_name']),
            'last_name' => htmlspecialchars($member['last_name']),
            'username' => htmlspecialchars($member['username']),
            'email' => htmlspecialchars($member['email']),
            'phone_number' => htmlspecialchars($member['phone_number']),
            'role' => htmlspecialchars($member['role']),
            'address' => htmlspecialchars($member['address']),
            'photo' => htmlspecialchars($member['photo'])  // Assuming the full path is stored in the database
        ]);
    } else {
        echo json_encode(['error' => 'Member not found']);
    }
} else {
    echo json_encode(['error' => 'No member ID provided']);
}
?>
