<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$servername = "localhost";
$db_username = "root";  
$db_password = "";  
$dbname = "foodwaste";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the password reset request or the actual password reset
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['identifier'])) {
        // Step 1: Request password reset
        $identifier = $_POST['identifier']; // Can be email or username

        // Check if identifier is a valid email
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
        } else {
            // Assume it's a username
            $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
        }

        $stmt->bind_param("s", $identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['id'];

            // Store the password change request
            $request_stmt = $conn->prepare("INSERT INTO password_change_requests (user_id, status) VALUES (?, 'pending')");
            $request_stmt->bind_param("i", $user_id);

            if ($request_stmt->execute()) {
                echo "Your request has been sent to the admin for approval. Please wait for approval.";
            } else {
                echo "Error sending request: " . $request_stmt->error;
            }

            $request_stmt->close();
        } else {
            echo "No user found with that email or username.";
        }

        $stmt->close();
    } elseif (isset($_POST['new_password']) && isset($_POST['user_id'])) {
        // Step 2: Reset password if admin has approved
        $user_id = $_POST['user_id'];
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        // Update the user's password
        $stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $user_id);
        
        if ($stmt->execute()) {
            echo "Password reset successfully.";
        } else {
            echo "Error resetting password: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Check if the page is accessed with a user ID parameter (indicating approved reset)
$is_reset_mode = isset($_GET['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $is_reset_mode ? 'Change Password' : 'Forgot Password'; ?></title>
</head>
<body>
    <?php if ($is_reset_mode): ?>
        <h1>Change Your Password</h1>
        <form method="POST" action="">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_GET['user_id']); ?>">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            <button type="submit">Reset Password</button>
        </form>
    <?php else: ?>
        <h1>Request Password Change</h1>
        <form method="POST" action="">
            <label for="identifier">Your Email or Username:</label>
            <input type="text" id="identifier" name="identifier" required>
            <button type="submit">Request Password Change</button>
        </form>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>
