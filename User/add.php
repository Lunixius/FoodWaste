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
    echo "Access denied. Only restaurant users can add inventory.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $picture = '';

    // Handle picture upload
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

    // Insert data into the inventory table
    $insert_query = $conn->prepare("INSERT INTO inventory (name, category, description, picture, donor) VALUES (?, ?, ?, ?, ?)");
    $insert_query->bind_param("sssss", $name, $category, $description, $picture, $username);
    
    if ($insert_query->execute()) {
        // Fetch the last inserted ID (Entity ID)
        $entity_id = $conn->insert_id;
        echo "New inventory added successfully! Entity ID: " . $entity_id;
        header("refresh:2; url=inventory.php"); // Redirect after 2 seconds
        exit();
    } else {
        echo "Error: " . $insert_query->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Add Inventory</title>
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
    </nav>

    <div class="container">
        <h2>Add Inventory Item</h2>
        <form action="add.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select class="form-control" id="category" name="category" required>
                    <option value="Fruits and Vegetables">Fruits and Vegetables</option>
                    <option value="Dairy Products">Dairy Products</option>
                    <option value="Meat and Fish">Meat and Fish</option>
                    <option value="Grains and Cereals">Grains and Cereals</option>
                    <option value="Baked Goods">Baked Goods</option>
                    <option value="Prepared Foods">Prepared Foods</option>
                    <option value="Beverages">Beverages</option>
                    <option value="Condiments and Sauces">Condiments and Sauces</option>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description (Optional)</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <div class="form-group">
                <label for="picture">Picture (Optional)</label>
                <input type="file" class="form-control" id="picture" name="picture" accept=".jpg,.jpeg,.png">
            </div>
            <button type="submit" class="btn btn-primary">Add Inventory</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
