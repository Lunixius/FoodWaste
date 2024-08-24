<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <style>
        .login-form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .login-form h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-form label {
            display: block;
            margin-bottom: 5px;
        }

        .login-form input,
        .login-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        .login-form button,
        .login-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        .login-form button:hover,
        .login-form input[type="submit"]:hover {
            background-color: #3e8e41;
        }

        .login-form p {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h1>User Login</h1>
        <form action="process_login.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone_number">Phone Number:</label>
            <input type="tel" id="phone_number" name="phone_number" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="user_type">User Type:</label>
            <select id="user_type" name="user_type" required>
                <option value="Restaurant">Restaurant</option>
                <option value="NGO">NGO</option>
            </select>

            <input type="submit" value="Login">
            <p><a href="forgot_password.php">Forgot Password?</a></p>
        </form>
    </div>
</body>
</html>
