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


// Get the search and filter inputs
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// Build the query with search and filter conditions
$query = "SELECT * FROM transactions WHERE 1";

// Apply search filter if provided
if (!empty($searchTerm)) {
    $query .= " AND particulars LIKE :searchTerm";
}

// Apply date filters if provided
if (!empty($fromDate)) {
    $query .= " AND transaction_date >= :fromDate";
}
if (!empty($toDate)) {
    $query .= " AND transaction_date <= :toDate";
}

$query .= " ORDER BY transaction_date ASC";

// Prepare and execute the query
$stmt = $pdo->prepare($query);
if (!empty($searchTerm)) {
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
}
if (!empty($fromDate)) {
    $stmt->bindValue(':fromDate', $fromDate);
}
if (!empty($toDate)) {
    $stmt->bindValue(':toDate', $toDate);
}
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Transactions</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4 text-center">All Transactions</h1>

    <!-- Search and Filter Form -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by Particulars" value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>
            <div class="col-md-3">
                <input type="date" name="from_date" class="form-control" value="<?php echo htmlspecialchars($fromDate); ?>">
            </div>
            <div class="col-md-3">
                <input type="date" name="to_date" class="form-control" value="<?php echo htmlspecialchars($toDate); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>ID</th>
                <th>Particulars</th>
                <th>Transaction Date</th>
                <th>Transaction Type</th>
                <th>Amount</th>
                <th>Description</th>
                <th>File</th>
               
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $slNo = 1; // Initialize the serial number
            foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?php echo $slNo++; ?></td> <!-- Incremented Sl No -->
                    <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['particulars']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['transaction_type']); ?></td>
                    <td class="text-end"><?php echo number_format($transaction['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                    <td>
                        <?php if ($transaction['file_path']): ?>
                            <a href="<?php echo htmlspecialchars($transaction['file_path']); ?>" target="_blank">View File</a>
                        <?php else: ?>
                            No File
                        <?php endif; ?>
                    </td>
                    <td>
    <button type="button" class="btn btn-warning btn-sm" onclick="openEditModal(<?php echo $transaction['id']; ?>)">Edit</button>
</td>

<td>
    <button type="button" class="btn btn-danger btn-sm" onclick="openDeleteModal(<?php echo $transaction['id']; ?>)">Delete</button>
</td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>








<!-- Bootstrap Modal for Editing Transaction -->
<div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Form to Edit Transaction -->
        <form id="editTransactionForm" enctype="multipart/form-data">
          <input type="hidden" name="id" id="transaction_id">

          <div class="mb-3">
            <label for="edit_particulars" class="form-label">Particulars</label>
            <input type="text" class="form-control" id="edit_particulars" name="particulars" required>
          </div>

          <div class="mb-3">
            <label for="edit_transaction_date" class="form-label">Transaction Date</label>
            <input type="date" class="form-control" id="edit_transaction_date" name="transaction_date" required>
          </div>

          <div class="mb-3">
            <label for="edit_transaction_type" class="form-label">Transaction Type</label>
            <select class="form-control" id="edit_transaction_type" name="transaction_type" required>
              <option value="expense">Expense</option>
              <option value="income">Income</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="edit_amount" class="form-label">Amount</label>
            <input type="number" class="form-control" id="edit_amount" name="amount" step="0.01" required>
          </div>

          <div class="mb-3">
            <label for="edit_description" class="form-label">Description (optional)</label>
            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
          </div>

          <!-- File Upload -->
          <div class="mb-3">
            <label for="edit_file" class="form-label">File (optional)</label>
            <input type="file" class="form-control" id="edit_file" name="file">
            <a id="file_preview_link" href="#" target="_blank" style="display: none;">View Current File</a>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>





<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTransactionModal" tabindex="-1" aria-labelledby="deleteTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteTransactionModalLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this transaction?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>


<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>



<script>
    // Function to open the modal and populate it with the transaction data
function openEditModal(transactionId) {
    // Fetch transaction data using AJAX
    fetch('fetch_transaction.php?id=' + transactionId)
        .then(response => response.json())
        .then(data => {
            // Populate the modal form fields with the data
            document.getElementById('transaction_id').value = data.id;
            document.getElementById('edit_particulars').value = data.particulars;
            document.getElementById('edit_transaction_date').value = data.transaction_date;
            document.getElementById('edit_transaction_type').value = data.transaction_type;
            document.getElementById('edit_amount').value = data.amount;
            document.getElementById('edit_description').value = data.description;

            // Handle file preview
            if (data.file_path) {
                const fileLink = document.getElementById('file_preview_link');
                fileLink.href = data.file_path;
                fileLink.style.display = 'inline';
            } else {
                document.getElementById('file_preview_link').style.display = 'none';
            }

            // Show the modal
            const editModal = new bootstrap.Modal(document.getElementById('editTransactionModal'));
            editModal.show();
        })
        .catch(error => {
            console.error('Error fetching transaction:', error);
        });
}

// Handle form submission and send the update via AJAX
document.getElementById('editTransactionForm').addEventListener('submit', function(event) {
    event.preventDefault();  // Prevent traditional form submission

    const formData = new FormData(this);  // Collect form data including file

    fetch('update_transaction.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Transaction updated successfully!');
            location.reload();  // Reload the page to reflect changes
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating transaction:', error);
    });
});

</script>



<script>
    let transactionIdToDelete = null;

// Open the delete confirmation modal
function openDeleteModal(transactionId) {
    transactionIdToDelete = transactionId;  // Store the transaction ID to be deleted
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteTransactionModal'));
    deleteModal.show();
}

// Handle the delete confirmation button click
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (transactionIdToDelete !== null) {
        // Send an AJAX request to delete the transaction
        fetch('delete_transaction.php?id=' + transactionIdToDelete, {
            method: 'GET'  // or use 'POST' if needed
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Transaction deleted successfully!');
                location.reload();  // Reload the page after successful deletion
            } else {
                alert('Error deleting transaction: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting transaction:', error);
        });
    }
});

</script>
</body>
</html>
