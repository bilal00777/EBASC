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

// Initialize variables for search and date filters
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// Build the SQL query for expenses with search and date range filtering
$expenseQuery = "SELECT * FROM transactions WHERE transaction_type = 'expense'";
$incomeQuery = "SELECT * FROM transactions WHERE transaction_type = 'income'";

// Add search and date filter conditions
if (!empty($searchTerm)) {
    $expenseQuery .= " AND particulars LIKE :searchTerm";
    $incomeQuery .= " AND particulars LIKE :searchTerm";
}

if (!empty($fromDate)) {
    $expenseQuery .= " AND transaction_date >= :fromDate";
    $incomeQuery .= " AND transaction_date >= :fromDate";
}

if (!empty($toDate)) {
    $expenseQuery .= " AND transaction_date <= :toDate";
    $incomeQuery .= " AND transaction_date <= :toDate";
}

$expenseQuery .= " ORDER BY transaction_date ASC";
$incomeQuery .= " ORDER BY transaction_date ASC";

// Prepare and execute the expense query
$expenseStmt = $pdo->prepare($expenseQuery);
if (!empty($searchTerm)) $expenseStmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
if (!empty($fromDate)) $expenseStmt->bindValue(':fromDate', $fromDate);
if (!empty($toDate)) $expenseStmt->bindValue(':toDate', $toDate);
$expenseStmt->execute();
$expenses = $expenseStmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare and execute the income query
$incomeStmt = $pdo->prepare($incomeQuery);
if (!empty($searchTerm)) $incomeStmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
if (!empty($fromDate)) $incomeStmt->bindValue(':fromDate', $fromDate);
if (!empty($toDate)) $incomeStmt->bindValue(':toDate', $toDate);
$incomeStmt->execute();
$incomes = $incomeStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize totals
$totalExpense = 0;
$totalIncome = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance Sheet</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4 text-center">Balance Sheet</h1>

    <!-- Search and Filter Form -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by Particulars" value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>
            <div class="col-md-3">
                <input type="date" name="from_date" class="form-control" placeholder="From Date" value="<?php echo htmlspecialchars($fromDate); ?>">
            </div>
            <div class="col-md-3">
                <input type="date" name="to_date" class="form-control" placeholder="To Date" value="<?php echo htmlspecialchars($toDate); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr class="text-center">
                <th colspan="3">Expense</th>
                <th colspan="3">Income</th>
            </tr>
            <tr>
                <th>Date</th>
                <th>Particulars</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Particulars</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Determine the maximum number of rows between expenses and incomes
            $maxRows = max(count($expenses), count($incomes));

            // Display both expenses and incomes side by side
            for ($i = 0; $i < $maxRows; $i++) {
                echo "<tr>";

                // Expense data (left side)
                if (isset($expenses[$i])) {
                    $expense = $expenses[$i];
                    $totalExpense += $expense['amount']; // Add to total expenses
                    echo "<td>" . htmlspecialchars($expense['transaction_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($expense['particulars']) . "</td>";
                    echo "<td class='text-end'>" . number_format($expense['amount'], 2) . "</td>";
                } else {
                    echo "<td colspan='3'></td>";  // Empty cells if no more expenses
                }

                // Income data (right side)
                if (isset($incomes[$i])) {
                    $income = $incomes[$i];
                    $totalIncome += $income['amount']; // Add to total incomes
                    echo "<td>" . htmlspecialchars($income['transaction_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($income['particulars']) . "</td>";
                    echo "<td class='text-end'>" . number_format($income['amount'], 2) . "</td>";
                } else {
                    echo "<td colspan='3'></td>";  // Empty cells if no more incomes
                }

                echo "</tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="2" class="text-end">Total Expense:</td>
                <td class="text-end"><?php echo number_format($totalExpense, 2); ?></td>
                <td colspan="2" class="text-end">Total Income:</td>
                <td class="text-end"><?php echo number_format($totalIncome, 2); ?></td>
            </tr>
            <tr class="fw-bold">
                <td colspan="5" class="text-end">Net Balance:</td>
                <td class="text-end">
                    <?php
                    // Calculate the net balance (income - expense)
                    $netBalance = $totalIncome - $totalExpense;
                    echo number_format($netBalance, 2);
                    ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>









<!-- Download PDF Button -->
<button type="button" class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#pdfModal">
    Download PDF
</button>

<!-- PDF Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalLabel">Generate PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form id="pdfForm" action="generate_pdf.php" method="POST">
    <div class="mb-3">
        <label for="pdfHeading" class="form-label">PDF Heading</label>
        <input type="text" class="form-control" id="pdfHeading" name="pdfHeading" placeholder="Enter a heading for the PDF" required>
    </div>
    
    <input type="hidden" name="expenses" id="expensesData">
    <input type="hidden" name="incomes" id="incomesData">
    <input type="hidden" name="totalExpense" id="totalExpenseData">
    <input type="hidden" name="totalIncome" id="totalIncomeData">
    <input type="hidden" name="netBalance" id="netBalanceData">
    <button type="submit" class="btn btn-primary">Download PDF</button>
</form>

            </div>
        </div>
    </div>
</div>






<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


<script>
  function collectTableData() {
    const expenses = [];
    const incomes = [];

    // Get all rows from the table body
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const expenseDate = row.querySelector('td:nth-child(1)').innerText.trim();
        const expenseParticulars = row.querySelector('td:nth-child(2)').innerText.trim();
        const expenseAmount = row.querySelector('td:nth-child(3)').innerText.trim();
        const incomeDate = row.querySelector('td:nth-child(4)').innerText.trim();
        const incomeParticulars = row.querySelector('td:nth-child(5)').innerText.trim();
        const incomeAmount = row.querySelector('td:nth-child(6)').innerText.trim();

        if (expenseDate || expenseParticulars || expenseAmount) {
            expenses.push({
                date: expenseDate,
                particulars: expenseParticulars,
                amount: expenseAmount
            });
        }

        if (incomeDate || incomeParticulars || incomeAmount) {
            incomes.push({
                date: incomeDate,
                particulars: incomeParticulars,
                amount: incomeAmount
            });
        }
    });

    // Debug collected data in the browser console
    console.log("Expenses Data:", expenses);
    console.log("Incomes Data:", incomes);

    // Set the form hidden fields with the collected data as JSON strings
    document.getElementById('expensesData').value = JSON.stringify(expenses);
    document.getElementById('incomesData').value = JSON.stringify(incomes);
    document.getElementById('totalExpenseData').value = document.querySelector('tfoot td:nth-child(3)').innerText.trim();
    document.getElementById('totalIncomeData').value = document.querySelector('tfoot td:nth-child(6)').innerText.trim();
    document.getElementById('netBalanceData').value = document.querySelector('tfoot td:nth-child(6)').innerText.trim();
}

document.getElementById('pdfForm').addEventListener('submit', function (event) {
    collectTableData();  // Collect table data before form submission
});



document.getElementById('pdfForm').addEventListener('submit', function (event) {
    collectTableData();  // Collect table data before form submission
    console.log('Form data prepared for submission');
});


</script>




</body>
</html>
