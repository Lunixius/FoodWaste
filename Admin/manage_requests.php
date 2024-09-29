<?php
// Include database connection at the beginning of the file
$conn = new mysqli('localhost', 'root', '', 'foodwaste');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session to retrieve the username and user type
session_start();

// Fetch all pending requests from the database
$request_result = $conn->query("SELECT * FROM requests WHERE status = 'pending'");

// Handle Approve and Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];

    if (isset($_POST['approve'])) {
        // Set status to 'approved' and approval date to current date
        $status = 'approved';
        $approval_date = date('Y-m-d H:i:s'); // Get current date and time

        // Update the request status and set the approval date
        $update_query = $conn->prepare("UPDATE requests SET status = ?, approval_date = ? WHERE request_id = ?");
        $update_query->bind_param("ssi", $status, $approval_date, $request_id);
    } elseif (isset($_POST['reject'])) {
        // Set status to 'rejected' and clear approval date
        $status = 'rejected';
        $approval_date = NULL;

        // Update the request status and set approval date to NULL
        $update_query = $conn->prepare("UPDATE requests SET status = ?, approval_date = ? WHERE request_id = ?");
        $update_query->bind_param("ssi", $status, $approval_date, $request_id);
    }

    // Execute the query
    $update_query->execute();

    // Reload the page to reflect changes
    header("Location: manage_requests.php");
    exit();
}

// Close the database connection at the end
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Manage Requests</title>
    <style>
        body {
            font-family: 'Lato', sans-serif;
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
    <?php include 'admin_navbar.php'; ?> <!-- Ensure the correct navbar file is included -->

    <div class="container">
        <h2>Manage Requests</h2>
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($request_result && $request_result->num_rows > 0) {
                    // Loop through each row of pending requests
                    while ($row = $request_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['request_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['requested_quantity']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['request_date']) . "</td>";
                        echo "<td>" . ($row['approval_date'] ? htmlspecialchars($row['approval_date']) : 'N/A') . "</td>";
                        echo '<td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="' . htmlspecialchars($row['request_id']) . '">
                                    <button type="submit" name="approve" class="btn btn-success">Approve</button>
                                    <button type="submit" name="reject" class="btn btn-danger">Reject</button>
                                </form>
                              </td>';
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No pending requests found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
