<?php
require('fpdf/fpdf.php');

// Collect the data from the form and check for missing data
$pdfHeading = isset($_POST['pdfHeading']) ? $_POST['pdfHeading'] : 'Balance Sheet';

// Decode the expenses and incomes JSON data. Fallback to empty array if json_decode fails
$expenses = isset($_POST['expenses']) ? json_decode($_POST['expenses'], true) : [];
$incomes = isset($_POST['incomes']) ? json_decode($_POST['incomes'], true) : [];

$totalExpense = isset($_POST['totalExpense']) ? $_POST['totalExpense'] : '0.00';
$totalIncome = isset($_POST['totalIncome']) ? $_POST['totalIncome'] : '0.00';
$netBalance = isset($_POST['netBalance']) ? $_POST['netBalance'] : '0.00';

// Convert total amounts to float by removing commas
$totalExpense = (float)str_replace(',', '', $totalExpense);
$totalIncome = (float)str_replace(',', '', $totalIncome);
$netBalance = (float)str_replace(',', '', $netBalance);

// Make sure expenses and incomes are arrays, even if they are empty
if (!is_array($expenses)) {
    $expenses = [];
}
if (!is_array($incomes)) {
    $incomes = [];
}

// Create instance of FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Add the logo (make sure the logo path is correct)
$pdf->Image('../logo/ebasc logo.png', 85, 10, 40); // Center the logo, adjust size (40x40)
$pdf->Ln(30); // Add space after the logo

// Add the club name
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'ERATTIL BROTHER ARTS AND SPORTS CLUB', 0, 1, 'C');

// Add the PDF heading below the club name
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, $pdfHeading, 0, 1, 'C');

// Add the current date at the top-right corner
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 10, 'Date: ' . date('Y-m-d'), 0, 1, 'R');

// Add a space before the table
$pdf->Ln(10);

// Style for table heading
$pdf->SetFillColor(154, 36, 113); // Set background color to #9A2471
$pdf->SetTextColor(255, 255, 255); // Set text color to white
$pdf->SetFont('Arial', 'B', 12);

// Add Expense/Income Table Header
$pdf->Cell(95, 10, 'Expenses', 1, 0, 'C', true);  // true to enable fill color
$pdf->Cell(95, 10, 'Incomes', 1, 1, 'C', true);

// Table headings for both expense and income
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 10, 'Date', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Particulars', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Amount', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Date', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Particulars', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Amount', 1, 1, 'C', true); // End the row

$pdf->SetFont('Arial', '', 10);

// Safely use count() since we know expenses and incomes are arrays
$maxRows = max(count($expenses), count($incomes));


// Initialize totals
$calculatedTotalExpense = 0;
$calculatedTotalIncome = 0;
for ($i = 0; $i < $maxRows; $i++) {
    // Expense data
    if (isset($expenses[$i])) {
        $expenseAmount = (float)str_replace(',', '', $expenses[$i]['amount']);
        $calculatedTotalExpense += $expenseAmount;
        $pdf->Cell(30, 10, $expenses[$i]['date'], 1);
        $pdf->Cell(40, 10, $expenses[$i]['particulars'], 1);
        $pdf->Cell(25, 10, number_format($expenseAmount, 2), 1);
    } else {
        $pdf->Cell(30, 10, '', 1);
        $pdf->Cell(40, 10, '', 1);
        $pdf->Cell(25, 10, '', 1);
    }

    // Income data
    if (isset($incomes[$i])) {
        $incomeAmount = (float)str_replace(',', '', $incomes[$i]['amount']);
        $calculatedTotalIncome += $incomeAmount;
        $pdf->Cell(30, 10, $incomes[$i]['date'], 1);
        $pdf->Cell(40, 10, $incomes[$i]['particulars'], 1);
        $pdf->Cell(25, 10, number_format($incomeAmount, 2), 1);
    } else {
        $pdf->Cell(30, 10, '', 1);
        $pdf->Cell(40, 10, '', 1);
        $pdf->Cell(25, 10, '', 1);
    }

    $pdf->Ln(); // New line after each row
}


// Calculate Net Balance
$calculatedNetBalance = $calculatedTotalIncome - $calculatedTotalExpense;

// Add totals
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 10, 'Total Expense: ' . number_format($calculatedTotalExpense, 2), 0, 0, 'L');
$pdf->Cell(50, 10, 'Total Income: ' . number_format($calculatedTotalIncome, 2), 0, 0, 'L');
$pdf->Ln();
$pdf->Cell(70, 10, 'Net Balance: ' . number_format($calculatedNetBalance, 2), 0, 0, 'L');


// Add this to debug the incoming data
var_dump($expenses);
var_dump($incomes);
exit;


// Output the PDF
$pdf->Output('D', 'Balance_Sheet.pdf');  // D for download
?>
