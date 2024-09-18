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

// If not logged in as NGO, restrict access
if ($user_type !== 'NGO') {
    echo "Access denied. Only NGO users can view this page.";
    exit();
}

// Fetch all inventory items with date_created and last_modified
$inventory_query = $conn->prepare("SELECT id, name, category, expiry_date, quantity, picture, donor, date_created, last_modified FROM inventory");
$inventory_query->execute();
$inventory_result = $inventory_query->get_result();

// Close the database connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Available Inventory</title>
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
        table {
            width: 100%;
            margin-top: 20px;
        }
        table th, table td {
            text-align: center;
            vertical-align: middle;
        }
        .inventory-image {
            max-width: 100px;
            max-height: 100px;
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
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>Available Inventory for NGOs</h2>

        <!-- Filter and Search Bar -->
        <div class="filter-bar">
            <input type="text" id="search" class="form-control search-box" placeholder="Search by name...">
            <button id="search-btn" class="btn btn-primary" style="margin-top: 10px;">Search...</button>
    
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
                        // Inside the inventory table rows
                        <td>
                        <form id="requestForm-<?php echo $row['id']; ?>" action="request.php" method="POST">
                            <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
                            <input type="number" name="requested_quantity" min="1" max="<?php echo $row['quantity']; ?>" placeholder="Enter quantity" required>
                            <button type="button" class="btn btn-warning btn-sm btn-spacing" onclick="confirmRequest(<?php echo $row['id']; ?>)">Request</button>
                        </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($inventory_result->num_rows == 0): ?>
                    <tr>
                        <td colspan="10" class="text-center">No inventory available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Full-screen image modal -->
        <div id="image-modal">
            <img src="" alt="Full Screen Image">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search Functionality
        document.getElementById('search').addEventListener('input', function() {
            var searchValue = this.value.toLowerCase();
            var rows = document.querySelectorAll('.inventory-row');
            rows.forEach(function(row) {
                var name = row.cells[1].innerText.toLowerCase();
                if (name.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Search by name when "Search..." button is clicked
        document.getElementById('search-btn').addEventListener('click', function() {
            var searchValue = document.getElementById('search').value.toLowerCase();
            var rows = document.querySelectorAll('.inventory-row');
            rows.forEach(function(row) {
                var name = row.cells[1].innerText.toLowerCase();
                if (name.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Category Filter Functionality
        document.getElementById('category-filter').addEventListener('change', function() {
            var selectedCategory = this.value.toLowerCase();
            var rows = document.querySelectorAll('.inventory-row');
            rows.forEach(function(row) {
                var category = row.dataset.category.toLowerCase();
                if (selectedCategory === '' || category === selectedCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Full-screen image viewer
        document.querySelectorAll('.inventory-image').forEach(function(img) {
            img.addEventListener('click', function() {
                var src = this.getAttribute('src');
                var modal = document.getElementById('image-modal');
                modal.querySelector('img').setAttribute('src', src);
                modal.style.display = 'block';
            });
        });

        document.getElementById('image-modal').addEventListener('click', function() {
            this.style.display = 'none';
        });

        // Confirm Request Function
        function confirmRequest(itemId) {
            var form = document.getElementById('requestForm-' + itemId);
            var quantityInput = form.querySelector('input[name="requested_quantity"]');
            var requestedQuantity = quantityInput.value;

            if (requestedQuantity <= 0 || requestedQuantity > quantityInput.max) {
                alert("Invalid quantity.");
                return;
            }

            if (confirm("Are you sure you want to request this quantity?")) {
                form.submit();
            }
        }
    </script>
</body>
</html>


