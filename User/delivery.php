<?php
// Include the database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();

// Fetch approved requests for Restaurant user
$restaurant_username = $_SESSION['username'];  // Assuming restaurant username is stored in session

// Updated query to fetch NGO's information by joining with the requests and inventory tables
$query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                 u.username AS ngo_name, u.phone_number AS ngo_phone, 
                 r.requested_quantity, r.status
          FROM requests r
          JOIN inventory i ON r.id = i.id 
          JOIN user u ON r.ngo_name = u.username  -- Changed this line
          WHERE r.status = 'approved' AND i.donor = ?";
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
    <title>Delivery Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h2>Approved Delivery Requests</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>NGO Name</th>
                    <th>NGO Phone Number</th>
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
                        <td><?= htmlspecialchars($row['ngo_name']) ?></td>
                        <td><?= htmlspecialchars($row['ngo_phone']) ?></td>
                        <td><?= htmlspecialchars($row['requested_quantity']) ?></td>
                        <td>
                            <form method="GET" action="receive.php">
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
