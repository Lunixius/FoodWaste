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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $user_type = $conn->real_escape_string($_POST['user_type']);

    // Check if the username or email already exists
    $sql = "SELECT * FROM user WHERE username = '$username' OR email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $error_message = "Username or Email already exists.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the database
        $sql = "INSERT INTO user (username, email, password, phone_number, user_type) VALUES ('$username', '$email', '$hashed_password', '$phone_number', '$user_type')";
        if ($conn->query($sql) === TRUE) {
            header("Location: user_verification.php");
            exit();
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
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
            overflow: hidden; /* Prevents any overflow */
        }

        .register-form {
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

        .register-form:before {
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

        .register-form:after {
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

        .register-form h1 {
            font-size: 24px;  /* Slightly reduced font size */
            text-align: center;
            margin-bottom: 20px;  /* Reduced margin */
            color: #444;
            font-weight: 700;
        }

        .register-form label {
            font-size: 14px;  /* Slightly reduced font size */
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 500;
        }

        .register-form input,
        .register-form select {
            width: 100%;
            padding: 10px;  /* Reduced padding */
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            margin-bottom: 10px;  /* Reduced margin */
            background-color: #f7f7f7;
            transition: all 0.3s ease;
        }

        .register-form input:focus,
        .register-form select:focus {
            background-color: #fff;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
            outline: none;
        }

        .register-form button {
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

        .register-form button:hover {
            background-color: #388E3C;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .register-form p {
            text-align: center;
            margin-top: 10px;  /* Reduced margin */
            color: #666;
            font-size: 14px;
        }

        .register-form p a {
            color: #FF9800;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-form p a:hover {
            color: #E65100;
        }
    </style>
</head>
<body>
    <div class="register-form">
        <h1>User Registration</h1>
        <?php if (isset($error_message)): ?>
            <p style="color: red; text-align: center;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <?php if (isset($success_message)): ?>
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
