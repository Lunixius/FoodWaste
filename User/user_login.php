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
<html>
<head>
    <title>User Login</title>
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
            overflow: hidden; /* Prevents any overflow */
        }

        .login-form {
            width: 500px;  /* Adjusted width */
            padding: 20px;  /* Reduced padding */
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border-top: 5px solid #4CAF50;
            border-left: 5px solid #FF9800;
            position: relative;
        }

        .login-form:before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: #FF9800;
            border-radius: 50%;
            opacity: 0.2;
        }

        .login-form:after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 150px;
            height: 150px;
            background: #4CAF50;
            border-radius: 50%;
            opacity: 0.2;
        }

        .login-form h1 {
            font-size: 24px;  /* Slightly reduced font size */
            text-align: center;
            margin-bottom: 20px;  /* Reduced margin */
            color: #444;
            font-weight: 700;
        }

        .login-form label {
            font-size: 14px;  /* Slightly reduced font size */
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 500;
        }

        .login-form input,
        .login-form select {
            width: 100%;
            padding: 10px;  /* Reduced padding */
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            margin-bottom: 10px;  /* Reduced margin */
            background-color: #f7f7f7;
            transition: all 0.3s ease;
        }

        .login-form input:focus,
        .login-form select:focus {
            background-color: #fff;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
            outline: none;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 600;
        }

        .login-form button {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            margin-top: 10px;  /* Reduced margin */
        }

        .login-form button:hover {
            background-color: #388E3C;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .login-form p {
            text-align: center;
            margin-top: 10px;  /* Reduced margin */
            color: #666;
            font-size: 14px;
        }

        .login-form p a {
            color: #FF9800;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-form p a:hover {
            color: #E65100;
        }
    </style>
</head>
<body>
    <div class="login-form">
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
