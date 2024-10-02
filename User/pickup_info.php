<?php
// Include the database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();

// Fetch the request details based on request_id
if (isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];

    // Updated query to join with inventory to fetch restaurant info
    $query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                     i.donor AS restaurant_username, u.phone_number AS restaurant_phone, 
                     r.requested_quantity, r.receive_method, r.receive_time, r.address
              FROM requests r
              JOIN inventory i ON r.id = i.id
              JOIN user u ON i.donor = u.username
              WHERE r.request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}

// Handle form submission for receiving method and address
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receive_method = $_POST['receive_method'];
    $receive_time = $_POST['receive_time'];
    $address = $receive_method === 'pickup' ? $_POST['address'] : 'N/A';

    // Update request in the database
    $update = $conn->prepare("UPDATE requests SET receive_method = ?, receive_time = ?, address = ? WHERE request_id = ?");
    $update->bind_param("sssi", $receive_method, $receive_time, $address, $request_id);
    $update->execute();
    header("Location: pickup.php?request_id=$request_id"); // Redirect to prevent form resubmission
    exit;
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
    <?php include 'navbar.php';?>
    <div class="container">
        <h2>Pickup Request Details</h2>

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
                    <th>Restaurant Username</th>
                    <td><?= htmlspecialchars($row['restaurant_username']) ?></td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td><?= htmlspecialchars($row['restaurant_phone']) ?></td>
                </tr>
                <tr>
                    <th>Requested Quantity</th>
                    <td><?= htmlspecialchars($row['requested_quantity']) ?></td>
                </tr>
            </table>

            <!-- Form for NGO to choose receive method -->
            <form method="POST">
                <div class="mb-3">
                    <label for="receive_method" class="form-label">Receive Method</label>
                    <select name="receive_method" id="receive_method" class="form-control" required>
                        <option value="">Choose Method</option>
                        <option value="delivery" <?= $row['receive_method'] === 'delivery' ? 'selected' : '' ?>>Delivery</option>
                        <option value="pickup" <?= $row['receive_method'] === 'pickup' ? 'selected' : '' ?>>Pickup</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="receive_time" class="form-label">Receive Time</label>
                    <input type="datetime-local" name="receive_time" id="receive_time" class="form-control" value="<?= htmlspecialchars($row['receive_time']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address (if Pickup)</label>
                    <input type="text" name="address" id="address" class="form-control" value="<?= htmlspecialchars($row['address']) ?>" <?= $row['receive_method'] === 'pickup' ? 'required' : '' ?>>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        <?php else: ?>
            <p class="alert alert-danger">Request not found.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
