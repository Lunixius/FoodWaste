<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcN/tWtIaxVXM" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .container, .container-lg, .container-md, .container-sm, .container-xl, .container-xxl {
            max-width: 100%!important;
        }
        .navbar-expand-lg .navbar-nav .dropdown-menu {
            position: absolute;
            width: 900%;
            padding: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        .navbar-expand-lg .navbar-nav {
            flex-direction: row;
            justify-content: center;
        }
        .nav-item {
            margin-right: 30px;
        }
        .category-item {
            cursor: pointer;
        }
        .row {
            --bs-gutter-x: 1.5rem;
            --bs-gutter-y: 0;
            display: flex;
            flex-wrap: inherit!important;
            margin-top: calc(var(--bs-gutter-y) * -1);
            margin-right: calc(var(--bs-gutter-x) * -.5);
            margin-left: 0px!important;
        }
        .row>* {
            flex-shrink: 0;
            width: 100%;
            max-width: 100%;
            padding-right: calc(var(--bs-gutter-x) * .5);
            padding-left: calc(var(--bs-gutter-x) * .5);
            margin-top: var(--bs-gutter-y);
            padding: 0;
        }
        .col-sm {
            flex: 1 0 0%;
            height: 420px;
        }
        .cart-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            padding: 0 15px;
        }
        .cart-icon i {
            font-size: 30px;
        }
        .navbar-expand-lg .navbar-nav {
            flex-direction: row;
            margin-left: auto;
            margin-right: auto;
        }
        .logo-column {
            display: flex;
            align-items: center;
        }
        .logo-column img {
            width: 100px;
            height: auto;
        }
        .cart-icon i {
            font-size: 25px;
            color: lightcoral;
            transition: font-size 0.3s;
        }
        .cart-icon i:hover {
            font-size: 28px;
        }
        .profile-icon {
            display: flex;
            align-items: center;
            margin-left: 15px;
        }
        .profile-icon i {
            font-size: 25px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Container for the menu -->
    <div class="container-xxl">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <div class="logo-column">
                    <img src="Picture/Retail_Logo.png" alt="Logo">
                </div>
                <div class="collapse navbar-collapse justify-content-center" id="navbarNavDropdown">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="inventory.php">Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="donation.php">Donation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contacts.php">Contacts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="delivery.php">Delivery</a>
                        </li>
                    </ul>
                </div>
                <div class="cart-icon">
                    <a class="nav-link" href="view_cart.php">
                        <i class="fas fa-cart-shopping"></i>
                    </a>
                </div>
                <div class="profile-icon dropdown">
                    <i class="fa-solid fa-user" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false"></i>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown" id="profileMenu">
                        <!-- This will be populated by JavaScript based on login status -->
                    </ul>
                </div>
            </div>
        </nav>
    </div>

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
                            <li><a class="dropdown-item" href="#" onclick="logout()">Sign Out</a></li>
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
            // Clear session and redirect to index.php
            document.cookie = 'PHPSESSID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            window.location.href = "user_login.php";
    }

    </script>
</body>
</html>
