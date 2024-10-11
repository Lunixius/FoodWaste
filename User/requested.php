<?php
// Include database connection at the beginning of the file
$conn = new mysqli('localhost', 'root', '', 'foodwaste');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session to retrieve the username and user type
session_start();
$username = $_SESSION['username']; // Ensure 'username' is correctly set in session
$user_type = $_SESSION['user_type']; // Ensure 'user_type' is correctly set in session

// Fetch requests related to the restaurant's inventory items
$request_query = "
    SELECT r.request_id, r.id, r.name, r.ngo_name, r.requested_quantity, r.status, r.request_date, r.approval_date 
    FROM requests r
    JOIN inventory i ON r.id = i.id
    WHERE i.donor = ?
";
$stmt = $conn->prepare($request_query);
$stmt->bind_param("s", $username);  // Bind the donor username to the query
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <title>Requests for My Items</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #000;
            padding: 15px;
        }
        .container {
            margin-top: 50px;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-weight: 600;
            margin-bottom: 30px;
            color: #333;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table th, table td {
            text-align: center;
            vertical-align: middle;
            padding: 12px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f0f0f0;
            font-weight: 500;
            color: #555;
        }
        td {
            background-color: #fafafa;
            color: #444;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .highlight-orange {
            background-color: #ffa500;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .highlight-green {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .highlight-red {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .no-requests {
            font-size: 1.1rem;
            color: #666;
        }
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>Requests for My Items</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>NGO Name</th> <!-- Changed from Username to NGO Name -->
                    <th>Requested Quantity</th>
                    <th>Status</th>
                    <th>Request Date</th>
                    <th>Approval Date</th>
                    <th>Action</th> <!-- Action column -->
                </tr>
            </thead>
            <tbody>
                <?php
                if ($request_result) {
                    if ($request_result->num_rows > 0) {
                        while ($row = $request_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['request_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ngo_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['requested_quantity']) . "</td>";

                            // Status column with highlighted boxes for different statuses
                            if ($row['status'] === 'approved') {
                                echo "<td><span class='highlight-green'>Approved</span></td>";
                            } elseif ($row['status'] === 'pending') {
                                echo "<td><span class='highlight-orange'>Pending</span></td>";
                            } elseif ($row['status'] === 'rejected') {
                                echo "<td><span class='highlight-red'>Rejected</span></td>";
                            }

                            echo "<td>" . htmlspecialchars($row['request_date']) . "</td>";
                            echo "<td>" . ($row['approval_date'] ? htmlspecialchars($row['approval_date']) : 'N/A') . "</td>";

                            // Action column with different behavior for each status
                            if ($row['status'] === 'approved') {
                                echo "<td><a href='delivery.php?request_id=" . htmlspecialchars($row['request_id']) . "' class='btn btn-primary'>View</a></td>";
                            } elseif ($row['status'] === 'pending') {
                                echo "<td><span class='highlight-orange'>Waiting for approval</span></td>";
                            } elseif ($row['status'] === 'rejected') {
                                echo "<td><span class='highlight-red'>N/A</span></td>";
                            }

                            echo "</tr>";
                        }
                    } else {
                        // Display message within a row if no requests found
                        echo "<tr><td colspan='9' class='text-center no-requests'>No requests found.</td></tr>";
                    }
                } else {
                    // Display error within a row if query fails
                    echo "<tr><td colspan='9' class='text-center no-requests'>Error fetching requests.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
