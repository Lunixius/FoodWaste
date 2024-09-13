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
    echo "Access denied. Only restaurant users can view this page.";
    exit();
}

// Fetch all inventory items with date_created and last_modified
$inventory_query = $conn->prepare("SELECT id, name, category, description, picture, donor, date_created, last_modified FROM inventory WHERE donor = ?");
$inventory_query->bind_param("s", $username);
$inventory_query->execute();
$inventory_result = $inventory_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Inventory</title>
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
        .add-button {
            float: right;
            margin-bottom: 20px;
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
            gap: 15px; /* Adds space between the search bar, filter, and button */
        }

        .search-box {
            flex: 3; /* Occupies more width */
        }

        .filter-box {
            flex: 1; /* Occupies less width than search box */
        }

        .add-button {
            margin-left: 10px;
            flex: 1;
        }

            /* Style for images in the table */
        .inventory-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Full-screen modal image */
        #image-modal {
            display: none; /* Hidden by default */
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
        <h2>Restaurant Inventory</h2>

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

        <a href="add.php" class="btn btn-success add-button">Add Entity</a>

        <table class="table table-bordered" id="inventory-table">
    <thead>
        <tr>
            <th>Entity ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Description</th>
            <th>Picture</th>
            <th>Donor</th>
            <th>Date Created</th>
            <th>Last Modified</th>
            <th>Check</th> <!-- New "Check" column -->
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $inventory_result->fetch_assoc()): ?>
            <tr class="inventory-row" data-category="<?php echo htmlspecialchars($row['category']); ?>">
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td>
                    <?php if (!empty($row['picture'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($row['picture']); ?>" alt="Image" class="inventory-image clickable-image">
                    <?php else: ?>
                        No picture
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['donor']); ?></td>
                <td><?php echo htmlspecialchars($row['date_created']); ?></td>
                <td><?php echo htmlspecialchars($row['last_modified']); ?></td>
                <td>
                    <a href="info.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-info btn-sm">Info</a> <!-- New Info button -->
                </td>
                <td>
                    <?php if ($row['donor'] == $username): ?>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($inventory_result->num_rows == 0): ?>
            <tr>
                <td colspan="10" class="text-center">No inventory added yet.</td> <!-- Updated colspan to 10 -->
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
                var category = row.cells[2].innerText.toLowerCase();
                var description = row.cells[3].innerText.toLowerCase();
                if (name.includes(searchValue) || category.includes(searchValue) || description.includes(searchValue)) {
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
                var name = row.cells[1].innerText.toLowerCase(); // Name is in the second cell (index 1)
                if (name.includes(searchValue)) {
                    row.style.display = '';  // Show rows where the name matches the search term
                } else {
                    row.style.display = 'none';  // Hide rows that don't match
                }
            );
        });


        // Category Filter Functionality
        document.getElementById('category-filter').addEventListener('change', function() {
            var selectedCategory = this.value.toLowerCase();
            var rows = document.querySelectorAll('.inventory-row');
            rows.forEach(function(row) {
                var category = row.getAttribute('data-category').toLowerCase();
                if (selectedCategory === '' || category === selectedCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // JavaScript to handle image click and open modal
        document.querySelectorAll('.clickable-image').forEach(function(image) {
            image.addEventListener('click', function() {
                var src = this.getAttribute('src');
                var modal = document.getElementById('image-modal');
                var modalImg = modal.querySelector('img');
                modalImg.setAttribute('src', src);
                modal.style.display = 'block';
            });
        });

        // Close the modal when clicking outside the image or pressing "ESC"
        document.addEventListener('click', function(event) {
            var modal = document.getElementById('image-modal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                document.getElementById('image-modal').style.display = 'none';
            }
        });

    </script>
</body>
</html>
