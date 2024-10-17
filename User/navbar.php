<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection parameters
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

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: user_login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user type
$user_type_query = $conn->prepare("SELECT user_type FROM user WHERE username = ?");
$user_type_query->bind_param("s", $username);
$user_type_query->execute();
$user_type_result = $user_type_query->get_result();
$user = $user_type_result->fetch_assoc();
$user_type = $user['user_type']; // Store user type for dynamic menu items

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: user_login.php");
    exit();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .navbar {
            background-color: #000; /* Black background */
            padding: 15px;
            width: 100%;
        }

        .navbar-nav {
            flex-direction: row;
            margin-left: auto;
            margin-right: auto;
        }

        .nav-item {
            margin-right: 30px;
        }

        .profile-text {
            position: absolute;
            right: 60px; /* Adjusted to leave space for the logout button */
            top: 15px;
            cursor: pointer;
            font-size: 20px;
            color: white;
            font-weight: bold;
        }

        .logout-icon {
            position: absolute;
            right: 20px;
            top: 15px;
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
                        <!-- Dynamic label and link for Delivery/Pickup -->
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

