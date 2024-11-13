<?php
session_start();

// Check if admin is already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_homepage.php");
    exit();
}

// Database connection parameters
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "foodwaste";

// Create a database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_input = $_POST['login_input'];
    $password = $_POST['password'];

    // Prepare and execute query to fetch admin based on username or email
    $query = $conn->prepare("SELECT * FROM admin WHERE username = ? OR email = ?");
    $query->bind_param("ss", $login_input, $login_input);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];

            // Redirect to admin homepage
            header('Location: admin_homepage.php');
            exit();
        } else {
            $error = "Invalid username/email or password.";
        }
    } else {
        $error = "No admin found with that username or email.";
    }
    
    $query->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #e9f5ff;
            margin: 0;
        }

        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            width: 400px;
            max-width: 100%;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
            font-weight: 600;
        }

        .form-control {
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 10px;
            font-size: 16px;
        }

        .btn-primary {
            width: 100%;
            background-color: #007bff;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .error {
            color: #ff4d4d;
            text-align: center;
            margin-top: 10px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .register-link a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #0056b3;
        }

        /* Styling for Input Fields and Form */
        input::placeholder {
            color: #6c757d;
            font-weight: 400;
        }

        /* Add subtle box shadow for hover and focus */
        .form-control:focus {
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
            border-color: #007bff;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Admin Login</h2>
        <form method="POST" action="">
            <input type="text" name="login_input" class="form-control" placeholder="Username or Email" required>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <button type="submit" class="btn btn-primary">Login</button>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
        </form>

        <div class="register-link">
            <p>Don't have an account? <a href="admin_registration.php">Register here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
