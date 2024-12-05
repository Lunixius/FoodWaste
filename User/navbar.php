<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (isset($_SESSION['username'])) {
    $username = htmlspecialchars($_SESSION['username']);

    // Fetch user type from the database
    $servername = "localhost";
    $db_username = "root";  // Replace with your database username
    $db_password = "";  // Replace with your database password
    $dbname = "foodwaste";

    // Create a database connection
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_type_query = $conn->prepare("SELECT user_type FROM user WHERE username = ?");
    $user_type_query->bind_param("s", $username);
    $user_type_query->execute();
    $user_type_result = $user_type_query->get_result();
    $user = $user_type_result->fetch_assoc();
    $user_type = htmlspecialchars($user['user_type']);

    $user_type_query->close();
    $conn->close();

    // Logout logic
    if (isset($_POST['logout']) && $_POST['logout'] == 1) {
        session_destroy();
        header("Location: user_login.php");
        exit();
    }
} else {
    $username = "Guest";
    $user_type = "N/A";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Bar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .navbar {
            background-color: #000; /* Black background */
        }

        .navbar .nav-link {
            color: white;
            font-weight: 500;
        }

        .navbar .nav-link:hover {
            color: #f8f9fa;
            text-decoration: underline;
        }

        .navbar .profile-link {
            color: white;
            font-weight: bold;
            margin-right: 20px;
        }

        .navbar .logout-icon {
            color: red;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .user-info {
            color: #fff;
            font-size: 1rem;
            margin-right: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <!-- User Info -->
            <div class="user-info">
                <strong><?php echo $username; ?></strong> 
                <span>(<?php echo $user_type; ?>)</span>
            </div>

            <!-- Navbar Links -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="user_homepage.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ($user_type === 'NGO') ? 'item.php' : 'inventory.php'; ?>">Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ($user_type === 'NGO') ? 'request.php' : 'requested.php'; ?>">Requests</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ($user_type === 'NGO') ? 'pickup.php' : 'delivery.php'; ?>">
                            <?php echo ($user_type === 'NGO') ? 'Pickup' : 'Delivery'; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ($user_type === 'NGO') ? 'confirmed.php' : 'confirm.php'; ?>">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacts.php">Contacts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_report.php">Reports</a>
                    </li>
                </ul>
            </div>

            <!-- Profile and Logout -->
            <a href="user_profile.php" class="profile-link">Profile</a>
            <div class="logout-icon" onclick="document.getElementById('logoutForm').submit();">
                <i class="fa-solid fa-power-off"></i>
            </div>
        </div>
    </nav>

    <!-- Logout Form -->
    <form id="logoutForm" method="post" action="">
        <input type="hidden" name="logout" value="1">
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
