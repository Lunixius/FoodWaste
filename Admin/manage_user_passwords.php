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

// Approve or deny password change requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action == "approve") {
        // Update the request status
        $stmt = $conn->prepare("UPDATE password_change_requests SET status = 'approved' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $notification = "Request approved. The user can now reset their password.";
    } elseif ($action == "deny") {
        // Deny the request
        $stmt = $conn->prepare("UPDATE password_change_requests SET status = 'denied' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $notification = "Request denied.";
    }
    $stmt->close();

    // Redirect to the same page to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF'] . "?notification=" . urlencode($notification) . "&status=" . urlencode($action));
    exit();
}

// Retrieve the notification message from the URL, if present
if (isset($_GET['notification'])) {
    $notification = htmlspecialchars($_GET['notification']);
    $notification_class = ($_GET['status'] == 'approve') ? 'success' : 'error';
}

// Fetch all password change requests with username and email
$request_query = $conn->prepare("
    SELECT r.request_id, u.username, u.email, r.status
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
        /* General page styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Navbar styling */
.navbar {
    width: 100%;
    background-color: #333;  /* Change as needed */
    color: white;
    display: flex;
    align-items: center;
    padding: 10px 20px;
    box-sizing: border-box;
    justify-content: space-between; /* Spaces out content in navbar */
}

.navbar a {
    color: white;
    text-decoration: none;
    padding: 8px 16px;
}

.navbar a:hover {
    background-color: #575757; /* Subtle hover effect */
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

        /* Table styling */
        table {
            width: 80%;
            margin-top: 20px;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        /* Button styling */
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

        /* Notification box styling */
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

<h1>Pending Password Change Requests</h1>

<!-- Notification box for messages -->
<?php if ($notification): ?>
<div class="notification <?php echo $notification_class; ?>" id="notification-box">
    <?php echo $notification; ?>
</div>
<script>
    // Display the notification box for a short time
    const notificationBox = document.getElementById('notification-box');
    notificationBox.classList.add('show');
    setTimeout(() => {
        notificationBox.classList.remove('show');
    }, 3000);
</script>
<?php endif; ?>

<table>
    <tr>
        <th>Request ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while ($request = $request_result->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($request['request_id']); ?></td>
        <td><?php echo htmlspecialchars($request['username']); ?></td>
        <td><?php echo htmlspecialchars($request['email']); ?></td>
        <td><?php echo ucfirst($request['status']); ?></td>
        <td>
            <form method="POST" action="">
                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                <?php if ($request['status'] == 'pending'): ?>
                    <button type="submit" name="action" value="approve">Approve</button>
                    <button type="submit" name="action" value="deny">Deny</button>
                <?php else: ?>
                    <em><?php echo ucfirst($request['status']); ?></em>
                <?php endif; ?>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
