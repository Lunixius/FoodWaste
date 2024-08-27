<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body {
            font-family: 'Lato', sans-serif;
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
            overflow: hidden; /* Prevents any overflow */
        }

        .login-form {
            width: 500px;  /* Adjusted width */
            padding: 20px;  /* Reduced padding */
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border-top: 5px solid #4CAF50;
            border-left: 5px solid #FF9800;
        }

        .login-form:before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: #FF9800;
            border-radius: 50%;
            opacity: 0.2;
        }

        .login-form:after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 150px;
            height: 150px;
            background: #4CAF50;
            border-radius: 50%;
            opacity: 0.2;
        }

        .login-form h1 {
            font-size: 24px;  /* Slightly reduced font size */
            text-align: center;
            margin-bottom: 20px;  /* Reduced margin */
            color: #444;
            font-weight: 700;
        }

        .login-form label {
            font-size: 14px;  /* Slightly reduced font size */
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 500;
        }

        .login-form input,
        .login-form select {
            width: 100%;
            padding: 10px;  /* Reduced padding */
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            margin-bottom: 10px;  /* Reduced margin */
            background-color: #f7f7f7;
            transition: all 0.3s ease;
        }

        .login-form input:focus,
        .login-form select:focus {
            background-color: #fff;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
            outline: none;
        }

        .login-form button {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            margin-top: 10px;  /* Reduced margin */
        }

        .login-form button:hover {
            background-color: #388E3C;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .login-form p {
            text-align: center;
            margin-top: 10px;  /* Reduced margin */
            color: #666;
            font-size: 14px;
        }

        .login-form p a {
            color: #FF9800;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-form p a:hover {
            color: #E65100;
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

            <button type="submit">Login</button>
            <p><a href="user_forgot_password.php">Forgot Password?</a></p>
        </form>
    </div>
</body>
</html>
