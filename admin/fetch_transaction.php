<?php
include '../config/config.php';

if (isset($_GET['id'])) {
    $transactionId = intval($_GET['id']);

    // Fetch the transaction data
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = :id");
    $stmt->bindParam(':id', $transactionId);
    $stmt->execute();
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transaction) {
        // Return the transaction as JSON
        echo json_encode([
            'id' => $transaction['id'],
            'particulars' => $transaction['particulars'],
            'transaction_date' => $transaction['transaction_date'],
            'transaction_type' => $transaction['transaction_type'],
            'amount' => $transaction['amount'],
            'description' => $transaction['description'],
           'file_path' => $transaction['file_path'] ? $transaction['file_path'] : null

        ]);
    } else {
        echo json_encode(['error' => 'Transaction not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
