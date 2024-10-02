<?php
// Include database connection at the beginning of the file
$conn = new mysqli('localhost', 'root', '', 'foodwaste');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session to retrieve the restaurant's username
session_start();
$username = $_SESSION['username']; // Ensure 'username' is correctly set in session

// Fetch requests related to the restaurant's inventory items
$request_query = "
    SELECT r.request_id, r.id, r.name, r.username, r.requested_quantity, r.receive_method, r.receive_time, r.address, u.username AS ngo_name, u.phone 
    FROM requests r
    JOIN inventory i ON r.id = i.id
    JOIN user u ON r.username = u.username
    WHERE i.donor = ?
";
$stmt = $conn->prepare($request_query);
$stmt->bind_param("s", $username);  // Bind the restaurant username to the query
$stmt->execute();
$request_result = $stmt->get_result();

// Close the database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Requests from NGOs</title>
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
        <h2>Requests from NGOs</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>NGO Name</th>
                    <th>Phone Number</th>
                    <th>Requested Quantity</th>
                    <th>Food Receive Method</th>
                    <th>Receive Time</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($request_result) {
                    if ($request_result->num_rows > 0) {
                        while ($row = $request_result->fetch_assoc()) {
                            $receive_method = htmlspecialchars($row['receive_method']);
                            $receive_time = htmlspecialchars($row['receive_time']);
                            $address = htmlspecialchars($row['address']);

                            // Check if NGO has chosen a receive method
                            if (empty($receive_method)) {
                                $receive_method = 'Pending';
                                $receive_time = 'Pending';
                                $address = 'Pending';
                            } else if ($receive_method === 'Pickup') {
                                $receive_time = 'N/A';
                                $address = 'N/A';
                            } else if ($receive_method === 'Delivery') {
                                if (empty($receive_time)) {
                                    $receive_time = 'Pending';
                                }
                                if (empty($address)) {
                                    $address = 'Pending';
                                }
                            }

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['request_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ngo_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['requested_quantity']) . "</td>";
                            echo "<td>" . $receive_method . "</td>";
                            echo "<td>" . $receive_time . "</td>";
                            echo "<td>" . $address . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        // Display message within a row if no requests found
                        echo "<tr><td colspan='9' class='text-center'>No requests found.</td></tr>";
                    }
                } else {
                    // Display error within a row if query fails
                    echo "<tr><td colspan='9' class='text-center'>Error fetching requests.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
