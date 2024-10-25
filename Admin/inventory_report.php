<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
$conn = new mysqli('localhost', 'root', '', 'foodwaste');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for report
$total_donations = 0;
$total_delivered = 0;
$total_wasted = 0;
$remaining_inventory = 0;
$category_data = [];
$expiry_data = [];
$request_status = ['approved' => 0, 'pending' => 0, 'rejected' => 0];

// Handle date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$date_filter = '';

if ($start_date && $end_date) {
    $date_filter = "WHERE date_created BETWEEN '$start_date' AND '$end_date'";
}

// Fetch total donations
$query_total_donations = "SELECT SUM(quantity) AS total_donations FROM inventory $date_filter";
$result_total_donations = $conn->query($query_total_donations);
if ($result_total_donations && $row = $result_total_donations->fetch_assoc()) {
    $total_donations = $row['total_donations'];
}

// Fetch total delivered
$query_total_delivered = "SELECT SUM(requested_quantity) AS total_delivered FROM requests WHERE status = 'approved'";
$result_total_delivered = $conn->query($query_total_delivered);
if ($result_total_delivered && $row = $result_total_delivered->fetch_assoc()) {
    $total_delivered = $row['total_delivered'];
}

// Fetch total wasted (expired items not requested)
$query_total_wasted = "SELECT i.id, i.quantity, IFNULL(SUM(r.requested_quantity), 0) AS total_requested, 
                       (i.quantity - IFNULL(SUM(r.requested_quantity), 0)) AS wasted_quantity 
                       FROM inventory i 
                       LEFT JOIN requests r ON i.id = r.id AND r.status = 'approved' 
                       WHERE i.expiry_date < NOW() 
                       GROUP BY i.id 
                       HAVING wasted_quantity > 0";

$result_total_wasted = $conn->query($query_total_wasted);
$total_wasted = 0;
if ($result_total_wasted) {
    while ($row = $result_total_wasted->fetch_assoc()) {
        $total_wasted += $row['wasted_quantity'];
    }
}

// Fetch remaining inventory
$query_remaining_inventory = "SELECT SUM(quantity) AS remaining_inventory FROM inventory $date_filter";
$result_remaining_inventory = $conn->query($query_remaining_inventory);
if ($result_remaining_inventory && $row = $result_remaining_inventory->fetch_assoc()) {
    $remaining_inventory = $row['remaining_inventory'];
}

// Fetch request status
$query_request_status = "SELECT status, COUNT(*) AS count FROM requests GROUP BY status";
$result_request_status = $conn->query($query_request_status);
if ($result_request_status) {
    while ($row = $result_request_status->fetch_assoc()) {
        $request_status[$row['status']] = $row['count'];
    }
}

// Fetch category breakdown
$query_category_breakdown = "SELECT category, SUM(quantity) AS total_quantity FROM inventory GROUP BY category";
$result_category_breakdown = $conn->query($query_category_breakdown);
if ($result_category_breakdown) {
    while ($row = $result_category_breakdown->fetch_assoc()) {
        $category_data[] = $row;
    }
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
            height: 400px;
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
        .chart-container {
            margin-top: 30px;
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
            <h5>Total Wasted: <?php echo $total_wasted ? $total_wasted : 0; ?></h5>
            <h5>Remaining Inventory: <?php echo $remaining_inventory ? $remaining_inventory : 0; ?></h5>
        </div>
        <div class="col-md-6">
            <h5>Request Status:</h5>
            <ul>
                <li>Approved: <?php echo $request_status['approved']; ?></li>
                <li>Pending: <?php echo $request_status['pending']; ?></li>
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
</div>

<script>
// Data for Category Breakdown Pie Chart
const categoryData = {
    labels: <?php echo json_encode(array_column($category_data, 'category')); ?>,
    datasets: [{
        data: <?php echo json_encode(array_column($category_data, 'total_quantity')); ?>,
        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
    }]
};

// Data for Status Breakdown Bar Chart
const statusData = {
    labels: ['Approved', 'Pending', 'Rejected'],
    datasets: [{
        label: 'Requests',
        data: [
            <?php echo $request_status['approved']; ?>,
            <?php echo $request_status['pending']; ?>,
            <?php echo $request_status['rejected']; ?>
        ],
        backgroundColor: ['#4BC0C0', '#FFCE56', '#FF6384'],
    }]
};

// Render Pie Chart
const categoryPieChartCtx = document.getElementById('categoryPieChart').getContext('2d');
new Chart(categoryPieChartCtx, {
    type: 'pie',
    data: categoryData
});

// Render Bar Chart
const statusBarChartCtx = document.getElementById('statusBarChart').getContext('2d');
new Chart(statusBarChartCtx, {
    type: 'bar',
    data: statusData
});
</script>

</body>
</html>
