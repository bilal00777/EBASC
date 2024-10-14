<?php
session_start(); // Ensure session is started

// Check if the admin is logged in, if not, redirect to the login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Include necessary files
include '../config/config.php';
require('fpdf/fpdf.php'); // Include FPDF library

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

// PDF generation process
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Balance Sheet', 0, 1, 'C');
        $this->Ln(10);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Create instance of PDF class
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Table headers
$pdf->Cell(95, 10, 'Expense', 1, 0, 'C');
$pdf->Cell(95, 10, 'Income', 1, 1, 'C');

$pdf->Cell(30, 10, 'Date', 1);
$pdf->Cell(40, 10, 'Particulars', 1);
$pdf->Cell(25, 10, 'Amount', 1);
$pdf->Cell(30, 10, 'Date', 1);
$pdf->Cell(40, 10, 'Particulars', 1);
$pdf->Cell(25, 10, 'Amount', 1);
$pdf->Ln();

// Determine the maximum number of rows between expenses and incomes
$maxRows = max(count($expenses), count($incomes));

// Display both expenses and incomes side by side in PDF
for ($i = 0; $i < $maxRows; $i++) {
    // Expense data (left side)
    if (isset($expenses[$i])) {
        $expense = $expenses[$i];
        $totalExpense += $expense['amount'];
        $pdf->Cell(30, 10, $expense['transaction_date'], 1);
        $pdf->Cell(40, 10, $expense['particulars'], 1);
        $pdf->Cell(25, 10, number_format($expense['amount'], 2), 1);
    } else {
        $pdf->Cell(95, 10, '', 1); // Empty cells if no more expenses
    }

    // Income data (right side)
    if (isset($incomes[$i])) {
        $income = $incomes[$i];
        $totalIncome += $income['amount'];
        $pdf->Cell(30, 10, $income['transaction_date'], 1);
        $pdf->Cell(40, 10, $income['particulars'], 1);
        $pdf->Cell(25, 10, number_format($income['amount'], 2), 1);
    } else {
        $pdf->Cell(95, 10, '', 1); // Empty cells if no more incomes
    }

    $pdf->Ln();
}

// Totals row
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 10, 'Total Expense', 1);
$pdf->Cell(25, 10, number_format($totalExpense, 2), 1);
$pdf->Cell(70, 10, 'Total Income', 1);
$pdf->Cell(25, 10, number_format($totalIncome, 2), 1);
$pdf->Ln();

// Net Balance
$pdf->Cell(140, 10, 'Net Balance', 1);
$pdf->Cell(50, 10, number_format($totalIncome - $totalExpense, 2), 1);

// Output the generated PDF
$pdf->Output('D', 'balance_sheet.pdf');
exit();
