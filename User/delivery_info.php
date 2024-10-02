<?php
// Include the database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();

// Fetch the request details based on request_id
if (isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];

    // Updated query to join with the user table to fetch NGO info
    $query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                     r.username AS ngo_username, u.phone_number AS ngo_phone, 
                     r.requested_quantity, r.receive_time, r.address
              FROM requests r
              JOIN user u ON r.username = u.username
              WHERE r.request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Delivery Info</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php';?>
    <div class="container">
        <h2>Delivery Request Details</h2>

        <!-- Display request details if found -->
        <?php if ($row): ?>
            <table class="table table-bordered">
                <tr>
                    <th>Request ID</th>
                    <td><?= htmlspecialchars($row['request_id']) ?></td>
                </tr>
                <tr>
                    <th>Inventory ID</th>
                    <td><?= htmlspecialchars($row['inventory_id']) ?></td>
                </tr>
                <tr>
                    <th>Item Name</th>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                </tr>
                <tr>
                    <th>NGO Username</th>
                    <td><?= htmlspecialchars($row['ngo_username']) ?></td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td><?= htmlspecialchars($row['ngo_phone']) ?></td>
                </tr>
                <tr>
                    <th>Requested Quantity</th>
                    <td><?= htmlspecialchars($row['requested_quantity']) ?></td>
                </tr>
                <tr>
                    <th>Receive Time</th>
                    <td><?= htmlspecialchars($row['receive_time']) ?></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                </tr>
            </table>
        <?php else: ?>
            <p class="alert alert-danger">Request not found.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
