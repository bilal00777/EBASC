<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

include '../config/config.php';
require('fpdf/fpdf.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: collection_list.php?error=Invalid collection ID');
    exit();
}

$collection_id = $_GET['id'];

// Fetch collection information
$stmt = $pdo->prepare("SELECT * FROM collections WHERE id = :id");
$stmt->execute(['id' => $collection_id]);
$collection = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$collection) {
    header('Location: collection_list.php?error=Collection not found');
    exit();
}

// Fetch collection members
$memberStmt = $pdo->prepare("SELECT id, member_name, status, paid_amount FROM collection_members WHERE collection_id = :collection_id");
$memberStmt->execute(['collection_id' => $collection_id]);
$members = $memberStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total paid amount
$totalCollected = 0;
foreach ($members as $member) {
    $totalCollected += $member['paid_amount'];
}

// Define PDF class
class PDF extends FPDF {
    protected $customHeading;
    
    function __construct($heading) {
        parent::__construct();
        $this->customHeading = $heading;
    }
    
    function Header() {
        // Add the logo at the center (adjusted to avoid overlap)
        $this->Image('../logo/ebasc logo.png', 80, 10, 50, 0, 'PNG'); // Adjust as per logo size and center it
        $this->Ln(40); // Space below the logo (to avoid overlapping with the headings)

        // Add Heading: ERATTIL BROTHERS ARTS AND SPORTS CLUB (centered at the top)
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 40, 'ERATTIL BROTHERS ARTS AND SPORTS CLUB', 0, 1, 'C');
        $this->Ln(0); // Space after club name

        // Add Sub-heading (centered)
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 1, $this->customHeading, 0, 1, 'C');
        $this->Ln(0); // Extra line break after heading

        // Add Current Date (right-top)
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, 'Date: ' . date('Y-m-d'), 0, 0, 'R');
        $this->Ln(15); // Space between date and table
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Create PDF instance and add page
$pdf = new PDF($collection['heading']);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Set table header background color and text color
$pdf->SetFillColor(129, 27, 132); // Purple background (#811B84)
$pdf->SetTextColor(255, 255, 255); // White text

// Add table headers with background fill
$pdf->Cell(30, 10, 'Sl No', 1, 0, 'C', true);
$pdf->Cell(80, 10, 'Member Name', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Paid Amount', 1, 0, 'C', true);
$pdf->Ln();

// Reset text color for table rows
$pdf->SetTextColor(0, 0, 0); // Black text

// Display collection members in the PDF
$slNo = 1;
foreach ($members as $member) {
    $pdf->Cell(30, 10, $slNo++, 1);
    $pdf->Cell(80, 10, $member['member_name'], 1);
    $pdf->Cell(40, 10, ucfirst($member['status']), 1);
    $pdf->Cell(40, 10, number_format($member['paid_amount'], 2), 1);
    $pdf->Ln();
}

// Display total collected amount at the end of the table
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(150, 10, 'Total Collected', 1);
$pdf->Cell(40, 10, number_format($totalCollected, 2), 1);
$pdf->Ln();

$pdf->Output('I', 'Collection_' . $collection['heading'] . '_Details.pdf');
exit();
