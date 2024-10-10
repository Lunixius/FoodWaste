<?php
// Include the database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();

// Fetch the request details based on request_id
if (isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];

    // Query to fetch request details along with restaurant and NGO information
    $query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                    i.donor AS restaurant_username, u.phone_number AS restaurant_phone, 
                    r.requested_quantity, r.receive_time, r.address, r.latitude, r.longitude, 
                    r.restaurant_confirmed, r.ngo_confirmed, r.admin_confirmed
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

// Handle admin confirmation of the request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_request'])) {
    // Confirm the request for the admin
    $update_query = "UPDATE requests SET admin_confirmed = 1 WHERE request_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $request_id);
    $update_stmt->execute();

    // Redirect to order.php for order details (this file will be created later)
    header("Location: order.php?request_id=$request_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Confirmation</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e9f5f5;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .status.confirmed {
            background-color: #d4edda; /* Light green */
            color: #155724; /* Dark green */
        }
        .status.pending {
            background-color: #fff3cd; /* Light orange */
            color: #856404; /* Dark orange */
        }
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container">
        <h2 class="mb-4">Request Confirmation</h2>

        <!-- Display request details -->
        <?php if (isset($row)): ?>
            <div class="mb-3">
                <h5>Item: <?php echo htmlspecialchars($row['item_name']); ?></h5>
                <p>Request ID: <?php echo htmlspecialchars($row['request_id']); ?></p>
                <p>Inventory ID: <?php echo htmlspecialchars($row['inventory_id']); ?></p>
                <p>Requested Quantity: <?php echo htmlspecialchars($row['requested_quantity']); ?></p>
                <p>Restaurant Name: <?php echo htmlspecialchars($row['restaurant_username']); ?></p>
                <p>Restaurant Phone Number: <?php echo htmlspecialchars($row['restaurant_phone']); ?></p>
                <p>Preferred Pickup Time: <?php echo htmlspecialchars($row['receive_time']); ?></p>
                <p>Pickup Address: <?php echo htmlspecialchars($row['address']); ?></p>
            </div>

            <!-- Display status bars -->
            <div class="status <?php echo $row['restaurant_confirmed'] ? 'confirmed' : 'pending'; ?>">
                <strong>Restaurant Status:</strong>
                <?php if ($row['restaurant_confirmed']): ?>
                    Confirmed.
                <?php else: ?>
                    Waiting for restaurant confirmation.
                <?php endif; ?>
            </div>

            <div class="status <?php echo $row['ngo_confirmed'] ? 'confirmed' : 'pending'; ?>">
                <strong>NGO Status:</strong>
                <?php if ($row['ngo_confirmed']): ?>
                    Confirmed.
                <?php else: ?>
                    Waiting for NGO confirmation.
                <?php endif; ?>
            </div>

            <!-- Confirmation button for admin -->
            <?php if ($row['restaurant_confirmed'] && $row['ngo_confirmed'] && !$row['admin_confirmed']): ?>
                <form method="POST">
                    <button type="submit" name="confirm_request" class="btn btn-primary">Confirm Request</button>
                </form>
            <?php else: ?>
                <p class="text-muted">The request cannot be confirmed until both the restaurant and NGO confirm their information.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Request details not found!</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
