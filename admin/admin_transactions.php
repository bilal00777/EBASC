<?php
session_start(); // Make sure session_start() is the first thing in the script

// Check if the admin is logged in, if not, redirect to the login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit(); // Make sure to call exit() after header to stop script execution
}

// Include necessary files after session checks
include '../includes/header.php';
include '../config/config.php';



// Initialize a variable for JavaScript execution
$showSuccessModal = false;

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and collect form inputs
    $particulars = htmlspecialchars($_POST['particulars']);
    $transaction_date = $_POST['transaction_date'];
    $transaction_type = $_POST['transaction_type'];
    $amount = $_POST['amount'];
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : null;

    // Handle file upload (if any)
    $file_path = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['file']['name']);
        $upload_dir = 'admin/upload_t_files/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_path = $upload_dir . $file_name;
        if (move_uploaded_file($file_tmp, $file_path)) {
            $file_path = 'admin/upload_t_files/' . $file_name; // Store the relative path in the DB
        } else {
            echo "Error uploading the file.";
            exit;
        }
    }

    // Insert the data into the transactions table
    $stmt = $pdo->prepare("INSERT INTO transactions 
                            (particulars, transaction_date, transaction_type, file_path, amount, description) 
                            VALUES (:particulars, :transaction_date, :transaction_type, :file_path, :amount, :description)");
    $stmt->bindParam(':particulars', $particulars);
    $stmt->bindParam(':transaction_date', $transaction_date);
    $stmt->bindParam(':transaction_type', $transaction_type);
    $stmt->bindParam(':file_path', $file_path);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':description', $description);

    if ($stmt->execute()) {
        $showSuccessModal = true;  // Trigger the modal in JavaScript
    } else {
        echo "Error updating transaction.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Transaction</title>
    <!-- Bootstrap CSS (Optional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Update Transaction</h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="particulars" class="form-label">Particulars</label>
            <input type="text" class="form-control" id="particulars" name="particulars" required>
        </div>

        <div class="mb-3">
            <label for="transaction_date" class="form-label">Transaction Date</label>
            <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Transaction Type</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="transaction_type" id="expense" value="expense" required>
                <label class="form-check-label" for="expense">Expense</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="transaction_type" id="income" value="income" required>
                <label class="form-check-label" for="income">Income</label>
            </div>
        </div>

        <div class="mb-3">
            <label for="file" class="form-label">File (optional)</label>
            <input type="file" class="form-control" id="file" name="file">
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description (optional)</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Transaction</button>
    </form>
</div>




<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success" id="successModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-success">
                Transaction updated successfully!
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Trigger Modal and Timeout -->
<?php if ($showSuccessModal): ?>
<script>
    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();

    // Automatically close the modal after 2 seconds
    setTimeout(function() {
        successModal.hide();
    }, 2000);
</script>
<?php endif; ?>
</body>
</html>
