<?php
session_start();

// Database connection parameters
$servername = "localhost";
$db_username = "root";  // Replace with your database username
$db_password = "";  // Replace with your database password
$dbname = "foodwaste";

// Create a database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];
    $requested_quantity = $_POST['requested_quantity'];

    // Insert request into the database using inventory_id
    $stmt = $conn->prepare("INSERT INTO requests (inventory_id, username, requested_quantity, status, request_date) VALUES (?, ?, ?, 'pending', NOW())");
    $username = $_SESSION['username'];  // Assuming username is stored in the session
    $stmt->bind_param("isi", $item_id, $username, $requested_quantity);

    if ($stmt->execute()) {
        // Redirect back to item.php with a success message
        header("Location: item.php?request=success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Retrieve requests with item_name by joining inventory table
$request_query = "
    SELECT r.request_id, r.inventory_id, i.name AS item_name, r.username, r.requested_quantity, r.status, r.request_date, r.approval_date, r.fulfillment_date
    FROM requests r
    JOIN inventory i ON r.inventory_id = i.id
    WHERE r.username = ?
";
$request_stmt = $conn->prepare($request_query);
$request_stmt->bind_param("s", $_SESSION['username']);
$request_stmt->execute();
$request_result = $request_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>My Requests</title>
    <style>
        body {
            font-family: 'Lato', sans-serif;
        }
        .navbar {
            background-color: #000;
            padding: 15px;
        }
        .container {
            margin-top: 50px;
        }
        table {
            width: 100%;
            margin-top: 20px;
        }
        table th, table td {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>My Requests</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>Username</th>
                    <th>Requested Quantity</th>
                    <th>Status</th>
                    <th>Request Date</th>
                    <th>Approval Date</th>
                    <th>Fulfillment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($request_result->num_rows > 0): ?>
                    <?php while ($row = $request_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['request_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['inventory_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['requested_quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                            <td><?php echo $row['approval_date'] ? htmlspecialchars($row['approval_date']) : 'N/A'; ?></td>
                            <td><?php echo $row['fulfillment_date'] ? htmlspecialchars($row['fulfillment_date']) : 'N/A'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

