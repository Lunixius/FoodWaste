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
                    r.requested_quantity, r.receive_time, r.address, r.latitude, r.longitude, 
                    r.restaurant_confirmed, r.ngo_confirmed
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
    $receive_date = $_POST['receive_date'];
    $receive_time = $_POST['receive_time'];
    $address = $_POST['address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Combine date and time into a single variable
    $combined_datetime = "$receive_date $receive_time";

    // Update request with pickup info and confirmation status
    $update_query = "UPDATE requests SET receive_time = ?, address = ?, latitude = ?, longitude = ?";

    // Update confirmation based on the role
    if ($role === 'restaurant') {
        $update_query .= ", restaurant_confirmed = 1";
    } elseif ($role === 'ngo') {
        $update_query .= ", ngo_confirmed = 1";
    }

    $update_query .= " WHERE request_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssssi", $combined_datetime, $address, $latitude, $longitude, $request_id);
    $update_stmt->execute();

    // Refresh the page to reflect the updated status
    header("Location: receive.php?request_id=$request_id"); 
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
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .status.confirmed {
            background-color: #d4edda; /* Light green */
            color: #155724; /* Dark green */
        }
        .status.pending {
            background-color: #fff3cd; /* Light orange */
            color: #856404; /* Dark orange */
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
                <label for="receive_date" class="form-label">Preferred Pickup Date</label>
                <input type="date" id="receive_date" name="receive_date" class="form-control" value="<?php echo isset($row['receive_time']) ? explode(' ', $row['receive_time'])[0] : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="receive_time" class="form-label">Preferred Pickup Time</label>
                <input type="time" id="receive_time" name="receive_time" class="form-control" value="<?php echo isset($row['receive_time']) ? explode(' ', $row['receive_time'])[1] : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="receive_method" class="form-label">Receive Method</label>
                <select id="receive_method" name="receive_method" class="form-select" required>
                    <option value="" disabled>Select Receive Method</option>
                    <option value="Pickup" <?php echo isset($row['address']) && !empty($row['address']) ? 'selected' : ''; ?>>Pickup</option>
                    <option value="Delivery">Delivery</option>
                </select>
            </div>

            <div class="input-group mb-3">
                <input id="address-input" type="text" class="form-control" name="address" placeholder="Enter your pickup address" value="<?php echo $row['address'] ?? ''; ?>">
                <button id="search-address-btn" class="btn btn-outline-secondary" type="button">Search</button>
            </div>
            <div id="map"></div>

            <input type="hidden" id="latitude" name="latitude" value="<?php echo $row['latitude'] ?? ''; ?>">
            <input type="hidden" id="longitude" name="longitude" value="<?php echo $row['longitude'] ?? ''; ?>">

            <!-- Two separate status bars -->
            <div class="status <?php echo $row['restaurant_confirmed'] ? 'confirmed' : 'pending'; ?>">
                <strong>Restaurant Status:</strong>
                <?php if ($row['restaurant_confirmed']): ?>
                    Confirmed.
                <?php else: ?>
                    Waiting for restaurant confirmation.
                <?php endif; ?>
            </div>

            <div class="status <?php echo $row['ngo_confirmed'] ? 'confirmed' : 'pending'; ?>">
                <strong>NGO Status:</strong>
                <?php if ($row['ngo_confirmed']): ?>
                    Confirmed.
                <?php else: ?>
                    Waiting for NGO confirmation.
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Confirm Pickup</button>
            </div>
        </form>

        <!-- Optional cancellation button -->
        <form method="POST">
            <input type="hidden" name="cancel_confirmation" value="1">
            <button type="submit" class="btn btn-danger">Cancel Confirmation</button>
        </form>
    </div>

    <!-- Google Maps JavaScript API -->
    <script>
        let map, marker;

        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: -34.397, lng: 150.644 },
                zoom: 8,
            });

            const addressInput = document.getElementById("address-input");
            const searchButton = document.getElementById("search-address-btn");

            // Initialize autocomplete for the address input
            const autocomplete = new google.maps.places.Autocomplete(addressInput);
            autocomplete.bindTo("bounds", map);

            // When the user selects an address from the dropdown, populate the address fields
            autocomplete.addListener("place_changed", function () {
                const place = autocomplete.getPlace();
                if (!place.geometry) return;

                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }

                // Set the latitude and longitude values
                document.getElementById("latitude").value = place.geometry.location.lat();
                document.getElementById("longitude").value = place.geometry.location.lng();

                // Add a marker for the selected location
                if (marker) marker.setMap(null);
                marker = new google.maps.Marker({
                    position: place.geometry.location,
                    map: map,
                    title: place.name,
                });
            });

            // Handle search button click
            searchButton.addEventListener("click", function () {
                const address = addressInput.value;
                if (address) {
                    autocomplete.set("place", null);
                    autocomplete.set("input", address);
                }
            });
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDbwOcep_rhK8dH77TJlPR7VuOZyN3OY7A&libraries=places&callback=initMap" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
