<?php
session_start();

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
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

// Fetch user info
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT user_type FROM user WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_type = $user['user_type'];

$user_query->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 90%;
            margin: 20px auto;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 40px;
        }

        .cards {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px; /* Increased gap for better spacing */
            padding: 20px;
        }

        .card {
            background-color: #fff;
            width: 280px; /* Slightly increased card width */
            padding: 25px; /* Increased padding */
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        .card h2 {
            color: #4CAF50;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .card a {
            text-decoration: none;
            color: #FF9800;
            font-size: 16px;
            display: block;
            margin-top: 10px;
            transition: color 0.3s ease;
        }

        .card a:hover {
            color: #E65100;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Food Waste</h1>
        
        <div class="cards">
            <div class="card">
                <h2>Inventory</h2>
                <?php if ($user_type === 'NGO'): ?>
                    <a href="item.php">Browse Inventory</a>
                <?php else: ?>
                    <a href="inventory.php">View Inventory</a>
                <?php endif; ?>
            </div>
            <div class="card">
                <h2>Contacts</h2>
                <a href="contacts.php">Contact</a>
            </div>
            <div class="card">
                <h2>Delivery</h2>
                <a href="delivery.php">Delivery</a>
            </div>
            <div class="card">
                <h2>Requests</h2>
                <?php if ($user_type === 'NGO'): ?>
                    <a href="request.php">View My Requests</a>
                <?php else: ?>
                    <a href="requested.php">Manage Requests</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
