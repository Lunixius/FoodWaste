<?php
// Include database connection
$conn = new mysqli('localhost', 'root', '', 'foodwaste');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session to check for admin login
session_start();

// Fetch inventory items from the database
$inventory_result = $conn->query("SELECT * FROM inventory");

// Close the connection after fetching
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            margin-top: 50px;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #4CAF50;
        }

        table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse; /* Ensure borders are not doubled */
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

table th, table td {
    text-align: center;
    padding: 12px;
    border: 1px solid #ddd; /* Add a solid border */
}

th {
    background-color: #f2f2f2;
    font-weight: 500;
}

tr {
    transition: background-color 0.3s ease;
}

tr:hover {
    background-color: #f5f5f5;
}

td {
    background-color: #fff;
    border-bottom: 1px solid #ddd;
}

td:last-child {
    border-right: 0;
}

        /* Mobile responsive design */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            table {
                font-size: 14px;
            }

            table th, table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'admin_navbar.php'; ?>

    <div class="container">
        <h2>View Inventory</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Expiry Date</th>
                    <th>Donor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($inventory_result && $inventory_result->num_rows > 0) {
                    while ($row = $inventory_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['expiry_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['donor']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No inventory items found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
