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
    $quantity = $_POST['quantity'];
    $expiry_date = $_POST['expiry_date'];
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
    $insert_query = $conn->prepare("INSERT INTO inventory (name, category, quantity, expiry_date, picture, donor) VALUES (?, ?, ?, ?, ?, ?)");
    $insert_query->bind_param("ssisss", $name, $category, $quantity, $expiry_date, $picture, $username);
    
    // Check if quantity is a valid positive integer
    if (!filter_var($quantity, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        echo "Invalid quantity. Please enter a positive integer.";
        exit();
    }

    if ($insert_query->execute()) {
        // Fetch the last inserted ID (Entity ID)
        $entity_id = $conn->insert_id;
        echo "<script>alert('New inventory added successfully! Entity ID: " . $entity_id . "'); window.location.href = 'inventory.php';</script>";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <title>Add Inventory</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #000;
            padding: 15px;
        }

        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            padding: 30px;
            margin: 50px auto;
        }

        h2 {
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .form-group label {
            font-weight: 500;
            color: #555;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 16px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 10px 15px;
            font-size: 18px;
            border-radius: 8px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .form-group input[type="number"] {
            -moz-appearance: textfield; /* Firefox */
            -webkit-appearance: none; /* Safari */
            appearance: none; /* Other browsers */
        }

        .form-group input[type="file"] {
            padding: 10px;
        }

        .form-group select {
            height: 45px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <!-- Back Button -->
    <div class="d-flex justify-content-start mb-3">
        <a href="inventory.php" class="btn btn-secondary">Back</a>
    </div>

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
                <label for="quantity">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required min="1" step="1">
            </div>
            <div class="form-group">
                <label for="expiry_date">Expiry Date</label>
                <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
            </div>
            <div class="form-group">
    <label for="picture">Picture</label>
    <input type="file" class="form-control" id="picture" name="picture" accept=".jpg,.jpeg,.png" required>
</div>
            <button type="submit" class="btn btn-primary">Add Inventory</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js">
        
        document.getElementById('quantity').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-999]/g, ''); // Allows only digits
        });

    </script>
</body>
</html>
