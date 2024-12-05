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
    
    // Check if the connection was successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user type
    $user_type_query = $conn->prepare("SELECT user_type FROM user WHERE username = ?");
    $user_type_query->bind_param("s", $username);
    $user_type_query->execute();
    $user_type_result = $user_type_query->get_result();
    $user = $user_type_result->fetch_assoc();
    $user_type = htmlspecialchars($user['user_type']); // Store user type for display

    
    // Logout logic
    if (isset($_POST['logout']) && $_POST['logout'] == 1) {
        // Destroy session and redirect to login page
        session_destroy();
        header("Location: user_login.php"); // Replace with your login page
        exit();
    }

    // Close the database connection
    $conn->close();
} else {
    $username = "Guest"; // Default for not logged in
    $user_type = "N/A";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .navbar {
            background-color: #000; /* Black background */
            padding: 15px;
            width: 100%;
        }

        .user-info {
            background-color: white; /* White background */
            padding: 5px 10px; /* Padding for user info */
            border-radius: 5px; /* Rounded corners */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3); /* Shadow for depth */
            font-size: 14px; /* Font size */
            color: #333; /* Text color */
            margin-right: 15px; /* Margin for spacing */
        }

        .profile-text {
            color: white;
            font-weight: bold;
            font-size: 20px;
            margin-right: 20px; /* Space between profile text and logout icon */
        }

        .logout-icon {
            font-size: 25px;
            color: red;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <!-- User Info Container at the most left -->
            <div class="user-info d-flex align-items-center">
                <strong><?php echo $username; ?></strong>
                <small class="ms-2"><?php echo $user_type; ?></small>
            </div>

            <div class="collapse navbar-collapse justify-content-center" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="user_homepage.php">Home</a>
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

            <!-- Profile Text -->
            <div class="profile-text">
                <a href="user_profile.php" style="text-decoration: none; color: inherit;">Profile</a>
            </div>

            <!-- Logout Button -->
            <div class="logout-icon" onclick="document.getElementById('logoutForm').submit();">
                <i class="fa-solid fa-power-off"></i>
            </div>
        </div>
    </nav>

    <!-- Hidden Logout Form -->
    <form id="logoutForm" method="post" action="">
        <input type="hidden" name="logout" value="1">
    </form>

    <!-- Move Bootstrap JS to the end of the body to ensure proper initialization -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
