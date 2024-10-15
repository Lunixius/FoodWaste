<?php
// Include database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();

// Ensure the user's role is defined
$role = $_SESSION['role'] ?? null;

// Fetch all requests from the requests table
$query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                i.donor AS restaurant_username, u.phone_number AS restaurant_phone, 
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
        .btn-confirm {
            background-color: #28a745; /* Green button for "Complete Delivery" */
            border-color: #28a745;
            color: white;
        }
        .btn-confirm:disabled {
            background-color: #6c757d; /* Disabled grey color */
            border-color: #6c757d;
        }
        .row-completed {
            background-color: #d4edda; /* Light green background for completed rows */
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
                <th>Restaurant Name</th>
                <th>Phone Number</th>
                <th>Requested Quantity</th>
                <th>Receive Method</th>
                <th>Receive Time</th>
                <th>Address</th>
                <th>Confirm Delivery</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): 
                // Check if fields are empty
                $is_disabled = empty($row['receive_method']) || empty($row['receive_time']) || empty($row['address']);
            ?>
                <tr class="<?php echo ($row['delivery_completed'] === 'completed') ? 'row-completed' : ''; ?>">
                    <td><?php echo $row['request_id']; ?></td>
                    <td><?php echo $row['inventory_id']; ?></td>
                    <td><?php echo $row['item_name']; ?></td>
                    <td><?php echo $row['restaurant_username']; ?></td>
                    <td><?php echo $row['restaurant_phone']; ?></td>
                    <td><?php echo $row['requested_quantity']; ?></td>
                    <td><?php echo $row['receive_method']; ?></td>
                    <td><?php echo $row['receive_time']; ?></td>
                    <td><?php echo $row['address']; ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                            <button type="submit" name="confirm_delivery" class="btn btn-confirm"
                                <?php if ($row['delivery_completed'] === 'completed' || $is_disabled): ?>disabled<?php endif; ?>>
                                Complete Delivery
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
