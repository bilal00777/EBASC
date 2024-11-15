<?php
include '../config/config.php'; // Include database configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_POST['member_id'];
    $collect_id = $_POST['collect_id'];
    $status = $_POST['status'];
    $paid_amount = $_POST['paid_amount'];

    try {
        $stmt = $pdo->prepare("UPDATE society_members SET status = :status, paid_amount = :paid_amount WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':paid_amount', $paid_amount);
        $stmt->bindParam(':id', $member_id);
        $stmt->execute();

        // Redirect back to the main page with a success message
        $_SESSION['success_message'] = "Member updated successfully!";
        header("Location:view_spcl_list.php?collection_id=" . $collect_id); // Corrected string concatenation
        exit();
    } catch (Exception $e) {
        // Handle error
        $_SESSION['error_message'] = "Error updating member: " . $e->getMessage();
        header("Location:view_spcl_list.php?collection_id=" . $collect_id); // Corrected string concatenation
        exit();
    }
}
?>
