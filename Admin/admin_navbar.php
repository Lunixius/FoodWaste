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

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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
                    <a class="nav-link" href="manage_contacts.php">Contact</a>
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
