<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 85%;
            margin: 40px auto;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 40px;
            font-weight: 600;
        }

        .cards {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 40px;
            padding: 20px;
        }

        .card {
            background-color: #fff;
            width: 300px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        .card h2 {
            color: #007bff;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
        }

        .card a {
            text-decoration: none;
            color: #ff5722;
            font-size: 18px;
            font-weight: 500;
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .card a:hover {
            background-color: #0056b3;
            color: #f1f1f1;
        }

        .card a i {
            margin-left: 8px;
        }

        .navbar {
            background-color: #343a40;
        }

        .navbar-brand, .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #ff5722 !important;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .cards {
                gap: 20px;
            }
            
            .card {
                width: 90%;
                margin: 0 auto;
            }

            h1 {
                font-size: 28px;
            }
        }

    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'admin_navbar.php'; ?>

    <!-- Main Content -->
    <div class="container">
        <h1>Admin Dashboard</h1>
        
        <div class="cards">
    <div class="card">
        <h2>Inventory</h2>
        <a href="view_inventory.php">View Inventory <i class="fas fa-box-open"></i></a>
    </div>
    <div class="card">
        <h2>Requests</h2>
        <a href="manage_requests.php">Manage Requests <i class="fas fa-tasks"></i></a>
    </div>
    <div class="card">
        <h2>Orders</h2>
        <a href="order.php">View Orders <i class="fas fa-shipping-fast"></i></a>
    </div>
    <div class="card">
        <h2>Report</h2>
        <a href="report.php">Generate Report <i class="fas fa-chart-line"></i></a>
    </div>
    <div class="card">
        <h2>Upload Report</h2>
        <a href="admin_upload_report.php">Upload Attachment <i class="fas fa-upload"></i></a>
    </div>
    <div class="card">
        <h2>User Password Reset</h2>
        <a href="manage_user_passwords.php">User Password<i class="fas fa-lock"></i></a>
    </div>

    </div>

</body>
</html>
