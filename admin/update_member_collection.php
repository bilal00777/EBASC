<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = (int) $_POST['member_id'];
    $collection_id = (int) $_POST['collection_id'];
    $status = $_POST['status'];
    $paid_amount = (float) $_POST['paid_amount'];

    $update_query = "UPDATE collection_members SET status = :status, paid_amount = :paid_amount WHERE member_id = :member_id AND collection_id = :collection_id";
    $stmt = $pdo->prepare($update_query);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':paid_amount', $paid_amount, PDO::PARAM_STR);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->bindParam(':collection_id', $collection_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update member']);
    }
    
}
?>
