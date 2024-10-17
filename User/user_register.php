<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('your-background-image.jpg');
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        /* Registration container */
        .register-container {
            width: 500px;
            max-height: 80vh; /* Limit height to 80% of the viewport */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            background-color: rgba(255, 255, 255, 0.9);
            border-top: 5px solid #4CAF50;
            border-left: 5px solid #FF9800;
            position: relative;
            overflow-y: auto; /* Enable vertical scrolling */
        }

        /* Background circles */
        .register-container:before, .register-container:after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: 0.2;
        }
        .register-container:before {
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: #FF9800;
        }
        .register-container:after {
            bottom: -50px;
            left: -50px;
            width: 150px;
            height: 150px;
            background: #4CAF50;
        }

        /* Form styling */
        .register-container h1 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
            color: #444;
        }

        .register-container label {
            font-size: 14px;
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #666;
        }

        .register-container input,
        .register-container select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f7f7f7;
            margin-bottom: 15px;
            font-size: 14px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .register-container input:focus,
        .register-container select:focus {
            background-color: #fff;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
            outline: none;
        }

        /* Submit button */
        .register-container button {
            background-color: #4CAF50;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            margin-top: 10px;
            width: 100%;
        }

        .register-container button:hover {
            background-color: #388E3C;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Link styling */
        .register-container p {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-top: 15px;
        }

        .register-container p a {
            color: #FF9800;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-container p a:hover {
            color: #E65100;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>User Registration</h1>
        <?php if (!empty($error_message)): ?>
            <p style="color: red; text-align: center;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="user_register.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone_number">Phone Number:</label>
            <input type="tel" id="phone_number" name="phone_number" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <label for="user_type">User Type:</label>
            <select id="user_type" name="user_type" required>
                <option value="Restaurant">Restaurant</option>
                <option value="NGO">NGO</option>
            </select>

            <button type="submit">Register</button>
            <p><a href="user_login.php">Already have an account? Login</a></p>
        </form>
    </div>
</body>
</html>
