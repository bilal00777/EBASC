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
$pdfHeading = isset($_GET['pdf_heading']) ? $_GET['pdf_heading'] : 'Balance Sheet'; // Use default heading if not set

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

// Get current date
$currentDate = date('Y-m-d');

// PDF generation process
class PDF extends FPDF
{
    protected $customHeading;

    // Constructor to receive heading
    function __construct($heading)
    {
        parent::__construct();
        $this->customHeading = $heading;
    }

    // Page header
    function Header()
    {
        // Add the logo at the center (adjusted to avoid overlap)
        $this->Image('../logo/ebasc logo.png', 80, 10, 50, 0, 'PNG'); // Adjust as per logo size and center it
        $this->Ln(40); // Space below the logo (to avoid overlapping with the headings)

        // Add Heading: ERATTIL BROTHERS ARTS AND SPORTS CLUB (centered at the top)
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 40, 'ERATTIL BROTHERS ARTS AND SPORTS CLUB', 0, 1, 'C');
        $this->Ln(0); // Space after club name

        // Add Sub-heading (centered)
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0,1, $this->customHeading, 0, 1, 'C');
        $this->Ln(0); // Extra line break after heading

        // Add Current Date (right-top)
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, 'Date: ' . date('Y-m-d'), 0, 0, 'R');
        $this->Ln(15); // Space between date and table
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Function to set styled header with background color and white text
    function StyledTableHeader()
    {
        // Background color for the headers (purple)
        $this->SetFillColor(129, 27, 132); // #811B84
        $this->SetTextColor(255, 255, 255); // White

        // Header cells
        $this->Cell(95, 10, 'Expense', 1, 0, 'C', true);
        $this->Cell(95, 10, 'Income', 1, 1, 'C', true);

        // Reset colors for the rest of the table
        $this->SetTextColor(0, 0, 0); // Black text for table content
    }
}

// Create instance of PDF class with the custom heading
$pdf = new PDF($pdfHeading);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Add styled table headers
$pdf->StyledTableHeader();

// Add second row of table headers
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

// Generate the output file name based on the heading and save the file
$outputFileName = str_replace(' ', '_', $pdfHeading) . '-ebasc.pdf';
// For Preview: Display PDF in browser
$pdf->Output('I', $outputFileName);  // Use 'I' for inline display in browser

// For Download: Uncomment this if you want to force download
// $pdf->Output('D', $outputFileName); // Use 'D' for download


exit();
