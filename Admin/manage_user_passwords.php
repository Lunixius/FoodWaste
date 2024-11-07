<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection parameters
$servername = "localhost";
$db_username = "root";  
$db_password = "";  
$dbname = "foodwaste";

// Create a database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$notification = "";  // Variable for notification message

// Approve, deny, or delete password change requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action == "approve") {
        // Update the request status to approved
        $stmt = $conn->prepare("UPDATE password_change_requests SET status = 'approved' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
    
        // Retrieve the user ID and new password from the password_change_requests table
        $stmt = $conn->prepare("SELECT user_id, new_password FROM password_change_requests WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->bind_result($user_id, $new_password);
        $stmt->fetch();
        $stmt->close();
    
        // Update the password in the user table
        $stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $user_id);
        $stmt->execute();
        $stmt->close();
    
        // Optionally, delete the request after approval
        // Removed this to keep the request in history

        $notification = "Request approved. The password has been updated successfully.";
    } elseif ($action == "deny") {
        // Update the request status to denied
        $stmt = $conn->prepare("UPDATE password_change_requests SET status = 'denied' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        $notification = "Request denied.";
    } elseif ($action == "delete") {
        // Delete the request
        $stmt = $conn->prepare("DELETE FROM password_change_requests WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        $notification = "Request deleted successfully.";
    }

    // Redirect to the same page to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF'] . "?notification=" . urlencode($notification));
    exit();
}

// Retrieve the notification message from the URL, if present
if (isset($_GET['notification'])) {
    $notification = htmlspecialchars($_GET['notification']);
}

// Fetch all password change requests with username, email, and request date
$request_query = $conn->prepare("
    SELECT r.request_id, u.username, u.email, r.status, r.request_date
    FROM password_change_requests AS r
    JOIN user AS u ON r.user_id = u.id
");
$request_query->execute();
$request_result = $request_query->get_result();

$request_query->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage User Passwords</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .navbar {
            width: 100%;
            background-color: #333;
            color: white;
            display: flex;
            align-items: center;
            padding: 10px 20px;
            box-sizing: border-box;
            justify-content: space-between;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
        }

        .navbar a:hover {
            background-color: #575757;
            border-radius: 4px;
        }

        .navbar .logo {
            font-size: 1.2em;
            font-weight: bold;
        }

        h1 {
            margin-top: 30px;
            color: #333;
        }

        .table-container {
            width: 90%;
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table-container thead th {
            position: sticky;
            top: 0;
            background-color: #4CAF50;
            color: white;
            z-index: 1;
        }

        .table-container th, .table-container td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        button {
            padding: 10px 15px;
            margin: 0 5px;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        button[name="action"][value="approve"] {
            background-color: #4CAF50;
        }

        button[name="action"][value="approve"]:hover {
            background-color: #45a049;
        }

        button[name="action"][value="deny"] {
            background-color: #f44336;
        }

        button[name="action"][value="deny"]:hover {
            background-color: #e53935;
        }

        button[name="action"][value="delete"] {
            background-color: #FF9800;
        }

        button[name="action"][value="delete"]:hover {
            background-color: #FB8C00;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background-color: #333;
            color: white;
            border-radius: 8px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .notification.show {
            opacity: 1;
            visibility: visible;
        }

        .notification.success {
            background-color: #4CAF50;
        }

        .notification.error {
            background-color: #f44336;
        }
    </style>
</head>
<body>

<?php include 'admin_navbar.php'; ?>

<h1>Password Change Requests</h1>

<!-- Notification box for messages -->
<?php if ($notification): ?>
<div class="notification show <?php echo strpos($notification, 'success') !== false ? 'success' : 'error'; ?>">
    <?php echo $notification; ?>
</div>
<script>
    setTimeout(function() {
        document.querySelector('.notification').classList.remove('show');
    }, 3000); // Hide notification after 3 seconds
</script>
<?php endif; ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Request Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($request = $request_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($request['request_id']); ?></td>
                <td><?php echo htmlspecialchars($request['username']); ?></td>
                <td><?php echo htmlspecialchars($request['email']); ?></td>
                <td><?php echo date('Y-m-d H:i:s', strtotime($request['request_date'])); ?></td>
                <td><?php echo htmlspecialchars($request['status']); ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                        <?php if ($request['status'] == 'pending'): ?>
                            <button type="submit" name="action" value="approve">Approve</button>
                            <button type="submit" name="action" value="deny">Deny</button>
                        <?php else: ?>
                            <button type="submit" name="action" value="delete">Delete</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
