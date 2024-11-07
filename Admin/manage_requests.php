<?php
// Start session to access username
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$toast_message = ""; // To store the toast message

// Handle form submission for approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_id'], $_POST['action'])) {
        $request_id = $_POST['request_id'];
        $action = $_POST['action'];
        
        if ($action === 'approve') {
            $status = 'approved';
            $update_query = $conn->prepare("UPDATE requests SET status = ?, approval_date = NOW() WHERE request_id = ?");
            $update_query->bind_param("si", $status, $request_id);
        } elseif ($action === 'reject' && isset($_POST['remark'])) {
            $status = 'rejected';
            $remark = $_POST['remark'];
            $update_query = $conn->prepare("UPDATE requests SET status = ?, approval_date = NOW(), rejection_remark = ? WHERE request_id = ?");
            $update_query->bind_param("ssi", $status, $remark, $request_id);
        } else {
            $toast_message = "Please provide a remark for rejection.";
            $update_query = null;
        }

        if ($update_query) {
            if ($update_query->execute()) {
                $toast_message = "Request successfully updated.";
            } else {
                $toast_message = "Error updating request: " . $update_query->error;
            }

            $update_query->close();
        }
    } else {
        $toast_message = "Request ID or action is missing.";
    }
}

// Fetch all requests
$request_result = $conn->query("SELECT * FROM requests");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet"> <!-- Poppins font -->
    <title>Manage Requests</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif; /* Applied Poppins font */
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
            padding: 12px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr.pending {
            background-color: #fffbeb; /* Light orange for pending */
        }
        tr.approved {
            background-color: #e6ffe6; /* Light green for approved */
        }
        tr.rejected {
            background-color: #ffe6e6; /* Light red for rejected */
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .toast-container {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 9999;
        }
        input[type="text"] {
            width: 200px;
            margin-right: 10px; /* Spacing between input field and buttons */
        }
        .action-form {
            display: inline-block;
            margin-right: 15px; /* Space between approve and reject form */
        }
        .btn-success {
            margin-right: 10px; /* Space between approve button and rejection form */
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'admin_navbar.php'; ?>

    <div class="container">
        <h2>Manage Requests</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Restaurant Name</th>
                    <th>NGO Name</th>
                    <th>Requested Quantity</th>
                    <th>Status</th>
                    <th>Request Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
    <?php
    if ($request_result) {
        if ($request_result->num_rows > 0) {
            while ($row = $request_result->fetch_assoc()) {
                $row_class = "";
                if ($row['status'] === 'pending') {
                    $row_class = "pending";
                } elseif ($row['status'] === 'approved') {
                    $row_class = "approved";
                } elseif ($row['status'] === 'rejected') {
                    $row_class = "rejected";
                }

                echo "<tr class='$row_class'>";
                echo "<td>" . htmlspecialchars($row['request_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>"; // Display the category
                echo "<td>" . htmlspecialchars($row['restaurant_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['ngo_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['requested_quantity']) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>" . htmlspecialchars($row['request_date']) . "</td>";
                echo "<td>";
                
                // Aaction buttons 
                if ($row['status'] === 'pending') {
                    echo "
                        <div class='action-form'>
                            <form method='POST'>
                                <input type='hidden' name='request_id' value='" . htmlspecialchars($row['request_id']) . "' />
                                <input type='hidden' name='action' value='approve' />
                                <button type='submit' class='btn btn-success'>Approve</button>
                            </form>
                        </div>
                        <div class='action-form'>
                            <form method='POST'>
                                <input type='hidden' name='request_id' value='" . htmlspecialchars($row['request_id']) . "' />
                                <input type='hidden' name='action' value='reject' />
                                <input type='text' name='remark' placeholder='Reason for rejection' required />
                                <button type='submit' class='btn btn-danger'>Reject</button>
                            </form>
                        </div>
                    ";
                } elseif ($row['status'] === 'approved') {
                    echo "<a href='method.php?request_id=" . htmlspecialchars($row['request_id']) . "' class='btn btn-primary'>View</a>";
                } elseif ($row['status'] === 'rejected') {
                    echo "<span>Rejected: " . htmlspecialchars($row['rejection_remark']) . "</span>";
                }

                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='9' class='text-center'>No requests found.</td></tr>";
        }
    } else {
        echo "<tr><td colspan='9' class='text-center'>Error fetching requests: " . $conn->error . "</td></tr>";
    }
    ?>
</tbody>
        </table>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container">
        <div class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true" id="toastNotification">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($toast_message) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Show toast notification if there's a message
        var toastMessage = "<?= $toast_message ?>";
        if (toastMessage) {
            var toastElement = document.getElementById('toastNotification');
            var toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    </script>
</body>
</html>
