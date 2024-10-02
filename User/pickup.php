<?php
// Include the database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();
include 'navbar.php';

// Fetch approved requests for NGO
$query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, r.username AS restaurant_username, 
                 u.phone AS restaurant_phone, r.requested_quantity, r.receive_method, r.receive_time, r.address
          FROM requests r
          JOIN users u ON r.username = u.username
          WHERE r.status = 'approved' AND u.user_type = 'restaurant'";
$result = $conn->query($query);

// Handle form submission for receiving method and address
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $receive_method = $_POST['receive_method'];
    $receive_time = $_POST['receive_time'];
    $address = $receive_method === 'pickup' ? $_POST['address'] : 'N/A';

    // Update request in the database
    $update = $conn->prepare("UPDATE requests SET receive_method = ?, receive_time = ?, address = ? WHERE request_id = ?");
    $update->bind_param("sssi", $receive_method, $receive_time, $address, $request_id);
    $update->execute();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGO Pickup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Approved Requests for Pickup</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>Restaurant Username</th>
                    <th>Phone Number</th>
                    <th>Requested Quantity</th>
                    <th>Food Receive Method</th>
                    <th>Receive Time</th>
                    <th>Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['request_id']) ?></td>
                        <td><?= htmlspecialchars($row['inventory_id']) ?></td>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= htmlspecialchars($row['restaurant_username']) ?></td>
                        <td><?= htmlspecialchars($row['restaurant_phone']) ?></td>
                        <td><?= htmlspecialchars($row['requested_quantity']) ?></td>
                        <td>
                            <!-- Form for NGO to choose receive method -->
                            <form method="POST">
                                <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                <select name="receive_method" class="form-control" required>
                                    <option value="">Choose Method</option>
                                    <option value="delivery" <?= $row['receive_method'] === 'delivery' ? 'selected' : '' ?>>Delivery</option>
                                    <option value="pickup" <?= $row['receive_method'] === 'pickup' ? 'selected' : '' ?>>Pickup</option>
                                </select>
                        </td>
                        <td>
                            <!-- Input for receive time -->
                            <input type="datetime-local" name="receive_time" class="form-control" value="<?= $row['receive_time'] ?>" required>
                        </td>
                        <td>
                            <!-- Address input if method is pickup -->
                            <?php if ($row['receive_method'] === 'pickup'): ?>
                                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($row['address']) ?>" required>
                            <?php else: ?>
                                <span>N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="submit" class="btn btn-primary">Submit</button>
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
