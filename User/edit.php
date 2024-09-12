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

// If not logged in as restaurant, restrict access
if ($user_type !== 'Restaurant') {
    echo "Access denied. Only restaurant users can edit inventory.";
    exit();
}

// Check if an inventory item ID was passed
if (!isset($_GET['id'])) {
    echo "No inventory item selected.";
    exit();
}

$inventory_id = $_GET['id'];

// Fetch the inventory item details
$inventory_query = $conn->prepare("SELECT * FROM inventory WHERE id = ? AND donor = ?");
$inventory_query->bind_param("is", $inventory_id, $username);
$inventory_query->execute();
$inventory_result = $inventory_query->get_result();

if ($inventory_result->num_rows === 0) {
    echo "No such inventory item found or you're not authorized to edit this item.";
    exit();
}

$inventory = $inventory_result->fetch_assoc();

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $picture = $inventory['picture'];

    // Handle picture upload if a new picture is provided
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === 0) {
        $target_dir = "upload/";
        $picture = basename($_FILES["picture"]["name"]);
        $target_file = $target_dir . $picture;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is a valid image
        $check = getimagesize($_FILES["picture"]["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            exit();
        }

        // Allow only JPG, JPEG, PNG files
        if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
            echo "Sorry, only JPG, JPEG, & PNG files are allowed.";
            exit();
        }

        // Move the file to the upload directory
        if (!move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
            echo "Sorry, there was an error uploading your file.";
            exit();
        }
    }

    // Update the inventory item in the database
    $update_query = $conn->prepare("UPDATE inventory SET name = ?, category = ?, description = ?, picture = ? WHERE id = ? AND donor = ?");
    $update_query->bind_param("ssssis", $name, $category, $description, $picture, $inventory_id, $username);

    if ($update_query->execute()) {
        echo "Inventory updated successfully!";
        header("Location: inventory.php");
        exit();
    } else {
        echo "Error: " . $update_query->error;
    }
}

// Handle deletion of the inventory item
if (isset($_POST['delete'])) {
    $delete_query = $conn->prepare("DELETE FROM inventory WHERE id = ? AND donor = ?");
    $delete_query->bind_param("is", $inventory_id, $username);

    if ($delete_query->execute()) {
        echo "Item deleted successfully!";
        header("Location: inventory.php");
        exit();
    } else {
        echo "Error: " . $delete_query->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Inventory</title>
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
        .delete-button {
            background-color: red;
            color: white;
            display: inline-flex;
            align-items: center;
        }
        .delete-button i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>
    </nav>

    <div class="container">
    <h2>Edit Inventory Item</h2>
    <form action="edit.php?id=<?php echo $inventory_id; ?>" method="POST" enctype="multipart/form-data">
        <!-- Entity ID Display -->
        <div class="form-group">
            <label for="entity_id">Entity ID</label>
            <input type="text" class="form-control" id="entity_id" name="entity_id" value="<?php echo $inventory_id; ?>" readonly>
        </div>

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($inventory['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <select class="form-control" id="category" name="category" required>
                <option value="Fruits and Vegetables" <?php if ($inventory['category'] == 'Fruits and Vegetables') echo 'selected'; ?>>Fruits and Vegetables</option>
                <option value="Dairy Products" <?php if ($inventory['category'] == 'Dairy Products') echo 'selected'; ?>>Dairy Products</option>
                <option value="Meat and Fish" <?php if ($inventory['category'] == 'Meat and Fish') echo 'selected'; ?>>Meat and Fish</option>
                <option value="Grains and Cereals" <?php if ($inventory['category'] == 'Grains and Cereals') echo 'selected'; ?>>Grains and Cereals</option>
                <option value="Baked Goods" <?php if ($inventory['category'] == 'Baked Goods') echo 'selected'; ?>>Baked Goods</option>
                <option value="Prepared Foods" <?php if ($inventory['category'] == 'Prepared Foods') echo 'selected'; ?>>Prepared Foods</option>
                <option value="Beverages" <?php if ($inventory['category'] == 'Beverages') echo 'selected'; ?>>Beverages</option>
                <option value="Condiments and Sauces" <?php if ($inventory['category'] == 'Condiments and Sauces') echo 'selected'; ?>>Condiments and Sauces</option>
            </select>
        </div>

        <div class="form-group">
            <label for="description">Description (Optional)</label>
            <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($inventory['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="picture">Picture (Optional)</label>
            <input type="file" class="form-control" id="picture" name="picture" accept=".jpg,.jpeg,.png">
            <?php if (!empty($inventory['picture'])): ?>
                <p>Current picture: <img src="upload/<?php echo htmlspecialchars($inventory['picture']); ?>" alt="Image" width="100"></p>
            <?php endif; ?>
        </div>

        <button type="submit" name="update" class="btn btn-primary">Update Inventory</button>
    </form>

    <form action="edit.php?id=<?php echo $inventory_id; ?>" method="POST" style="margin-top: 15px;">
        <button type="submit" name="delete" class="btn delete-button" onclick="return confirm('Are you sure you want to delete this item?');">
            <i class="bi bi-trash"></i> Delete
        </button>
    </form>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
