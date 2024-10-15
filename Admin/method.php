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
                    r.requested_quantity, r.receive_time, r.address
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

// Handle the redirection to view orders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_orders'])) {
    // Redirect to order.php for viewing order details
    header("Location: order.php?request_id=$request_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details</title>
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
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <div class="container">
        <h2 class="mb-4">Request Details</h2>

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

            <!-- View Orders button -->
            <form method="POST">
                <button type="submit" name="view_orders" class="btn btn-primary">View Orders</button>
            </form>

        <?php else: ?>
            <p>Request details not found!</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
