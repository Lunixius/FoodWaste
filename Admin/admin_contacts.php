<?php
session_start();

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

// Fetch Admin contacts
$admin_query = "SELECT username, email FROM admin";
$admin_result = $conn->query($admin_query);

// Fetch Restaurant contacts
$restaurant_query = "SELECT username, email, phone_number FROM user WHERE user_type = 'Restaurant'";
$restaurant_result = $conn->query($restaurant_query);

// Fetch NGO contacts
$ngo_query = "SELECT username, email, phone_number FROM user WHERE user_type = 'NGO'";
$ngo_result = $conn->query($ngo_query);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Contacts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 90%;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffff; /* White background for better contrast */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        h3 {
            margin-top: 30px;
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        .table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 80px; /* Increased margin for spacing between tables */
            border: 1px solid #dee2e6; /* Full border around the table */
        }
        .table th, .table td {
            border: 1px solid #dee2e6; /* Full border for table cells */
            text-align: center;
            padding: 12px;
            background-color: #fff;
        }
        .table th {
            background-color: #f8f9fa;
            color: #333;
        }
        .btn-message {
            background-color: #007BFF; /* Complete blue for user buttons */
            color: white;
            border: none;
            padding: 8px 16px; /* Added some padding for better sizing */
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease; /* Added transition for a smoother effect */
            font-weight: 500; /* Ensure text weight is consistent */
            display: inline-block; /* Ensure it behaves like a block element */
        }

        .btn-message:hover {
            background-color: #0056b3; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect on hover */
        }

        .btn-message.admin {
            background-color: #FF9800; /* Orange for admin button */
        }

        .btn-message.admin:hover {
            background-color: #E65100; /* Darker orange on hover */
        }

    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'admin_navbar.php'; ?>

    <div class="container">
        <h1>Contacts</h1>

        <!-- Admin Contacts Table -->
        <h3>Admin Contacts</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($admin_result->num_rows > 0): ?>
                    <?php while ($admin = $admin_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><a href="admin_message.php?username=<?php echo urlencode($admin['username']); ?>" class="btn btn-message admin">Message</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No Admin contacts found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Restaurant Contacts Table -->
        <h3>Restaurant Contacts</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($restaurant_result->num_rows > 0): ?>
                    <?php while ($restaurant = $restaurant_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($restaurant['username']); ?></td>
                            <td><?php echo htmlspecialchars($restaurant['email']); ?></td>
                            <td><?php echo htmlspecialchars($restaurant['phone_number']); ?></td>
                            <td><a href="admin_message.php?username=<?php echo urlencode($restaurant['username']); ?>" class="btn btn-message">Message</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No Restaurant contacts found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- NGO Contacts Table -->
        <h3>NGO Contacts</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ngo_result->num_rows > 0): ?>
                    <?php while ($ngo = $ngo_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ngo['username']); ?></td>
                            <td><?php echo htmlspecialchars($ngo['email']); ?></td>
                            <td><?php echo htmlspecialchars($ngo['phone_number']); ?></td>
                            <td><a href="admin_message.php?username=<?php echo urlencode($ngo['username']); ?>" class="btn btn-message">Message</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No NGO contacts found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
