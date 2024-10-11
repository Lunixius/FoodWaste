<?php
// Include the database connection and session
$conn = new mysqli('localhost', 'root', '', 'foodwaste');
session_start();

// Ensure the user's role is defined
$role = $_SESSION['role'] ?? null; // Get user role from session, set to null if not set

// Fetch the request details based on request_id
if (isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];

    // Updated query to join with inventory to fetch restaurant info
    $query = "SELECT r.request_id, r.id AS inventory_id, r.name AS item_name, 
                i.donor AS restaurant_username, u.phone_number AS restaurant_phone, 
                r.requested_quantity, r.receive_time, r.address, 
                r.restaurant_confirmed, r.ngo_confirmed,
                r.receive_method  -- Add this line
          FROM requests r
          JOIN inventory i ON r.id = i.id
          JOIN user u ON i.donor = u.username
          WHERE r.request_id = ?";


    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}

// Handle form submission for confirmation and address update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $receive_date = $_POST['receive_date'];
    $receive_time = $_POST['receive_time'];
    $address = $_POST['address'];
    // Removed latitude and longitude
    // $latitude = $_POST['latitude'];
    // $longitude = $_POST['longitude'];

    // Combine date and time into a single variable
    $combined_datetime = "$receive_date $receive_time";

    // Update request with pickup info and confirmation status
    $update_query = "UPDATE requests SET receive_time = ?, address = ?";

    // Update confirmation based on the role
    if ($role === 'restaurant') {
        $update_query .= ", restaurant_confirmed = 1";
    } elseif ($role === 'ngo') {
        $update_query .= ", ngo_confirmed = 1";
    }

    $update_query .= " WHERE request_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssi", $combined_datetime, $address, $request_id);
    $update_stmt->execute();

    // Redirect to confirm.php to view the order status
    header("Location: confirm.php"); 
    exit;
}

// Handle cancellation of confirmation
if (isset($_POST['cancel_confirmation'])) {
    // Check which user is canceling the confirmation
    if ($role === 'restaurant') {
        $cancel_query = "UPDATE requests SET restaurant_confirmed = 0 WHERE request_id = ?";
    } elseif ($role === 'ngo') {
        $cancel_query = "UPDATE requests SET ngo_confirmed = 0 WHERE request_id = ?";
    }

    $cancel_stmt = $conn->prepare($cancel_query);
    $cancel_stmt->bind_param("i", $request_id);
    $cancel_stmt->execute();

    // Refresh the page to reflect the updated status
    header("Location: receive.php?request_id=$request_id");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup Information</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e9f5f5;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
        }
        #address-input {
            height: 45px;
            border-radius: 8px;
            border: 1px solid #ccc;
            padding: 10px 15px;
        }
        #map {
            height: 400px;
            width: 100%;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h2 class="mb-4">Pickup Information</h2>

        <!-- Display request details -->
        <?php if (isset($row)): ?>
            <div class="mb-3">
                <h5>Item: <?php echo $row['item_name']; ?></h5>
                <p>Request ID: <?php echo $row['request_id']; ?></p>
                <p>Inventory ID: <?php echo $row['inventory_id']; ?></p>
                <p>Requested Quantity: <?php echo $row['requested_quantity']; ?></p>
                <p>Restaurant Name: <?php echo $row['restaurant_username']; ?></p>
                <p>Restaurant Phone Number: <?php echo $row['restaurant_phone']; ?></p>
            </div>
        <?php else: ?>
            <p>Request details not found!</p>
        <?php endif; ?>

        <!-- Form for pickup time and address -->
        <form method="POST">
        <div class="mb-3">
           <label for="receive_date" class="form-label">Preferred Date</label>
            <input type="date" id="receive_date" name="receive_date" class="form-control" value="<?php echo isset($row['receive_time']) ? explode(' ', $row['receive_time'])[0] : ''; ?>" required>
        </div>

        <div class="mb-3">
            <label for="receive_time" class="form-label">Preferred Time</label>
            <input type="time" id="receive_time" name="receive_time" class="form-control" value="<?php echo isset($row['receive_time']) ? explode(' ', $row['receive_time'])[1] : ''; ?>" required>
        </div>

        <div class="mb-3">
            <label for="receive_method" class="form-label">Receive Method</label>
            <select id="receive_method" name="receive_method" class="form-select" required>
                <option value="" disabled>Select Receive Method</option>
                <option value="Delivery" <?php echo (isset($row['receive_method']) && $row['receive_method'] == 'Delivery') ? 'selected' : ''; ?>>Delivery</option>
                <option value="Pickup" <?php echo (isset($row['receive_method']) && $row['receive_method'] == 'Pickup') ? 'selected' : ''; ?>>Pickup</option>
            </select>
        </div>

        <div class="input-group mb-3">
            <input id="address-input" type="text" class="form-control" name="address" placeholder="Enter your pickup address" value="<?php echo $row['address'] ?? ''; ?>" required>
            <button id="search-address-btn" class="btn btn-outline-secondary" type="button">Search</button>
        </div>
        <div id="map"></div>

        <!-- Conditional button display -->
        <div class="mb-3">
            <?php if ($row['restaurant_confirmed'] == 1 || $row['ngo_confirmed'] == 1): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="cancel_confirmation" value="1">
                    <button type="submit" class="btn btn-secondary">Cancel Confirmation</button>
                </form>
            <?php else: ?>
                <button type="submit" class="btn btn-primary">Confirm Pickup</button>
            <?php endif; ?>
    </div>
    </form>

    </div>

    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDbwOcep_rhK8dH77TJlPR7VuOZyN3OY7A&libraries=places&callback=initMap" async defer></script>
    <script>
    function initMap() {
        const map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: 0, lng: 0},
            zoom: 2
        });

        const input = document.getElementById('address-input');
        const searchButton = document.getElementById('search-address-btn');

        const autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);

        searchButton.addEventListener('click', () => {
            const address = input.value;
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({'address': address}, (results, status) => {
                if (status === 'OK') {
                    const location = results[0].geometry.location;
                    map.setCenter(location);
                    new google.maps.Marker({
                        position: location,
                        map: map
                    });
                } else {
                    alert('Geocode was not successful for the following reason: ' + status);
                }
            });
        });
    }
    </script>

</body>
</html>
