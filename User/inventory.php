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

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Check for associated requests
    $check_requests_query = $conn->prepare("SELECT COUNT(*) FROM requests WHERE id = ?");
    $check_requests_query->bind_param("i", $delete_id);
    $check_requests_query->execute();
    $check_requests_query->bind_result($request_count);
    $check_requests_query->fetch();
    $check_requests_query->close();

    if ($request_count > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete item. There are active requests associated with it.']);
        exit();
    }

    $delete_query = $conn->prepare("DELETE FROM inventory WHERE id = ? AND donor = ?");
    $delete_query->bind_param("is", $delete_id, $username);
    
    if ($delete_query->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $delete_query->close();
    exit();  // End script after processing delete request
}

// Fetch all inventory items with date_created and last_modified
$inventory_query = $conn->prepare("SELECT id, name, category, expiry_date, quantity, picture, donor, date_created, last_modified FROM inventory WHERE donor = ?");
$inventory_query->bind_param("s", $username);
$inventory_query->execute();
$inventory_result = $inventory_query->get_result();

// Close database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <title>Inventory</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e9f5f5;
        }
        .navbar {
            background-color: #000;
            padding: 15px;
        }
        .container {
            margin-top: 50px;
        }
        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-top: 20px;
            border-radius: 5px;
            overflow: hidden;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            text-align: center;
            vertical-align: middle;
            padding: 10px; /* Adds spacing for comfort */
            border: 2px solid #007bff; /* Bolder borders with a nice color */
            font-size: 14px; /* Adjusts text size for readability */
        }

        table th {
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            background-color: #343a40; /* Dark background color for contrast */
            color: #ffffff; /* White text for visibility */
            border: 2px solid #000; /* Bold borders */
            padding: 10px;
        }

        table td {
            text-align: center;
            vertical-align: middle;
            border: 2px solid #ddd; /* Light borders for cells */
            padding: 10px;
        }

        .add-button {
            float: right;
            margin-bottom: 20px;
        }
        .inventory-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 5px;
        }
        .filter-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }
        .search-box {
            flex: 3;
        }
        .filter-box {
            flex: 1;
        }
        .btn-spacing {
            margin-right: 10px;
        }

        #inventory-table th {
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            background-color: #f8f9fa; /* Light background color for header */
            color: #000000; /* Black text color */
            border: 2px solid #ddd; /* Light border for header */
            padding: 10px;
        }
        
        /* Full-screen modal image */
        #image-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
        }
        #image-modal img {
            display: block;
            margin: auto;
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }
        #delete-message {
            display: none;
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>Restaurant Inventory</h2>

        <!-- Filter and Search Bar -->
        <div class="filter-bar">
            <select id="category-filter" class="form-select filter-box" style="width: 200px;">
                <option value="">All Categories</option>
                <option value="Fruits and Vegetables">Fruits and Vegetables</option>
                <option value="Dairy Products">Dairy Products</option>
                <option value="Meat and Fish">Meat and Fish</option>
                <option value="Grains and Cereals">Grains and Cereals</option>
                <option value="Baked Goods">Baked Goods</option>
                <option value="Prepared Foods">Prepared Foods</option>
                <option value="Beverages">Beverages</option>
                <option value="Condiments and Sauces">Condiments and Sauces</option>
            </select>
            <a href="add.php" class="btn btn-success add-button">Add Item</a>
        </div>

        <table class="table table-bordered" id="inventory-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Expiry Date</th>
                    <th>Quantity</th>
                    <th>Picture</th>
                    <th>Donor</th>
                    <th>Date Created</th>
                    <th>Last Modified</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $inventory_result->fetch_assoc()): ?>
                    <tr class="inventory-row" data-category="<?php echo htmlspecialchars($row['category']); ?>">
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['expiry_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td>
                            <?php if (!empty($row['picture'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($row['picture']); ?>" alt="Image" class="inventory-image">
                            <?php else: ?>
                                No picture
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['donor']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_created']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_modified']); ?></td>
                        <td>
                            <a href="info.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm btn-spacing">View details</a>
                            <?php if ($row['donor'] == $username): ?>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm btn-spacing">Edit</a>
                                <form action="" method="POST" class="d-inline">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm btn-spacing" onclick="return confirm('Are you sure you want to delete this inventory item?');">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($inventory_result->num_rows == 0): ?>
                    <tr>
                        <td colspan="10" class="text-center">No inventory added yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="image-modal" onclick="this.style.display='none'">
        <img src="" alt="Full Image" id="full-image">
    </div>

    <div id="delete-message"></div>

    <script>
        // Handle category filtering
        document.getElementById('category-filter').addEventListener('change', function () {
            const selectedCategory = this.value;
            const rows = document.querySelectorAll('.inventory-row');

            rows.forEach(row => {
                if (selectedCategory === "" || row.dataset.category === selectedCategory) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });

        // Handle image click for full-screen view
        const images = document.querySelectorAll('.inventory-image');
        images.forEach(img => {
            img.addEventListener('click', function () {
                const modal = document.getElementById('image-modal');
                const fullImage = document.getElementById('full-image');
                fullImage.src = this.src;
                modal.style.display = 'block';
            });
        });

        // AJAX deletion handler
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the form from submitting normally

        const deleteId = this.querySelector('input[name="delete_id"]').value;

        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(`delete_id=${deleteId}`)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Successfully deleted
                alert('Item deleted successfully!');
                location.reload(); // Reload the page to refresh the inventory list
            } else {
                // Show error message
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

    </script>
</body>
</html>


