<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection parameters
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "foodwaste";

// Create a database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Handle logout functionality
if (isset($_POST['logout'])) {
    session_destroy(); // Destroy the session to log out the user
    header('Location: admin_login.php'); // Redirect to login page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Navbar</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* Apply Poppins font globally */
        body, .navbar, .nav-link {
            font-family: 'Poppins', sans-serif;
        }

        /* Navbar customization */
        .navbar {
            background-color: #333333;
            padding: 15px 10px;
        }

        .navbar-brand {
            color: #ffffff;
            font-weight: 500;
        }

        .navbar-nav .nav-link {
            color: #ffffff;
            font-weight: 400;
            padding: 8px 16px;
        }

        .navbar-nav .nav-link:hover {
            color: #f0f0f0;
        }

        /* Custom logout button */
        .btn-link {
            color: #ff4b4b; /* Red logout button */
            text-decoration: none;
            font-weight: 500;
        }

        .btn-link:hover {
            color: #ff6666;
            text-decoration: none;
        }

        /* Responsive toggle button */
        .navbar-toggler {
            border: none;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3E%3Cpath stroke='rgba%28255, 255, 255, 0.5%29' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_homepage.php">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="view_inventory.php">Inventory</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_requests.php">Requests</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="order.php">Delivery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_contacts.php">Contacts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="report.php">Report</a>
                </li>
                <li class="nav-item">
                    <form method="POST" style="display:inline;">
                        <button type="submit" name="logout" class="btn btn-link nav-link">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Bootstrap JS and dependencies (Popper.js, jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
