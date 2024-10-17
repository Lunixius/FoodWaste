<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align form at the top when scrolling */
        }

        /* Make the body scrollable */
        html, body {
            overflow-y: scroll; /* Enable vertical scrolling */
        }

        .register-form {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            margin: 40px 0; /* Add some space at the top and bottom */
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .register-form h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .register-form label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 700;
            color: #666;
        }

        .register-form input,
        .register-form select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .register-form input:focus,
        .register-form select:focus {
            border-color: #007bff;
            outline: none;
        }

        .register-form button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 700;
            transition: background-color 0.3s ease;
        }

        .register-form button:hover {
            background-color: #0056b3;
        }

        .register-form p {
            font-size: 14px;
            color: #666;
            margin-top: 20px;
        }

        .register-form p a {
            color: #007bff;
            text-decoration: none;
            font-weight: 700;
        }

        .register-form p a:hover {
            color: #0056b3;
        }

        /* Error message */
        .error-message {
            color: red;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="register-form">
        <h1>User Registration</h1>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="user_register.php" method="post">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="phone_number">Phone Number</label>
            <input type="tel" id="phone_number" name="phone_number" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <label for="user_type">User Type</label>
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
