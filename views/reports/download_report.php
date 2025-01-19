<?php
require_once('../../libraries/tcpdf/tcpdf.php');
require_once('../../config/db.php');

// Fetch project data
$project_id = $_GET['project_id'] ?? null;
if (!$project_id) {
    die("Project ID is required.");
}

$query = $conn->prepare("SELECT * FROM projects WHERE id = ?");
$query->execute([$project_id]);
$project = $query->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    die("Project not found.");
}

// Check if 'start_date' exists, otherwise use a default value
$start_date = isset($project['start_date']) ? $project['start_date'] : '2025-01-01'; // Fallback date

// Fetch expenses data
$query = $conn->prepare("SELECT category, SUM(amount) as total_amount FROM expenses WHERE project_id = ? GROUP BY category");
$query->execute([$project_id]);
$expenses = $query->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals and budget utilization
$budget = $project['budget'];
$total_expenses = array_sum(array_column($expenses, 'total_amount'));
$budget_utilization = ($total_expenses / $budget) * 100;

// Get project duration (in months)
$project_duration = $project['duration'] ?? 0; // assuming 'duration' is in months

// Calculate elapsed months
$current_date = date('Y-m-d');
$start_date = strtotime($start_date);
$current_date = strtotime($current_date);

$elapsed_months = round(($current_date - $start_date) / (60 * 60 * 24 * 30)); // Approximate months

// Calculate remaining months for completion
$remaining_budget = $budget - $total_expenses;
$monthly_spending_rate = $total_expenses / max($elapsed_months, 1); // Avoid division by zero
$remaining_months = ceil($remaining_budget / $monthly_spending_rate);

$predicted_completion_timestamp = strtotime("+$remaining_months months", $current_date);
$predicted_completion_date = date('d-m-Y', $predicted_completion_timestamp);

// Suggestion based on budget utilization
$suggestion = ($budget_utilization > 80) ? "Warning: Overspending. Consider re-evaluating the budget allocation." :
    "Good budget utilization. Continue to monitor the spending closely.";

// Check if CSV download is requested
if (isset($_GET['download_csv']) && $_GET['download_csv'] == 1) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="project_report.csv"');
    $output = fopen('php://output', 'w');

    // Add the headers for CSV
    fputcsv($output, ['Category', 'Amount (Rupees)', 'Percentage (%)']);

    // Write the expense data to CSV
    foreach ($expenses as $expense) {
        $percentage = ($expense['total_amount'] / $total_expenses) * 100;
        fputcsv($output, [$expense['category'], number_format($expense['total_amount'], 2), number_format($percentage, 2)]);
    }

    fclose($output);
    exit;
}

// Disable output buffering to avoid any warnings or errors being output before the PDF
ob_start();

// Start generating PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('TrackBuild');
$pdf->SetTitle('Project Report');
$pdf->SetSubject('Project Financial Report');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();

// Add a border
$pdf->SetDrawColor(0, 0, 0); // Black border
$pdf->Rect(5, 5, $pdf->getPageWidth() - 10, $pdf->getPageHeight() - 10);

// Add company logo centered
$logo_path = '../../assets/logo.jpg';
if (file_exists($logo_path)) {
    $pageWidth = $pdf->getPageWidth();
    $logoWidth = 30; // Further reduced logo size
    $logoX = ($pageWidth - $logoWidth) / 2;
    $pdf->Image($logo_path, $logoX, 10, $logoWidth);
}

// Add report title below the logo
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(0, 51, 102);
$pdf->Ln(35); // Adjust gap between the logo and the title
$pdf->Cell(0, 10, 'Project Financial Report', 0, 1, 'C');
$pdf->Ln(10);

// Add project details
$pdf->SetFont('helvetica', '', 12);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(50, 10, "Project Name:", 1, 0, 'L', true);
$pdf->Cell(0, 10, $project['project_name'], 1, 1, 'L');
$pdf->Cell(50, 10, "Engineer ID:", 1, 0, 'L', true);
$pdf->Cell(0, 10, $project['engineer_id'], 1, 1, 'L');
$pdf->Cell(50, 10, "Client ID:", 1, 0, 'L', true);
$pdf->Cell(0, 10, $project['client_id'], 1, 1, 'L');
$pdf->Cell(50, 10, "Description:", 1, 0, 'L', true);
$pdf->Cell(0, 10, $project['description'], 1, 1, 'L');
$pdf->Cell(50, 10, "Land Area (sq ft):", 1, 0, 'L', true);
$pdf->Cell(0, 10, $project['land_area'], 1, 1, 'L');
$pdf->Cell(50, 10, "Budget (Rupees):", 1, 0, 'L', true);
$pdf->Cell(0, 10, number_format($budget, 2), 1, 1, 'L');
$pdf->Cell(50, 10, "Total Expenses (Rupees):", 1, 0, 'L', true);
$pdf->Cell(0, 10, number_format($total_expenses, 2), 1, 1, 'L');
$pdf->Cell(50, 10, "Budget Utilization (%):", 1, 0, 'L', true);
$pdf->Cell(0, 10, number_format($budget_utilization, 2).'%', 1, 1, 'L');
$pdf->Ln(10);

// Add summary table
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 10, 'Summary Table', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);

$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(80, 10, 'Category', 1, 0, 'C', true);
$pdf->Cell(80, 10, 'Amount (Rupees)', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Percentage (%)', 1, 1, 'C', true);

foreach ($expenses as $expense) {
    $percentage = ($expense['total_amount'] / $total_expenses) * 100;
    $pdf->Cell(80, 10, $expense['category'], 1, 0, 'C');
    $pdf->Cell(80, 10, number_format($expense['total_amount'], 2), 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($percentage, 2), 1, 1, 'C');
}

$pdf->Ln(10);

// Add predictive information and suggestions
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Predicted Project Completion Date:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, $predicted_completion_date, 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Suggestions:', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 10, $suggestion, 0, 'L');
$pdf->Ln(10);

// Footer note
$pdf->SetFont('helvetica', 'I', 10);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 10, 'Generated by TrackBuild on '.date('Y-m-d'), 0, 1, 'C');

// Output PDF
ob_end_clean(); // Clean the output buffer before generating the PDF
$pdf->Output("Project_Report_{$project['project_name']}.pdf", 'I');
?>