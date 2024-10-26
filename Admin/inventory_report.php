<?php

require_once __DIR__ . '/libs/fpdf.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'foodwaste');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$total_donations = 0;
$total_delivered = 0;
$remaining_inventory = 0;
$category_data = [];
$request_status = ['approved' => 0, 'rejected' => 0];

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$date_filter = '';

if ($start_date && $end_date) {
    $date_filter = "WHERE date_created BETWEEN '$start_date' AND '$end_date'";
}

$query_total_donations = "SELECT SUM(quantity) AS total_donations FROM inventory";
$result_total_donations = $conn->query($query_total_donations);
if ($result_total_donations && $row = $result_total_donations->fetch_assoc()) {
    $total_donations = $row['total_donations'];
}

$query_total_delivered = "SELECT SUM(requested_quantity) AS total_delivered FROM requests WHERE status = 'approved'";
$result_total_delivered = $conn->query($query_total_delivered);
if ($result_total_delivered && $row = $result_total_delivered->fetch_assoc()) {
    $total_delivered = $row['total_delivered'];
}

$remaining_inventory = $total_donations - $total_delivered;

$query_request_status = "SELECT status, COUNT(*) AS count FROM requests WHERE status IN ('approved', 'rejected') GROUP BY status";
$result_request_status = $conn->query($query_request_status);
if ($result_request_status) {
    while ($row = $result_request_status->fetch_assoc()) {
        $request_status[$row['status']] = $row['count'];
    }
}

$query_category_breakdown = "SELECT category, SUM(quantity) AS total_quantity FROM inventory GROUP BY category";
$result_category_breakdown = $conn->query($query_category_breakdown);
if ($result_category_breakdown) {
    while ($row = $result_category_breakdown->fetch_assoc()) {
        $category_data[] = $row;
    }
}

if (isset($_POST['download_pdf'])) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Food Waste Report - Inventory', 0, 1, 'C');
    $reportTitle = $start_date && $end_date ? "$start_date - $end_date" : "Food Waste Report";
    $pdf->Cell(0, 10, $reportTitle, 0, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Report Summary', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);

    // Display date range
    if ($start_date && $end_date) {
        $pdf->Cell(0, 10, 'Date Range: ' . $start_date . ' to ' . $end_date, 0, 1, 'L');
    }

    $pdf->Cell(0, 10, 'Total Donations: ' . ($total_donations ? $total_donations : 0), 0, 1);
    $pdf->Cell(0, 10, 'Total Delivered: ' . ($total_delivered ? $total_delivered : 0), 0, 1);
    $pdf->Cell(0, 10, 'Remaining Inventory: ' . ($remaining_inventory ? $remaining_inventory : 0), 0, 1);

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Request Status Breakdown', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 10, 'Approved: ' . $request_status['approved'], 0, 1);
    $pdf->Cell(50, 10, 'Rejected: ' . $request_status['rejected'], 0, 1);

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Category Breakdown', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    foreach ($category_data as $category) {
        $pdf->Cell(50, 10, $category['category'] . ': ' . $category['total_quantity'], 0, 1);
    }

    $pdf->Output('D', 'FoodWasteReport.pdf');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Waste Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e9ecef;
            color: #333;
        }
        .container {
            margin-top: 50px;
            background: #fff;
            padding: 30px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .chart-container {
            position: relative;
            width: 100%; /* Full width */
            height: 400px; /* Set a fixed height */
        }
        h2 {
            color: #007bff;
            text-align: center;
            margin-bottom: 30px;
        }
        h5 {
            font-size: 18px;
            color: #495057;
            margin-bottom: 15px;
        }
        form label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>

<?php include 'admin_navbar.php'; ?>

<div class="container">
    <h2>Food Waste Report</h2>

    <!-- Date range filter -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date:</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date:</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-2 mt-4">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Report Summary -->
    <div class="row">
        <div class="col-md-6">
            <h5>Total Donations: <?php echo $total_donations ? $total_donations : 0; ?></h5>
            <h5>Total Delivered: <?php echo $total_delivered ? $total_delivered : 0; ?></h5>
            <h5>Remaining Inventory: <?php echo $remaining_inventory ? $remaining_inventory : 0; ?></h5>
        </div>
        <div class="col-md-6">
            <h5>Request Status:</h5>
            <ul>
                <li>Approved: <?php echo $request_status['approved']; ?></li>
                <li>Rejected: <?php echo $request_status['rejected']; ?></li>
            </ul>
        </div>
    </div>

    <!-- Category Breakdown Pie Chart -->
    <div class="chart-container">
        <canvas id="categoryPieChart"></canvas>
    </div>

    <!-- Status Breakdown Bar Chart -->
    <div class="chart-container">
        <canvas id="statusBarChart"></canvas>
    </div>

    <!-- Download PDF Button -->
    <div class="text-center mt-4">
        <form method="POST">
            <button type="submit" name="download_pdf" class="btn btn-primary btn-lg">Download PDF</button>
        </form>
    </div>

</div>

<script>
// Data for Category Breakdown Pie Chart
const categoryData = {
    labels: <?php echo json_encode(array_column($category_data, 'category')); ?>,
    datasets: [{
        label: 'Inventory by Category',
        data: <?php echo json_encode(array_column($category_data, 'total_quantity')); ?>,
        backgroundColor: ['#007bff', '#dc3545', '#ffc107', '#28a745', '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14'],
        borderWidth: 1
    }]
};

// Options for Pie Chart
const categoryOptions = {
    responsive: true,
    maintainAspectRatio: false, // Allow the chart to resize
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Inventory by Category'
        }
    }
};

// Data for Request Status Bar Chart
const statusData = {
    labels: ['Approved', 'Rejected'],
    datasets: [{
        label: 'Requests Status',
        data: [<?php echo $request_status['approved']; ?>, <?php echo $request_status['rejected']; ?>],
        backgroundColor: ['#28a745', '#dc3545'],
        borderWidth: 1
    }]
};

// Options for Bar Chart
const statusOptions = {
    responsive: true,
    maintainAspectRatio: false, // Allow the chart to resize
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Request Status Breakdown'
        }
    }
};

// Create Pie Chart
const categoryPieChart = new Chart(
    document.getElementById('categoryPieChart'),
    {
        type: 'pie',
        data: categoryData,
        options: categoryOptions
    }
);

// Create Bar Chart
const statusBarChart = new Chart(
    document.getElementById('statusBarChart'),
    {
        type: 'bar',
        data: statusData,
        options: statusOptions
    }
);

// Function to capture the charts and send them to the server
function captureCharts() {
    const pieChartCanvas = document.getElementById('categoryPieChart');
    const barChartCanvas = document.getElementById('statusBarChart');

    // Convert charts to Data URL
    const pieChartImage = pieChartCanvas.toDataURL('image/png');
    const barChartImage = barChartCanvas.toDataURL('image/png');

    // Set images in a hidden form to send to server
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="chart_images" value='${JSON.stringify([pieChartImage, barChartImage])}'>
    `;
    
    document.body.appendChild(form);
    form.submit();
}

// Call the function when the PDF button is clicked
document.querySelector('button[name="download_pdf"]').addEventListener('click', captureCharts);

</script>

</body>
</html>

