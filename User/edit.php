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
$user_query = $conn->prepare("SELECT username, user_type FROM user WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$username = $user['username'];
$user_type = $user['user_type'];

// If not logged in as restaurant, restrict access
if ($user_type !== 'Restaurant') {
    echo "Access denied. Only restaurant users can view this page.";
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    // Retrieve and sanitize form data
    $id = $_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $category = $conn->real_escape_string($_POST['category']);
    $expiry_date = $conn->real_escape_string($_POST['expiry_date']);
    $quantity = (int)$_POST['quantity'];

    // Handle file upload
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
        $picture = $_FILES['picture'];
        $upload_dir = 'upload/';
        $upload_file = $upload_dir . basename($picture['name']);

        // Move the uploaded file
        if (move_uploaded_file($picture['tmp_name'], $upload_file)) {
            $picture_filename = basename($picture['name']);
        } else {
            $picture_filename = null;
        }
    } else {
        $picture_filename = null;
    }

    // Update inventory item in the database
    if ($picture_filename) {
        $query = $conn->prepare("UPDATE inventory SET name = ?, category = ?, expiry_date = ?, quantity = ?, picture = ? WHERE id = ? AND donor = ?");
        $query->bind_param("sssssis", $name, $category, $expiry_date, $quantity, $picture_filename, $id, $username);
    } else {
        $query = $conn->prepare("UPDATE inventory SET name = ?, category = ?, expiry_date = ?, quantity = ? WHERE id = ? AND donor = ?");
        $query->bind_param("ssssis", $name, $category, $expiry_date, $quantity, $id, $username);
    }

    if ($query->execute()) {
        // Redirect back to the inventory page on success
        header('Location: inventory.php');
        exit();
    } else {
        echo "<script>alert('Error updating inventory item.');</script>";
    }

    $query->close();
}

// Fetch the inventory item to edit
if (!isset($_GET['id'])) {
    echo "No entity ID specified.";
    exit();
}

$entity_id = $_GET['id'];
$inventory_query = $conn->prepare("SELECT id, name, category, expiry_date, quantity, picture, donor FROM inventory WHERE id = ? AND donor = ?");
$inventory_query->bind_param("is", $entity_id, $username);
$inventory_query->execute();
$inventory_result = $inventory_query->get_result();

if ($inventory_result->num_rows === 0) {
    echo "Entity not found or you do not have permission to edit it.";
    exit();
}

$item = $inventory_result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Inventory Item</title>
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
        .inventory-image {
            max-width: 300px;
            max-height: 300px;
            object-fit: cover;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .buttons-container {
            margin-top: 20px;
            text-align: right;
        }
        .cancel-button {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>Edit Inventory Item</h2>
        
        <form action="edit.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">

            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($item['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" class="form-select" required>
                    <option value="Fruits and Vegetables" <?php echo ($item['category'] === 'Fruits and Vegetables') ? 'selected' : ''; ?>>Fruits and Vegetables</option>
                    <option value="Dairy Products" <?php echo ($item['category'] === 'Dairy Products') ? 'selected' : ''; ?>>Dairy Products</option>
                    <option value="Meat and Fish" <?php echo ($item['category'] === 'Meat and Fish') ? 'selected' : ''; ?>>Meat and Fish</option>
                    <option value="Grains and Cereals" <?php echo ($item['category'] === 'Grains and Cereals') ? 'selected' : ''; ?>>Grains and Cereals</option>
                    <option value="Baked Goods" <?php echo ($item['category'] === 'Baked Goods') ? 'selected' : ''; ?>>Baked Goods</option>
                    <option value="Prepared Foods" <?php echo ($item['category'] === 'Prepared Foods') ? 'selected' : ''; ?>>Prepared Foods</option>
                    <option value="Beverages" <?php echo ($item['category'] === 'Beverages') ? 'selected' : ''; ?>>Beverages</option>
                    <option value="Condiments and Sauces" <?php echo ($item['category'] === 'Condiments and Sauces') ? 'selected' : ''; ?>>Condiments and Sauces</option>
                </select>
            </div>

            <div class="form-group">
                <label for="expiry_date">Expiry Date:</label>
                <input type="date" id="expiry_date" name="expiry_date" class="form-control" value="<?php echo htmlspecialchars($item['expiry_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" class="form-control" value="<?php echo htmlspecialchars($item['quantity']); ?>" required>
            </div>

            <div class="form-group">
            <label for="picture">Picture (Optional)</label>
            <input type="file" class="form-control" id="picture" name="picture" accept=".jpg,.jpeg,.png">
            <?php if (!empty($inventory['picture'])): ?>
                <p>Current picture: <img src="upload/<?php echo htmlspecialchars($inventory['picture']); ?>" alt="Image" width="100"></p>
            <?php endif; ?>
        </div>

            <div class="buttons-container">
                <button type="submit" name="update" class="btn btn-primary">Update Inventory</button>
                <a href="inventory.php" class="btn btn-secondary cancel-button">Cancel</a>
            </div>
        </form>

        <!-- Confirmation and success messages -->
        <div id="confirmation-message" class="alert alert-warning mt-3" style="display: none;">
            Are you sure you want to update this item?
        </div>
        <div id="success-message" class="alert alert-success mt-3" style="display: none;">
            Inventory item updated successfully.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js">
        document.querySelector('form').addEventListener('submit', function(event) {
            event.preventDefault();  // Prevent the default form submission
            var confirmationMessage = document.getElementById('confirmation-message');
            var successMessage = document.getElementById('success-message');
            
            confirmationMessage.style.display = 'block';
            
            if (confirm('Are you sure you want to update this item?')) {
                var formData = new FormData(this);
                fetch('edit.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        confirmationMessage.style.display = 'none';
                        successMessage.style.display = 'block';
                        setTimeout(function() {
                            window.location.href = 'inventory.php';
                        }, 2000);
                    } else {
                        confirmationMessage.style.display = 'none';
                        alert('Failed to update inventory item.');
                    }
                })
                .catch(error => {
                    confirmationMessage.style.display = 'none';
                    alert('An error occurred while updating the inventory item.');
                });
            } else {
                confirmationMessage.style.display = 'none';
            }
        });
    </script>
</body>
</html>

<?php
$inventory_query->close();
$user_query->close();
$conn->close();
?>
