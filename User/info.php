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
$query = $conn->prepare("SELECT username, user_type FROM user WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$username = $user['username'];
$user_type = $user['user_type'];

// Check if an inventory item ID was passed
if (!isset($_GET['id'])) {
    echo "No inventory item selected.";
    exit();
}

$inventory_id = $_GET['id'];

// Fetch the inventory item details
$inventory_query = $conn->prepare("SELECT * FROM inventory WHERE id = ?");
$inventory_query->bind_param("i", $inventory_id);
$inventory_query->execute();
$inventory_result = $inventory_query->get_result();

if ($inventory_result->num_rows === 0) {
    echo "No such inventory item found.";
    exit();
}

$inventory = $inventory_result->fetch_assoc();

// Close the database connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <title>Inventory Info</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }
        .navbar {
            background-color: #000;
            padding: 15px;
        }
        .container {
            margin-top: 50px;
            max-width: 800px;
        }
        h2 {
            font-weight: 600;
            margin-bottom: 30px;
        }
        .form-group label {
            font-weight: 500;
            color: #555;
        }
        .form-control {
            border-radius: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #fff;
            font-size: 16px;
            color: #555;
        }
        textarea.form-control {
            resize: none;
        }
        .form-group img {
            border-radius: 10px;
            margin-top: 15px;
        }
        .btn-back {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-back:hover {
            background-color: #333;
        }
        .card {
            padding: 25px;
            border-radius: 15px;
            background-color: #fff;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>Inventory Item Information</h2>
        <div class="card">
            <!-- Entity ID Display -->
            <div class="form-group">
                <label for="entity_id">Entity ID</label>
                <input type="text" class="form-control" id="entity_id" value="<?php echo $inventory_id; ?>" readonly>
            </div>

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($inventory['name']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" class="form-control" id="category" value="<?php echo htmlspecialchars($inventory['category']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" rows="3" readonly><?php echo isset($inventory['description']) ? htmlspecialchars($inventory['description']) : 'No description available'; ?></textarea>
            </div>

            <div class="form-group">
                <label for="picture">Picture</label>
                <?php if (!empty($inventory['picture'])): ?>
                    <img src="upload/<?php echo htmlspecialchars($inventory['picture']); ?>" alt="Image" width="200">
                <?php else: ?>
                    <p>No picture available</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="donor">Donor</label>
                <input type="text" class="form-control" id="donor" value="<?php echo htmlspecialchars($inventory['donor']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="date_created">Date Created</label>
                <input type="text" class="form-control" id="date_created" value="<?php echo htmlspecialchars($inventory['date_created']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="last_modified">Last Modified</label>
                <input type="text" class="form-control" id="last_modified" value="<?php echo htmlspecialchars($inventory['last_modified']); ?>" readonly>
            </div>

            <!-- Back Button -->
            <button class="btn-back" onclick="window.history.back()">Go Back</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
