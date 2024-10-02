<?php
// Include the database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();

// Fetch approved requests for the restaurant
$restaurant_username = $_SESSION['username'];  // Assuming the restaurant's username is stored in the session

// Query to fetch NGO's information for approved requests
$query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                 r.username AS ngo_username, u.phone_number AS ngo_phone, 
                 r.requested_quantity, r.status
          FROM requests r
          JOIN user u ON r.username = u.username
          WHERE r.status = 'approved' AND r.donor = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $restaurant_username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Deliveries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h2>Approved Deliveries</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>NGO Username</th>
                    <th>Phone Number</th>
                    <th>Requested Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['request_id']) ?></td>
                        <td><?= htmlspecialchars($row['inventory_id']) ?></td>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= htmlspecialchars($row['ngo_username']) ?></td>
                        <td><?= htmlspecialchars($row['ngo_phone']) ?></td>
                        <td><?= htmlspecialchars($row['requested_quantity']) ?></td>
                        <td>
                            <!-- View Button that redirects to deliver_info.php -->
                            <form method="GET" action="deliver_info.php">
                                <input type="hidden" name="request_id" value="<?= htmlspecialchars($row['request_id']) ?>">
                                <button type="submit" class="btn btn-info">View</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
