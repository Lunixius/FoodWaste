<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            font-family: 'Lato', sans-serif;
        }

        .navbar {
            background-color: #000; /* Black background */
            padding: 15px;
            width: 100%;
        }

        .navbar-nav {
            flex-direction: row;
            margin-left: auto;
            margin-right: auto;
        }

        .nav-item {
            margin-right: 30px;
        }

        .profile-icon {
            position: absolute;
            right: 20px;
            top: 15px;
        }

        .profile-icon i {
            font-size: 25px;
            color: white;
            cursor: pointer;
        }

        .profile-icon .dropdown-menu {
            right: 0;
            left: auto;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <div class="collapse navbar-collapse justify-content-center" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="user_homepage.php">Home</a>
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

            <!-- Profile Icon -->
            <div class="profile-icon dropdown">
                <i class="fa-solid fa-user" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false"></i>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown" id="profileMenu">
                    <!-- Content will be updated by JavaScript -->
                </ul>
            </div>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const profileMenu = document.getElementById('profileMenu');

            // Check login status and update profile menu
            fetch('check_login_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.loggedIn) {
                        profileMenu.innerHTML = `
                            <li><a class="dropdown-item" href="profile.php">${data.username}</a></li>
                            <li><a class="dropdown-item" href="#" onclick="logout()">Log Out</a></li>
                        `;
                    } else {
                        profileMenu.innerHTML = `
                            <li><a class="dropdown-item" href="login.php">Log In</a></li>
                        `;
                    }
                })
                .catch(error => console.error('Error checking login status:', error));
        });

        function logout() {
            // Clear session and redirect to login page
            document.cookie = 'PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            window.location.href = "user_login.php";
        }
    </script>
</body>
</html>
