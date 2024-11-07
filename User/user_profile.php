<?php
session_start();

// Database connection parameters
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "foodwaste";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verify that the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Prepare and execute the query to fetch user data
$query = $conn->prepare("SELECT id, username, phone_number, email, user_type, password FROM user WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Check if user data was found
if (!$user) {
    echo "<p>Error: User not found.</p>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if updating profile information
    if (isset($_POST['save_changes'])) {
        $username = trim($_POST['username']);
        $phone_number = trim($_POST['phone_number']);
        $email = trim($_POST['email']);

        // Validate that fields are not empty
        if (empty($username) || empty($phone_number) || empty($email)) {
            $_SESSION['error_message'] = "Username, Email, and Phone Number cannot be empty!";
        } else {
            $update_query = $conn->prepare("UPDATE user SET username = ?, phone_number = ?, email = ? WHERE id = ?");
            $update_query->bind_param("sssi", $username, $phone_number, $email, $user_id);

            if ($update_query->execute()) {
                $_SESSION['success_message'] = "Profile updated successfully!";
            } else {
                $_SESSION['error_message'] = "Error updating profile!";
            }
        }
        header("Location: user_profile.php");
        exit();
    }

    // Check if changing password
    if (isset($_POST['change_password'])) {
        // Existing password change logic here
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            position: relative; /* Added for positioning */
        }
        .container {
            margin-top: 50px;
            max-width: 800px;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group label {
            font-weight: 500;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .alert {
            font-size: 14px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn {
            margin-right: 10px;
        }
        /* Back button positioning */
        .back-button-container {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .back-button {
            text-decoration: none;
            color: #007bff; /* Change color as needed */
            font-weight: 600;
        }
        /* Input field design */
        input[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
    <a href="user_homepage.php" class="back-button"><i class="bi bi-arrow-right-circle"></i> Back to Homepage</a>
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
                <label for="user_id" class="form-label">ID</label>
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

            <button type="submit" name="save_changes" class="btn btn-primary">Save Changes</button>
        </form>

        <form method="post" action="user_profile.php" class="mt-4">
            <div class="form-group">
                <label>Old Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" name="old_password" id="old_password" required>
                    <span class="input-group-text" onclick="togglePassword('old_password')"><i class="bi bi-eye"></i></span>
                </div>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" name="new_password" id="new_password" required>
                    <span class="input-group-text" onclick="togglePassword('new_password')"><i class="bi bi-eye"></i></span>
                </div>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" name="confirm_new_password" id="confirm_new_password" required>
                    <span class="input-group-text" onclick="togglePassword('confirm_new_password')"><i class="bi bi-eye"></i></span>
                </div>
            </div>

            <button type="submit" name="change_password" class="btn btn-secondary">Change Password</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle function
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
