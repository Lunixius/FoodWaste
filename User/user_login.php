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

// Initialize error message
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = $_POST['username_or_email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    // Check if the input is an email or a username, and prepare the query accordingly
    if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
        // Input is an email
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? AND user_type = ?");
    } else {
        // Input is a username
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? AND user_type = ?");
    }

    // Bind parameters and execute the statement
    $stmt->bind_param("ss", $input, $user_type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists, verify the password
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            header("Location: user_homepage.php");  // Redirect to the user dashboard
            exit();
        } else {
            $error_message = "Incorrect info.";
        }
    } else {
        $error_message = "Incorrect info.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
            position: relative;
        }

        .login-container:before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background-color: rgba(76, 175, 80, 0.2);
            border-radius: 50%;
            top: -100px;
            left: -50px;
            z-index: -1;
        }

        .login-container:after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background-color: rgba(255, 152, 0, 0.2);
            border-radius: 50%;
            bottom: -100px;
            right: -50px;
            z-index: -1;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #4CAF50;
            font-weight: 600;
        }

        label {
            font-size: 14px;
            text-align: left;
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #666;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            background-color: #f7f7f7;
            transition: border-color 0.3s ease;
        }

        input:focus,
        select:focus {
            border-color: #4CAF50;
            outline: none;
            background-color: #fff;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
        }

        button:hover {
            background-color: #388E3C;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .login-container p {
            font-size: 14px;
            color: #666;
            margin-top: 15px;
        }

        .login-container p a {
            color: #FF9800;
            text-decoration: none;
            font-weight: 600;
        }

        .login-container p a:hover {
            color: #E65100;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>User Login</h1>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="user_login.php" method="post">
            <label for="username_or_email">Username/Email:</label>
            <input type="text" id="username_or_email" name="username_or_email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="user_type">User Type:</label>
            <select id="user_type" name="user_type" required>
                <option value="Restaurant">Restaurant</option>
                <option value="NGO">NGO</option>
            </select>

            <button type="submit">Login</button>
            <p><a href="user_register.php">Register</a></p>
            <p><a href="user_forgot_password.php">Forgot Password?</a></p>
        </form>
    </div>
</body>
</html>
