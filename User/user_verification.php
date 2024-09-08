<?php
session_start();

// Database connection parameters
$servername = "localhost";
$db_username = "root";  // Replace with your database username
$db_password = "";  // Replace with your database password
$dbname = "foodwaste";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assume the registered email is stored in a session variable
$registered_email = $_SESSION['registered_email'];

$verification_code = "";
$success_message = "";
$error_message = "";

// Function to send a 6-digit verification code to the registered email
function sendVerificationCode($email) {
    $code = rand(100000, 999999);
    $_SESSION['verification_code'] = $code;

    // Mail the code (for demonstration purposes, this function just stores the code)
    mail($email, "Your Verification Code", "Your 6-digit verification code is: $code");

    return $code;
}

// Handle send code request
if (isset($_POST['send_code'])) {
    sendVerificationCode($registered_email);
    $success_message = "Verification code has been sent to your email.";
}

// Handle confirmation request
if (isset($_POST['confirm_code'])) {
    $input_code = $conn->real_escape_string($_POST['verification_code']);
    
    if ($input_code == $_SESSION['verification_code']) {
        // Correct code, redirect to login
        header("Location: user_login.php");
        exit();
    } else {
        // Incorrect code
        $error_message = "Incorrect code.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Verification</title>
    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-image: url('your-background-image.jpg'); 
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        .verification-form {
            width: 500px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            background-color: rgba(255, 255, 255, 0.9);
            text-align: center;
        }

        .verification-form h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #444;
        }

        .verification-form p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .verification-form input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        .verification-form button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .send-code-btn {
            background-color: #4CAF50;
            color: white;
        }

        .send-code-btn:disabled {
            background-color: #aaa;
        }

        .confirm-btn {
            background-color: #FF9800;
            color: white;
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
        }

        .success-message {
            color: green;
            margin-bottom: 10px;
        }
    </style>
    <script>
        function startCooldown() {
            const button = document.getElementById('send-code-btn');
            button.disabled = true;

            let cooldown = 10;
            const interval = setInterval(() => {
                if (cooldown <= 0) {
                    clearInterval(interval);
                    button.disabled = false;
                    button.innerText = "Didn't receive code? Send again";
                } else {
                    button.innerText = `Wait ${cooldown} seconds...`;
                    cooldown--;
                }
            }, 1000);
        }
    </script>
</head>
<body>
    <div class="verification-form">
        <h1>Email Verification</h1>
        <p>Confirm to send a 6-digit verification code to your email. <?php echo $registered_email; ?></p>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form method="post" action="user_verification.php">
            <input type="text" name="verification_code" placeholder="Enter your verification code" required>
            <button type="submit" name="confirm_code" class="confirm-btn">Confirm</button>
        </form>
        <form method="post" action="user_verification.php">
            <button type="submit" name="send_code" id="send-code-btn" class="send-code-btn" onclick="startCooldown()">Send Code</button>
        </form>
    </div>
</body>
</html>
