<?php
// Include the database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();

// Fetch approved requests for NGO user
$ngo_name = $_SESSION['username'];  // Assuming username is stored in session for logged-in NGO

// Updated query to fetch restaurant's information by joining with the inventory table
$query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                 i.donor AS restaurant_username, u.phone_number AS restaurant_phone, 
                 r.requested_quantity, r.status
          FROM requests r
          JOIN inventory i ON r.id = i.id
          JOIN user u ON i.donor = u.username
          WHERE r.status = 'approved' AND r.ngo_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $ngo_name);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGO Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h2>Approved Requests</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>Restaurant Name</th>
                    <th>Phone Number</th>
                    <th>Requested Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['request_id']) ?></td>
                            <td><?= htmlspecialchars($row['inventory_id']) ?></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= htmlspecialchars($row['restaurant_username']) ?></td>
                            <td><?= htmlspecialchars($row['restaurant_phone']) ?></td>
                            <td><?= htmlspecialchars($row['requested_quantity']) ?></td>
                            <td>
                                <form method="GET" action="receive.php">
                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($row['request_id']) ?>">
                                    <button type="submit" class="btn btn-info">View</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No approved requests found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
