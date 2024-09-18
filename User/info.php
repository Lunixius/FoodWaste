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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Inventory Info</title>
    <style>
        body {
            font-family: 'Lato', sans-serif;
        }
        .navbar {
            background-color: #000;
            padding: 15px;
        }
        .container {
            margin-top: 50px;
        }
        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>Inventory Item Information</h2>

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
            <textarea class="form-control" id="description" readonly><?php echo isset($inventory['description']) ? htmlspecialchars($inventory['description']) : 'No description available'; ?></textarea>
        </div>

        <div class="form-group">
            <label for="picture">Picture</label>
            <?php if (!empty($inventory['picture'])): ?>
                <img src="upload/<?php echo htmlspecialchars($inventory['picture']); ?>" alt="Image" width="200">
            <?php else: ?>
                No picture available
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

        <!-- Edit History Section (if available) -->
        <div class="form-group">
            <label for="edit_history">Edit History</label>
            <?php
            // Fetch edit history if available (assuming there's an `edit_history` table storing edit logs)
            $history_query = $conn->prepare("SELECT * FROM edit_history WHERE inventory_id = ?");
            $history_query->bind_param("i", $inventory_id);
            $history_query->execute();
            $history_result = $history_query->get_result();

            if ($history_result->num_rows > 0): ?>
                <ul>
                    <?php while ($history = $history_result->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($history['edit_time']) . " - " . htmlspecialchars($history['change_description']); ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No edit history available.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
