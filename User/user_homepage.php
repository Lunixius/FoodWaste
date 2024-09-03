<?php
include 'navbar.php'; // Include the navigation bar
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Homepage</title>
    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #f0f8ff; /* Light background color */
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Prevent horizontal scroll */
        }
        
        .navbar {
            background-color: #4CAF50; /* Green background for navbar */
            padding: 15px;
            color: white;
            text-align: center;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            color: #4CAF50;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .section-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .section-content a {
            text-decoration: none;
            color: #FF9800;
            font-size: 18px;
            margin: 10px 0;
            transition: color 0.3s ease;
        }

        .section-content a:hover {
            color: #E65100;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="container">
        <h1>Food Waste</h1>
        
        <div class="section">
            <h2>Inventory</h2>
            <div class="section-content">
                <a href="inventory.php">View Inventory</a>
            </div>
        </div>

        <div class="section">
            <h2>Donation</h2>
            <div class="section-content">
                <a href="donation.php">Make Donation</a>
            </div>
        </div>

        <div class="section">
            <h2>Contacts</h2>
            <div class="section-content">
                <a href="contacts.php">Contact </a>
            </div>
        </div>

        <div class="section">
            <h2>Delivery</h2>
            <div class="section-content">
                <a href="delivery.php">Delivery</a>
            </div>
        </div>
    </div>
</body>
</html>
