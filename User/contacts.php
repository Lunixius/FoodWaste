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

// Fetch user info to determine the user type
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT user_type FROM user WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_type = $user['user_type'];

$user_query->close();

// Fetch Restaurant and NGO contacts based on user type
if ($user_type === 'Restaurant') {
    $other_user_query = "SELECT username, email, phone_number FROM user WHERE user_type = 'NGO'";
} elseif ($user_type === 'NGO') {
    $other_user_query = "SELECT username, email, phone_number FROM user WHERE user_type = 'Restaurant'";
}

$other_user_result = $conn->query($other_user_query);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts</title>
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
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h1>Contacts</h1>

        <!-- Other User Contacts Table -->
        <h3><?php echo ($user_type === 'Restaurant') ? 'NGO Contacts' : 'Restaurant Contacts'; ?></h3>
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
                <?php if ($other_user_result->num_rows > 0): ?>
                    <?php while ($other_user = $other_user_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($other_user['username']); ?></td>
                            <td><?php echo htmlspecialchars($other_user['email']); ?></td>
                            <td><?php echo htmlspecialchars($other_user['phone_number']); ?></td>
                            <td><a href="message.php?username=<?php echo urlencode($other_user['username']); ?>" class="btn btn-message">Message</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No contacts found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
