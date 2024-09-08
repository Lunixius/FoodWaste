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

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $password = $conn->real_escape_string($_POST['password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);
    $user_type = $conn->real_escape_string($_POST['user_type']);

    // Check if passwords match
    if ($password != $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if the email or username already exists
        $check_sql = "SELECT * FROM user WHERE email = '$email' OR username = '$username'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $error_message = "Username or email already taken.";
        } else {
            // Store the user details in the session until they verify the code
            $_SESSION['registration_data'] = [
                'username' => $username,
                'email' => $email,
                'phone_number' => $phone_number,
                'password' => password_hash($password, PASSWORD_DEFAULT),  // Store hashed password
                'user_type' => $user_type,
            ];

            // Redirect to the verification page
            $_SESSION['registered_email'] = $email; // Store email for verification
            header("Location: user_verification.php");
            exit();
        }
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
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
            overflow: hidden;
        }

        /* Scrollable container */
        .register-container {
            width: 500px;
            height: 80vh; /* Allow some space for scrolling */
            padding: 20px;
            overflow-y: auto;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border-top: 5px solid #4CAF50;
            border-left: 5px solid #FF9800;
            position: relative;
            animation: zoom-out 0.5s ease-out;
        }

        /* Zoom-out animation */
        @keyframes zoom-out {
            0% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Background design */
        .register-container:before {
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

        .register-container:after {
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

        .register-container h1 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
            color: #444;
            font-weight: 700;
        }

        .register-container label {
            font-size: 14px;
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 500;
        }

        .register-container input,
        .register-container select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            margin-bottom: 10px;
            background-color: #f7f7f7;
            transition: all 0.3s ease;
        }

        .register-container input:focus,
        .register-container select:focus {
            background-color: #fff;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
            outline: none;
        }

        .register-container button {
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
            margin-top: 10px;
        }

        .register-container button:hover {
            background-color: #388E3C;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .register-container p {
            text-align: center;
            margin-top: 10px;
            color: #666;
            font-size: 14px;
        }

        .register-container p a {
            color: #FF9800;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-container p a:hover {
            color: #E65100;
        }

    </style>
</head>
<body>
    <div class="register-container">
        <h1>User Registration</h1>
        <?php if (!empty($error_message)): ?>
            <p style="color: red; text-align: center;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <p style="color: green; text-align: center;"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <form action="user_register.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone_number">Phone Number:</label>
            <input type="tel" id="phone_number" name="phone_number" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <label for="user_type">User Type:</label>
            <select id="user_type" name="user_type" required>
                <option value="Restaurant">Restaurant</option>
                <option value="NGO">NGO</option>
            </select>

            <button type="submit">Register</button>
            <p><a href="user_login.php">Already have an account? Login</a></p>
        </form>
    </div>
</body>
</html>
