<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/libs/fpdf.php';
$mysqli = new mysqli("localhost", "root", "", "foodwaste");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Initialize variables for chart data
$totalRequests = $approvedRequests = $pendingRequests = $rejectedRequests = $fulfilledRequests = $requestedQuantity = $totalDeliveredQuantity = 0;
$ngoData = $donorData = [];

// Set default date range (last 30 days)
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d');

// Check if form was submitted with a specific date range
if (isset($_POST['filter'])) {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
}

// Fetch total requests, approved, pending, rejected, and fulfilled requests
$requestQuery = "
    SELECT 
        COUNT(*) AS total_requests,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_requests,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_requests,
        SUM(requested_quantity) AS requested_quantity,
        SUM(CASE WHEN delivery_completed = 'completed' THEN requested_quantity ELSE 0 END) AS total_delivered_quantity
    FROM requests
    WHERE request_date BETWEEN '$startDate' AND '$endDate'";

$result = $mysqli->query($requestQuery);
if ($result) {
    $data = $result->fetch_assoc();
    $totalRequests = $data['total_requests'];
    $approvedRequests = $data['approved_requests'];
    $rejectedRequests = $data['rejected_requests'];
    $requestedQuantity = $data['requested_quantity'];
    $totalDeliveredQuantity = $data['total_delivered_quantity'];
}

// Fetch NGO Data Breakdown
$ngoQuery = "
    SELECT ngo_name, COUNT(request_id) AS total_requests, 
           SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_requests
    FROM requests
    WHERE request_date BETWEEN '$startDate' AND '$endDate'
    GROUP BY ngo_name";
$ngoResult = $mysqli->query($ngoQuery);
while ($row = $ngoResult->fetch_assoc()) {
    $ngoData[] = $row;
}

// Fetch Donor Data Breakdown
$donorQuery = "
    SELECT restaurant_name, COUNT(request_id) AS fulfilled_requests 
    FROM requests 
    WHERE delivery_completed = 1 AND request_date BETWEEN '$startDate' AND '$endDate'
    GROUP BY restaurant_name";
$donorResult = $mysqli->query($donorQuery);
while ($row = $donorResult->fetch_assoc()) {
    $donorData[] = $row;
}

// Handle PDF Download
if (isset($_POST['download_pdf'])) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Title
    $pdf->Cell(0, 10, 'Food Waste Request Report - Request', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);

    // Date range display
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Date Range: ' . htmlspecialchars($startDate) . ' to ' . htmlspecialchars($endDate), 0, 1, 'C');
    $pdf->Ln(5);

    // Report Summary
    $pdf->Cell(50, 10, 'Total Requests:', 0, 0);
    $pdf->Cell(50, 10, $totalRequests, 0, 1);
    $pdf->Cell(50, 10, 'Approved Requests:', 0, 0);
    $pdf->Cell(50, 10, $approvedRequests, 0, 1);
    $pdf->Cell(50, 10, 'Rejected Requests:', 0, 0);
    $pdf->Cell(50, 10, $rejectedRequests, 0, 1);
    $pdf->Cell(50, 10, 'Total Requested Quantity:', 0, 0);
    $pdf->Cell(50, 10, $requestedQuantity, 0, 1);
    $pdf->Cell(50, 10, 'Delivered Quantity:', 0, 0);
    $pdf->Cell(50, 10, $totalDeliveredQuantity, 0, 1);

    // NGO Data Breakdown
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'NGO Request Breakdown', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    foreach ($ngoData as $ngo) {
        $pdf->Cell(50, 10, 'NGO Name: ' . $ngo['ngo_name'], 0, 1);
        $pdf->Cell(50, 10, 'Total Requests: ' . $ngo['total_requests'], 0, 1);
        $pdf->Cell(50, 10, 'Approved Requests: ' . $ngo['approved_requests'], 0, 1);
        $pdf->Ln(5);
    }

    // Output the PDF
    $pdf->Output('D', 'FoodWasteRequestReport.pdf');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Report</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {packages: ['corechart']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            // Draw the pie chart for request status
            var requestStatusData = google.visualization.arrayToDataTable([
                ['Status', 'Number of Requests'],
                ['Approved', <?= $approvedRequests ?>],
                ['Rejected', <?= $rejectedRequests ?>],
                ['Fulfilled', <?= $fulfilledRequests ?>]
            ]);

            var requestStatusOptions = {
                title: 'Request Status Breakdown',
                pieHole: 0.4,
                width: 400,
                height: 300
            };
            var requestStatusChart = new google.visualization.PieChart(document.getElementById('requestStatusChart'));
            requestStatusChart.draw(requestStatusData, requestStatusOptions);

            // Draw the bar chart for NGO request breakdown
            var ngoData = google.visualization.arrayToDataTable([
                ['NGO', 'Total Requests', 'Approved Requests'],
                <?php foreach ($ngoData as $ngo) {
                    echo "['{$ngo['ngo_name']}', {$ngo['total_requests']}, {$ngo['approved_requests']}],";
                } ?>
            ]);

            var ngoOptions = {
                title: 'NGO Request Breakdown',
                hAxis: {title: 'NGO'},
                vAxis: {title: 'Requests'},
                width: 400,
                height: 300,
                legend: {position: 'top'}
            };
            var ngoChart = new google.visualization.BarChart(document.getElementById('ngoChart'));
            ngoChart.draw(ngoData, ngoOptions);
        }
    </script>
    <style>
        /* Import Poppins font */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

/* Apply Poppins font globally */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f5f5f5;
    color: #333;
}

/* General container styling for the report page */
.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Page header */
h1 {
    font-weight: 600;
    text-align: center;
    margin-bottom: 40px;
    color: #4CAF50;
}

/* Section headings */
h2 {
    font-weight: 500;
    color: #333;
    margin-bottom: 15px;
}

/* Paragraphs for summary data */
p {
    font-size: 16px;
    margin-bottom: 8px;
}

/* Date range filter form */
form {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
}

form label {
    font-weight: 500;
    margin-right: 10px;
}

form input[type="date"] {
    padding: 10px;
    margin: 0 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

form button {
    padding: 10px 15px;
    border: none;
    background-color: #4CAF50;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}

form button:hover {
    background-color: #45a049;
}

/* Chart container */
.chart-container {
    display: flex;
    justify-content: space-between;
    margin: 20px 0;
}

.chart {
    width: 48%;
}
    </style>
</head>
<body>
    <!-- Include your navigation -->
    <?php include 'admin_navbar.php'; ?>

    <!-- Main container for the report -->
    <div class="container">
    <h1>Request Report</h1>

    <!-- Date filter form -->
    <form method="POST">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" required>
        <button type="submit" name="filter">Filter</button>
    </form>

    <!-- Display report summary -->
    <h2>Summary</h2>
    <p>Total Requests: <?= $totalRequests ?></p>
    <p>Approved Requests: <?= $approvedRequests ?></p>
    <p>Rejected Requests: <?= $rejectedRequests ?></p>
    <p>Total Requested Quantity: <?= $requestedQuantity ?></p>
    <p>Delivered Quantity: <?= $totalDeliveredQuantity ?></p>

        <!-- Charts -->
        <div class="chart-container">
            <div id="requestStatusChart" class="chart"></div>
            <div id="ngoChart" class="chart"></div>
        </div>

        <!-- PDF Download Button -->
        <form method="POST">
            <button type="submit" name="download_pdf">Download PDF Report</button>
        </form>
    </div>
</body>
</html>
