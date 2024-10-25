<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
$mysqli = new mysqli("localhost", "root", "", "foodwaste");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Initialize variables for chart data
$totalRequests = $approvedRequests = $pendingRequests = $rejectedRequests = $fulfilledRequests = $requestedQuantity = $deliveredQuantity = 0;
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
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_requests,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_requests,
        SUM(CASE WHEN delivery_completed = 1 THEN 1 ELSE 0 END) AS fulfilled_requests,
        SUM(requested_quantity) AS requested_quantity,
        SUM(CASE WHEN delivery_completed = 1 THEN requested_quantity ELSE 0 END) AS delivered_quantity
    FROM requests
    WHERE request_date BETWEEN '$startDate' AND '$endDate'";

$result = $mysqli->query($requestQuery);
if ($result) {
    $data = $result->fetch_assoc();
    $totalRequests = $data['total_requests'];
    $approvedRequests = $data['approved_requests'];
    $pendingRequests = $data['pending_requests'];
    $rejectedRequests = $data['rejected_requests'];
    $fulfilledRequests = $data['fulfilled_requests'];
    $requestedQuantity = $data['requested_quantity'];
    $deliveredQuantity = $data['delivered_quantity'];
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

// Fetch Donor Data Breakdown (assuming 'restaurant_name' refers to donors)
$donorQuery = "
    SELECT restaurant_name, COUNT(request_id) AS fulfilled_requests 
    FROM requests 
    WHERE delivery_completed = 1 AND request_date BETWEEN '$startDate' AND '$endDate'
    GROUP BY restaurant_name";
$donorResult = $mysqli->query($donorQuery);
while ($row = $donorResult->fetch_assoc()) {
    $donorData[] = $row;
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
                ['Pending', <?= $pendingRequests ?>],
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
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 8px;
    margin-right: 10px;
    font-size: 14px;
}

form button {
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 10px 15px;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #45a049;
}

/* Summary Section */
.summary {
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
}

/* Container for the charts */
.chart-container {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    margin-top: 30px;
}

.chart-box {
    width: 45%;
    margin-bottom: 30px;
    background-color: #fff;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    padding: 20px;
}

#requestStatusChart, #ngoChart {
    width: 100%;
    height: 350px;
}

/* Table styling (optional for future use) */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

table th, table td {
    padding: 12px 15px;
    border: 1px solid #ddd;
    text-align: left;
}

table th {
    background-color: #4CAF50;
    color: white;
    font-weight: 500;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #f1f1f1;
}

/* Button styles */
button {
    padding: 10px 15px;
    background-color: #4CAF50;
    border: none;
    border-radius: 4px;
    color: white;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #45a049;
}

    </style>
</head>
<body>
    <!-- Include your navigation -->
    <?php include 'admin_navbar.php'; ?>

    <!-- Main container for the report -->
    <div class="container">
        <h1>Request Report</h1>

        <!-- Date Range Filter -->
        <form method="POST">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?= $startDate ?>">
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?= $endDate ?>">
            <button type="submit" name="filter">Filter</button>
        </form>

        <!-- Summary Section -->
        <div class="summary">
            <h2>Summary</h2>
            <p>Total Requests: <?= $totalRequests ?></p>
            <p>Approved Requests: <?= $approvedRequests ?></p>
            <p>Pending Requests: <?= $pendingRequests ?></p>
            <p>Rejected Requests: <?= $rejectedRequests ?></p>
            <p>Fulfilled Requests: <?= $fulfilledRequests ?></p>
            <p>Total Requested Quantity: <?= $requestedQuantity ?></p>
            <p>Total Delivered Quantity: <?= $deliveredQuantity ?></p>
        </div>

        <!-- Charts Section -->
        <div class="chart-container">
            <div class="chart-box">
                <div id="requestStatusChart"></div>
            </div>
            <div class="chart-box">
                <div id="ngoChart"></div>
            </div>
        </div>
    </div>
</body>

</html>
