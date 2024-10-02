<?php
// Include the database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();

// Fetch approved requests for NGO user
$ngo_username = $_SESSION['username'];  // Assuming username is stored in session for logged-in NGO

// Updated query to fetch restaurant's information by joining with the inventory table
$query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                 i.donor AS restaurant_username, u.phone_number AS restaurant_phone, 
                 r.requested_quantity, r.status
          FROM requests r
          JOIN inventory i ON r.id = i.id
          JOIN user u ON i.donor = u.username
          WHERE r.status = 'approved' AND r.username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $ngo_username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGO Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .disabled-btn {
            pointer-events: none;
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h2>Approved Requests</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Inventory ID</th>
                    <th>Item Name</th>
                    <th>Restaurant Username</th>
                    <th>Phone Number</th>
                    <th>Requested Quantity</th>
                    <th>Receive Method</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['request_id']) ?></td>
                        <td><?= htmlspecialchars($row['inventory_id']) ?></td>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= htmlspecialchars($row['restaurant_username']) ?></td>
                        <td><?= htmlspecialchars($row['restaurant_phone']) ?></td>
                        <td><?= htmlspecialchars($row['requested_quantity']) ?></td>
                        <td>
                            <!-- Dropdown for selecting Receive Method -->
                            <select class="form-select receive-method" data-request-id="<?= $row['request_id'] ?>">
                                <option value="">Select Method</option>
                                <option value="delivery">Delivery</option>
                                <option value="pickup">Pickup</option>
                            </select>
                        </td>
                        <td>
                            <!-- View Button that redirects to pickup_info.php or is disabled based on selection -->
                            <form method="GET" action="pickup_info.php">
                                <input type="hidden" name="request_id" value="<?= htmlspecialchars($row['request_id']) ?>">
                                <button type="submit" class="btn btn-info action-btn disabled-btn" disabled>View</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript to handle receive method selection and enable/disable buttons
        document.querySelectorAll('.receive-method').forEach(select => {
            select.addEventListener('change', function() {
                const requestId = this.getAttribute('data-request-id');
                const selectedMethod = this.value;
                const button = this.closest('tr').querySelector('.action-btn');

                if (selectedMethod === 'delivery') {
                    button.classList.remove('disabled-btn');
                    button.disabled = false;
                } else if (selectedMethod === 'pickup') {
                    button.classList.add('disabled-btn');
                    button.disabled = true;
                } else {
                    button.classList.add('disabled-btn');
                    button.disabled = true;
                }
            });
        });
    </script>
</body>
</html>
