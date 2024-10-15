<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Fetch all requests with necessary data
$query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                i.donor AS restaurant_username, u.phone_number AS restaurant_phone, 
                r.ngo_name, r.requested_quantity, r.receive_method, r.receive_time, r.address, 
                r.delivery_completed
          FROM requests r
          JOIN inventory i ON r.id = i.id
          JOIN user u ON i.donor = u.username";

$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status</title>
    <!-- Poppins Font and Bootstrap -->
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
        table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
            border: 1px solid #ddd;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: black;
        }
        td {
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-delivered {
            background-color: #28a745; /* Green for delivered */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .status-pending {
            background-color: orange; /* Orange for pending */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<?php include 'admin_navbar.php'; ?>

<div class="container">
    <h2>Order Status</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Inventory ID</th>
                <th>Item Name</th>
                <th>Restaurant Name</th>
                <th>Phone Number</th>
                <th>NGO Name</th>
                <th>Requested Quantity</th>
                <th>Receive Method</th>
                <th>Receive Time</th>
                <th>Address</th>
                <th>Delivery Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['request_id']; ?></td>
                    <td><?php echo $row['inventory_id']; ?></td>
                    <td><?php echo $row['item_name']; ?></td>
                    <td><?php echo $row['restaurant_username']; ?></td>
                    <td><?php echo $row['restaurant_phone']; ?></td>
                    <td><?php echo $row['ngo_name']; ?></td>
                    <td><?php echo $row['requested_quantity']; ?></td>
                    <td><?php echo $row['receive_method']; ?></td>
                    <td><?php echo $row['receive_time']; ?></td>
                    <td><?php echo $row['address']; ?></td>
                    <td>
                        <!-- Display delivery status -->
                        <?php if ($row['delivery_completed'] == 'completed'): ?>
                            <span class="status-delivered">Delivered</span>
                        <?php else: ?>
                            <span class="status-pending">Pending</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
