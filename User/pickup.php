<?php
// Include database connection
$conn = new mysqli('localhost', 'root', '', 'foodwaste');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();
$restaurant_username = $_SESSION['username']; // Ensure username is set
$request_id = $_GET['request_id']; // Get the request ID from the URL

// Fetch request details
$request_query = "SELECT r.name, r.receive_method, r.delivery_date, r.status, i.donor
                  FROM requests r
                  JOIN inventory i ON r.id = i.id
                  WHERE r.request_id = ?";
$stmt = $conn->prepare($request_query);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

// Handle form submission to confirm delivery/pickup
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address = $_POST['address']; // Get the address provided by the restaurant
    $confirmation_status = 'approved'; // Set status to approved after confirmation

    // Update request status and address
    $update_query = "UPDATE requests SET status = ?, fulfillment_date = ?, address = ? WHERE request_id = ?";
    $fulfillment_date = date('Y-m-d'); // Current date
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $confirmation_status, $fulfillment_date, $address, $request_id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Request confirmed successfully. The NGO will be notified.</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to confirm request.</div>";
    }
    $stmt->close();
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Delivery/Pickup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Lato', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h2>Confirm Delivery/Pickup for "<?php echo htmlspecialchars($request['name']); ?>"</h2>

        <form method="post">
            <div class="mb-3">
                <label for="address" class="form-label">Provide Address for NGO Pickup:</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>

            <button type="submit" class="btn btn-primary">Confirm</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
