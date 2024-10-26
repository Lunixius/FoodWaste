<?php
session_start();
$notification = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username_or_email'], $_POST['new_password'])) {
    $username_or_email = $_POST['username_or_email'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    $conn = new mysqli("localhost", "root", "", "foodwaste");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM user WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Insert a password change request
        $insert_request = $conn->prepare("INSERT INTO password_change_requests (user_id, new_password, status) VALUES (?, ?, 'pending')");
        $insert_request->bind_param("is", $user_id, $new_password);
        $insert_request->execute();

        $notification = "Your password change request has been submitted. Please wait for approval.";
    } else {
        $notification = "User not found. Please check your username or email.";
    }

    $stmt->close();
    $conn->close();

    // Redirect back to login with notification
    header("Location: user_login.php?notification=" . urlencode($notification));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        h1 {
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        label {
            margin-bottom: 5px;
            display: block;
            font-weight: 600;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .notification {
            text-align: center;
            margin: 15px 0;
            color: #d9534f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Request Password Change</h1>
        <?php if ($notification): ?>
            <div class="notification"><?php echo htmlspecialchars($notification); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="username_or_email">Username or Email:</label>
            <input type="text" id="username_or_email" name="username_or_email" required>
            
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            
            <button type="submit">Submit Request</button>
        </form>
    </div>
</body>
</html>
