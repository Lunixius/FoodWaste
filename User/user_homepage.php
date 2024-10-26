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

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        /* Background color for NGO users */
        .ngo-background {
            background-color: #e9f5f5;
        }

        /* Background color for Restaurant users */
        .restaurant-background {
            background-color: #f0e6ff;
        }

        .container {
            max-width: 85%;
            margin: 30px auto;
            text-align: center;
        }

        h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 40px;
            font-weight: 600;
        }

        .cards {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
            padding: 20px;
        }

        .card {
            background-color: #fff;
            width: 300px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 6px solid #4CAF50; /* Highlighting cards */
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .card h2 {
            color: #4CAF50;
            margin-bottom: 20px;
            font-size: 1.6rem;
            font-weight: 500;
        }

        .card a {
            display: inline-block;
            text-decoration: none;
            background-color: #FF9800;
            color: #fff;
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .card a:hover {
            background-color: #E65100;
        }

        .card i {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #4CAF50; /* Icon color */
        }

        /* Customize button styles for better interaction */
        .card a.btn {
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 6px;
        }

        /* Additional styling for better responsiveness */
        @media (max-width: 768px) {
            .cards {
                flex-direction: column;
                align-items: center;
            }

            .card {
                width: 90%;
            }
        }
    </style>
</head>
<body class="<?php echo ($user_type === 'NGO') ? 'ngo-background' : 'restaurant-background'; ?>">
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="container">
        <h1>Welcome, <?php echo ($user_type === 'NGO') ? 'NGO Partner' : 'Restaurant Partner'; ?></h1>
        
        <div class="cards">
            <!-- Card for Inventory -->
            <div class="card">
                <i class="fas fa-box"></i>
                <h2>Inventory</h2>
                <?php if ($user_type === 'NGO'): ?>
                    <a href="item.php">Browse Available Items</a>
                <?php else: ?>
                    <a href="inventory.php">Manage Inventory</a>
                <?php endif; ?>
            </div>
            
            <!-- Card for Requests -->
            <div class="card">
                <i class="fas fa-receipt"></i>
                <h2>Requests</h2>
                <?php if ($user_type === 'NGO'): ?>
                    <a href="request.php">View My Requests</a>
                <?php else: ?>
                    <a href="requested.php">Manage Requests</a>
                <?php endif; ?>
            </div>
            
            <!-- Card for Pickup or Delivery -->
            <div class="card">
                <i class="fas fa-truck"></i>
                <h2><?php echo ($user_type === 'NGO') ? 'Pickup' : 'Delivery'; ?></h2>
                <a href="<?php echo ($user_type === 'NGO') ? 'pickup.php' : 'delivery.php'; ?>">
                    <?php echo ($user_type === 'NGO') ? 'View Pickup Details' : 'Manage Deliveries'; ?>
                </a>
            </div>

            <!-- Card for Orders-->
            <div class="card">
                <i class="fas fa-shopping-cart"></i>
                <h2>Orders</h2>
                <a href="<?php echo ($user_type === 'NGO') ? 'confirmed.php' : 'confirm.php'; ?>">View Orders</a>
            </div>

            <!-- Card for Contacts -->
            <div class="card">
                <i class="fas fa-address-book"></i>
                <h2>Contacts</h2>
                <a href="contacts.php">View Contacts</a>
            </div>

            <!-- New Card for Reports -->
            <div class="card">
                <i class="fas fa-file-alt"></i>
                <h2>Reports</h2>
                <a href="user_report.php">Download Reports</a>
            </div>
        </div>
    </div>
</body>
</html>
