<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include '../includes/header.php';
include '../config/config.php';

// Fetch total income
$incomeStmt = $pdo->prepare("SELECT SUM(amount) AS total_income FROM transactions WHERE transaction_type = 'income'");
$incomeStmt->execute();
$totalIncome = $incomeStmt->fetch(PDO::FETCH_ASSOC)['total_income'];
$totalIncome = $totalIncome !== null ? $totalIncome : 0;

// Fetch total expenses
$expenseStmt = $pdo->prepare("SELECT SUM(amount) AS total_expense FROM transactions WHERE transaction_type = 'expense'");
$expenseStmt->execute();
$totalExpense = $expenseStmt->fetch(PDO::FETCH_ASSOC)['total_expense'];
$totalExpense = $totalExpense !== null ? $totalExpense : 0;

// Calculate the current balance
$currentBalance = $totalIncome - $totalExpense;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-cards {
            margin-top: 50px;
        }
        .card {
            margin: 15px;
        }
    </style>
</head>
<body>
    <div class="container dashboard-cards">
        <div class="row justify-content-center">
            <!-- Total Income Card -->
            <div class="col-md-4">
                <div class="card text-bg-success mb-3">
                    <div class="card-header">Total Income</div>
                    <div class="card-body">
                        <h5 class="card-title">₹<?php echo number_format($totalIncome, 2); ?></h5>
                    </div>
                </div>
            </div>

            <!-- Total Expense Card -->
            <div class="col-md-4">
                <div class="card text-bg-danger mb-3">
                    <div class="card-header">Total Expense</div>
                    <div class="card-body">
                        <h5 class="card-title">₹<?php echo number_format($totalExpense, 2); ?></h5>
                    </div>
                </div>
            </div>

            <!-- Current Balance Card -->
            <div class="col-md-4">
                <div class="card text-bg-primary mb-3">
                    <div class="card-header">Current Balance</div>
                    <div class="card-body">
                        <h5 class="card-title">₹<?php echo number_format($currentBalance, 2); ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
