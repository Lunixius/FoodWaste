<?php
// Include database connection at the beginning of the file
$conn = new mysqli('localhost', 'root', '', 'foodwaste');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session to retrieve the NGO's username
session_start();
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}
$ngo_username = $_SESSION['username'];

// Initialize flags to track request status
$request_success = false;
$request_message = '';

// Handle form submission for making a request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'], $_POST['requested_quantity'])) {
    $item_id = $_POST['item_id'];
    $requested_quantity = $_POST['requested_quantity'];

    // Use prepared statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT name, donor FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $item_query = $stmt->get_result();

    if ($item_query && $item_query->num_rows > 0) {
        $item_row = $item_query->fetch_assoc();
        $item_name = $item_row['name'];
        $restaurant_username = $item_row['donor']; // Donor is the restaurant

        // Insert request into the requests table using a prepared statement
        $stmt = $conn->prepare("INSERT INTO requests (id, name, restaurant_name, ngo_name, requested_quantity, status, request_date) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("isssi", $item_id, $item_name, $restaurant_username, $ngo_username, $requested_quantity);

        if ($stmt->execute()) {
            $request_success = true; // Set flag to true if request is successful
            $request_message = 'Request made successfully!';
            // Redirect to the same page to prevent form resubmission
            header("Location: request.php?success=1");
            exit();
        } else {
            $request_message = "Error making request: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $request_message = "Item not found.";
    }
}

// Fetch requests to display along with restaurant (donor) information
$request_result = $conn->query("
    SELECT r.request_id, r.id, r.name, r.restaurant_name, r.ngo_name, r.requested_quantity, r.status, r.request_date, r.approval_date, r.rejection_remark 
    FROM requests r
    WHERE r.ngo_name = '$ngo_username'
");

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>My Requests</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .navbar {
            background-color: #000;
            padding: 15px;
        }
        .container {
            margin-top: 50px;
        }
        h2 {
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        table th, table td {
            text-align: center;
            vertical-align: middle;
            padding: 16px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: 600;
            font-size: 16px;
        }
        td {
            font-size: 14px;
            color: #555;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-approved {
            background-color: #28a745;
            color: #fff;
        }
        .badge-rejected {
            background-color: #dc3545;
            color: #fff;
        }
        .alert-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            min-width: 250px;
            z-index: 1050;
            display: none;
        }
        .fade {
            opacity: 0;
            transition: opacity 1s ease;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>My Requests</h2>

        <?php if ($request_success): ?>
            <div class="alert alert-success alert-popup" role="alert" id="notification">
                <?php echo $request_message; ?>
            </div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>Restaurant Name</th>
                    <th>Requested Quantity</th>
                    <th>Status</th>
                    <th>Request Date</th>
                    <th>Approval Date</th>
                    <th>Remark</th> <!-- Remark column -->
                    <th>Action</th> 
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
                            echo "<td>" . htmlspecialchars($row['restaurant_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['requested_quantity']) . "</td>";
                            
                            // Status with color-coded badges
                            if ($row['status'] === 'pending') {
                                echo "<td><span class='badge badge-pending'>Pending</span></td>";
                            } elseif ($row['status'] === 'approved') {
                                echo "<td><span class='badge badge-approved'>Approved</span></td>";
                            } else {
                                echo "<td><span class='badge badge-rejected'>Rejected</span></td>";
                            }

                            echo "<td>" . htmlspecialchars($row['request_date']) . "</td>";
                            echo "<td>" . ($row['approval_date'] ? htmlspecialchars($row['approval_date']) : 'N/A') . "</td>";
                            echo "<td>" . ($row['rejection_remark'] ? htmlspecialchars($row['rejection_remark']) : 'N/A') . "</td>"; // Display rejection remark
                            
                            // Action column logic with badges
                            echo "<td>";
                            if ($row['status'] === 'approved') {
                                echo "<a href='pickup.php?request_id=" . $row['request_id'] . "' class='btn btn-primary'>View</a>";
                            } elseif ($row['status'] === 'rejected') {
                                echo "<span class='badge badge-rejected'>N/A</span>";
                            } else {
                                echo "<span class='badge badge-pending'>Waiting for approval</span>";
                            }
                            echo "</td>";

                            echo "</tr>";
                        }
                    } else {
                        // Display message within a row if no requests found
                        echo "<tr><td colspan='10' class='text-center'>No requests found.</td></tr>";
                    }
                } else {
                    // Display error within a row if query fails
                    echo "<tr><td colspan='10' class='text-center'>Error fetching requests: " . $conn->error . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Automatically show and hide alert notification
        const notification = document.getElementById('notification');
        if (notification) {
            notification.style.display = 'block';
            setTimeout(() => {
                notification.classList.add('fade');
            }, 3000); // Display for 3 seconds then fade out
        }
    </script>
</body>
</html>
