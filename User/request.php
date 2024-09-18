<?php
// Include database connection at the beginning of the file
$conn = new mysqli('localhost', 'root', '', 'foodwaste');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session to retrieve the username
session_start();
$username = $_SESSION['username']; // Ensure 'username' is correctly set in session

// Handle form submission for making a request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'], $_POST['requested_quantity'])) {
    $item_id = $_POST['item_id'];
    $requested_quantity = $_POST['requested_quantity'];
    
    // Fetch item name
    $item_query = $conn->query("SELECT name FROM inventory WHERE id = $item_id");
    if ($item_query && $item_query->num_rows > 0) {
        $item_row = $item_query->fetch_assoc();
        $item_name = $item_row['name'];

        // Insert request into the requests table
        $stmt = $conn->prepare("INSERT INTO requests (id, name, username, requested_quantity, status, request_date) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("issi", $item_id, $item_name, $username, $requested_quantity);
        
        if ($stmt->execute()) {
            echo "Request made successfully!";
        } else {
            echo "Error making request: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Item not found.";
    }
}

// Fetch requests to display
$request_result = $conn->query("SELECT * FROM requests WHERE username = '$username'");

// Close the database connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
            border-collapse: collapse;
        }
        table th, table td {
            text-align: center;
            vertical-align: middle;
            padding: 8px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
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
                <?php
                if ($request_result) {
                    if ($request_result->num_rows > 0) {
                        while ($row = $request_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['request_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>"; // Using 'id' instead of 'inventory_id'
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>"; // 'name' should match the column in 'requests' table
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['requested_quantity']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['request_date']) . "</td>";
                            echo "<td>" . ($row['approval_date'] ? htmlspecialchars($row['approval_date']) : 'N/A') . "</td>";
                            echo "<td>" . ($row['fulfillment_date'] ? htmlspecialchars($row['fulfillment_date']) : 'N/A') . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        // Display message within a row if no requests found
                        echo "<tr><td colspan='9' class='text-center'>No requests found.</td></tr>";
                    }
                } else {
                    // Display error within a row if query fails
                    echo "<tr><td colspan='9' class='text-center'>Error fetching requests: " . $conn->error . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

