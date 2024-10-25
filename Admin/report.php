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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }
        .container {
            margin-top: 30px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .card {
            margin-bottom: 20px;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn {
            padding: 15px 30px;
            font-size: 18px;
        }
        .row > div {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<?php include 'admin_navbar.php'; ?>

<div class="container">
    <h2>Generate Reports</h2>
    
    <div class="row justify-content-center">
        <!-- Inventory Report Button -->
        <div class="col-md-4">
            <div class="card">
                <h5>Inventory Report</h5>
                <a href="inventory_report.php" class="btn btn-primary">Go to Inventory Report</a>
            </div>
        </div>

        <!-- Request Report Button -->
        <div class="col-md-4">
            <div class="card">
                <h5>Request Report</h5>
                <a href="request_report.php" class="btn btn-primary">Go to Request Report</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
