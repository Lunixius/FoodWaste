<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <style>
        body {
    font-family: 'Lato', sans-serif;
    background-image: url('your-background-image.jpg'); 
    background-size: cover;
    background-position: center;
}

.login-form {
    max-width: 350px; 
    margin: 0 auto;
    padding: 30px;
    border: 1px solid #ccc;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: rgba(255, 255, 255, 0.8);
}

.login-form h1 {
    font-size: 24px;
    text-align: center;
    margin-bottom: 25px;
    color: #333;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

.login-form label {
    font-size: 14px;
    display: block;
    margin-bottom: 8px;
    color: #666;
}

.login-form input,
.login-form select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
    margin-bottom: 15px;
}

.login-form button {
    background-color: #4CAF50;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
    margin-top: 15px;
}

.login-form button:hover {
    background-color: #3e8e41;
}

.login-form p {
    text-align: center;
    margin-top: 15px;
    color: #888;
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
