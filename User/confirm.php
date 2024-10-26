<?php
// Include database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();

// Fetch user info
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT username, user_type FROM user WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$username = $user['username'];
$user_type = $user['user_type'];

// If not logged in as Restaurant, restrict access
if ($user_type !== 'Restaurant') {
    echo "Access denied. Only Restaurant users can view this page.";
    exit();
}

// Fetch all requests from the requests table
$query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                i.category, i.donor AS restaurant_username, u.phone_number AS restaurant_phone, 
                r.ngo_name, r.requested_quantity, r.receive_method, r.receive_time, 
                r.address, r.delivery_completed
          FROM requests r
          JOIN inventory i ON r.id = i.id
          JOIN user u ON i.donor = u.username";
$result = $conn->query($query);

// Handle confirmation for delivery
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];

    // Check if the request is being confirmed by the NGO
    if (isset($_POST['confirm_delivery'])) {
        // Update delivery confirmation
        $update_query = "UPDATE requests SET delivery_completed = 'completed' WHERE request_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("i", $request_id);
        $update_stmt->execute();

        // Refresh the page or redirect
        header("Location: confirm.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Requests</title>
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
        .row-completed {
            background-color: #d4edda; /* Light green background for completed rows */
        }
        /* Styling for Delivery Status */
        .status-pending {
            color: #ffa500; /* Orange for Pending */
            font-weight: bold;
        }
        .status-completed {
            color: #28a745; /* Green for Completed */
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>Confirm Requests</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Inventory ID</th>
                <th>Item Name</th>
                <th>Category</th> 
                <th>Restaurant Name</th>
                <th>Phone Number</th>
                <th>Requested Quantity</th>
                <th>Receive Method</th>
                <th>Receive Time</th>
                <th>Address</th>
                <th>Delivery Status</th> <!-- Add Delivery Status column header -->
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="<?php echo ($row['delivery_completed'] === 'completed') ? 'row-completed' : ''; ?>">
                    <td><?php echo $row['request_id']; ?></td>
                    <td><?php echo $row['inventory_id']; ?></td>
                    <td><?php echo $row['item_name']; ?></td>
                    <td><?php echo $row['category']; ?></td>
                    <td><?php echo $row['restaurant_username']; ?></td>
                    <td><?php echo $row['restaurant_phone']; ?></td>
                    <td><?php echo $row['requested_quantity']; ?></td>
                    <td><?php echo $row['receive_method']; ?></td>
                    <td><?php echo $row['receive_time']; ?></td>
                    <td><?php echo $row['address']; ?></td>
                    <td>
                        <span class="<?php echo ($row['delivery_completed'] === 'completed') ? 'status-completed' : 'status-pending'; ?>">
                            <?php echo ($row['delivery_completed'] === 'completed') ? 'Completed' : 'Pending'; ?>
                        </span>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>