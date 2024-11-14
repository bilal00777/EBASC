<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = (int) $_POST['member_id']; // The `id` field in `collection_members`
    $collection_id = (int) $_POST['collection_id'];
    $status = $_POST['status'];
    $paid_amount = (float) $_POST['paid_amount'];

    // Update `collection_members` table
    $update_query = "UPDATE collection_members SET status = :status, paid_amount = :paid_amount WHERE member_id = :member_id AND collection_id = :collection_id";
    $stmt = $pdo->prepare($update_query);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':paid_amount', $paid_amount, PDO::PARAM_STR);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->bindParam(':collection_id', $collection_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Fetch member name and collection heading
        $memberStmt = $pdo->prepare("SELECT member_name FROM collection_members WHERE member_id = :member_id AND collection_id = :collection_id");
        $memberStmt->execute(['member_id' => $member_id, 'collection_id' => $collection_id]);
        $member = $memberStmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Member not found']);
            exit();
        }

        $collectionStmt = $pdo->prepare("SELECT heading FROM collections WHERE id = :collection_id");
        $collectionStmt->execute(['collection_id' => $collection_id]);
        $collection = $collectionStmt->fetch(PDO::FETCH_ASSOC);

        if (!$collection) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Collection not found']);
            exit();
        }

        // Handle 'paid' status
        if (strtolower($status) === 'paid') {
            // Prepare transaction table data
            $particulars = 'Cid-' . $collection_id . '-' . $member['member_name'];
            $transaction_type = 'income';
            $transaction_date = date('Y-m-d');
            $description = $collection['heading'];

            // Insert into transactions table
            $transaction_query = "INSERT INTO transactions (particulars, transaction_date, transaction_type, amount, description) VALUES (:particulars, :transaction_date, :transaction_type, :amount, :description)";
            $transactionStmt = $pdo->prepare($transaction_query);
            $transactionStmt->bindParam(':particulars', $particulars, PDO::PARAM_STR);
            $transactionStmt->bindParam(':transaction_date', $transaction_date, PDO::PARAM_STR);
            $transactionStmt->bindParam(':transaction_type', $transaction_type, PDO::PARAM_STR);
            $transactionStmt->bindParam(':amount', $paid_amount, PDO::PARAM_STR);
            $transactionStmt->bindParam(':description', $description, PDO::PARAM_STR);

            if ($transactionStmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to insert transaction']);
            }
        }
        // Handle 'pending' status by removing the transaction
        elseif (strtolower($status) === 'pending') {
            $particulars = 'Cid-' . $collection_id . '-' . $member['member_name'];
            $delete_transaction_query = "DELETE FROM transactions WHERE particulars = :particulars";
            $deleteStmt = $pdo->prepare($delete_transaction_query);
            $deleteStmt->bindParam(':particulars', $particulars, PDO::PARAM_STR);

            if ($deleteStmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Transaction deleted for pending status']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete transaction']);
            }
        } else {
            echo json_encode(['status' => 'success']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update member']);
    }
}
?>
