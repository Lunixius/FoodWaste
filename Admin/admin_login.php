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
            overflow: hidden;
        }

        .login-form {
            width: 480px;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
            background: linear-gradient(to bottom right, #ffffff 40%, #ffddc1);
            position: relative;
        }

        .login-form:before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 120px;
            height: 120px;
            background: linear-gradient(to bottom right, #ff9800, #f44336);
            border-radius: 50%;
            opacity: 0.3;
        }

        .login-form:after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -40px;
            width: 120px;
            height: 120px;
            background: linear-gradient(to top left, #4caf50, #8bc34a);
            border-radius: 50%;
            opacity: 0.3;
        }

        .login-form h1 {
            font-size: 26px;
            text-align: center;
            margin-bottom: 25px;
            color: #444;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
            font-weight: 700;
        }

        .login-form label {
            font-size: 15px;
            display: block;
            margin-bottom: 7px;
            color: #555;
            font-weight: 500;
        }

        .login-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #bbb;
            border-radius: 8px;
            box-sizing: border-box;
            margin-bottom: 15px;
            background-color: #f0f0f0;
            transition: all 0.3s ease;
        }

        .login-form input:focus {
            background-color: #fff;
            border-color: #ff9800;
            box-shadow: 0 0 5px rgba(255, 152, 0, 0.5);
            outline: none;
        }

        .login-form button {
            background-color: #f44336;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
            font-weight: 600;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            margin-top: 15px;
        }

        .login-form button:hover {
            background-color: #d32f2f;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .login-form p {
            text-align: center;
            margin-top: 15px;
            color: #555;
            font-size: 15px;
        }

        .login-form p a {
            color: #f44336;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-form p a:hover {
            color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h1>Admin Login</h1>
        <form action="admin_login.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
            <p><a href="admin_forgot_password.php">Forgot Password?</a></p>
        </form>
    </div>
</body>
</html>
