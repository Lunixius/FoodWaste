<?php
session_start();

// Database connection parameters
$servername = "localhost";
$db_username = "root";  // Replace with your database username
$db_password = "";  // Replace with your database password
$dbname = "foodwaste";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT id, username, phone_number, email, user_type FROM user WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p>Error: User not found.</p>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update only fields that can be modified
    $username = $_POST['username'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];

    $update_query = $conn->prepare("UPDATE user SET username = ?, phone_number = ?, email = ? WHERE id = ?");
    $update_query->bind_param("sssi", $username, $phone_number, $email, $user_id);

    if ($update_query->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating profile!";
    }
    header("Location: user_profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">User Profile</a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="user_homepage.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inventory.php">Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacts.php">Contacts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="delivery.php">Delivery</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">Profile Information</h2>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success" role="alert">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <form method="post" action="user_profile.php">
            <!-- Hashtag ID (Non-modifiable) -->
            <div class="form-group">
                <label for="user_id" class="form-label">Hashtag ID</label>
                <input type="text" class="form-control" id="user_id" value="<?php echo htmlspecialchars('#' . $user['id']); ?>" readonly>
            </div>

            <!-- Username (Editable with Change Button) -->
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>

            <!-- Email (Editable with Change Button) -->
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>

            <!-- Phone Number (Editable with Change Button) -->
            <div class="form-group">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
            </div>

            <!-- Password (Private with Change Button) -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <button type="button" class="btn btn-primary" onclick="location.href='change_password.php';">Change Password</button>
            </div>

            <!-- User Type (Non-modifiable) -->
            <div class="form-group">
                <label for="user_type" class="form-label">User Type</label>
                <input type="text" class="form-control" id="user_type" value="<?php echo htmlspecialchars($user['user_type']); ?>" readonly>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
