<?php
// Include the database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();

// Fetch approved requests for Restaurant user
$restaurant_username = $_SESSION['username'];  // Assuming restaurant username is stored in session

// Updated query to fetch the category, NGO's information, and other necessary data by joining with the requests and inventory tables
$query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                 i.category AS item_category, u.username AS ngo_name, u.phone_number AS ngo_phone, 
                 r.requested_quantity, r.status
          FROM requests r
          JOIN inventory i ON r.id = i.id 
          JOIN user u ON r.ngo_name = u.username
          WHERE r.status = 'approved' AND i.donor = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $restaurant_username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Delivery Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link to Poppins font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 0;
        }
        .container {
            margin-top: 50px;
            padding: 20px;
        }
        h2 {
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
        }
        .table {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        table th {
            background-color: #f2f2f2;
            color: #555;
            text-align: center;
            vertical-align: middle;
        }
        table td {
            text-align: center;
            vertical-align: middle;
        }
        table td, table th {
            padding: 15px;
            border: 1px solid #ddd;
        }
        table tbody tr {
            transition: background-color 0.3s ease;
        }
        table tbody tr:hover {
            background-color: #e9ecef; /* Light gray on hover */
        }
        .btn-view {
            background-color: #007bff;
            color: white;
            font-weight: 500;
            border-radius: 5px;
            padding: 8px 12px;
            border: none;
            transition: background-color 0.3s;
        }
        .btn-view:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>Approved Delivery Requests</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>Category</th> <!-- Category column added -->
                    <th>NGO Name</th>
                    <th>NGO Phone Number</th>
                    <th>Requested Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['request_id']) ?></td>
                        <td><?= htmlspecialchars($row['inventory_id']) ?></td>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= htmlspecialchars($row['item_category']) ?></td> <!-- Display the category -->
                        <td><?= htmlspecialchars($row['ngo_name']) ?></td>
                        <td><?= htmlspecialchars($row['ngo_phone']) ?></td>
                        <td><?= htmlspecialchars($row['requested_quantity']) ?></td>
                        <td>
                            <form method="GET" action="receive.php">
                                <input type="hidden" name="request_id" value="<?= htmlspecialchars($row['request_id']) ?>">
                                <button type="submit" class="btn-view">View</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
