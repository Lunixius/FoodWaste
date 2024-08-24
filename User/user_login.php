<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <link rel="stylesheet" href="style.css"> </head>
<body>
    <h1>User Login</h1>
    <form action="process_login.php" method="post"> <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="phone_number">Phone Number:</label>
        <input type="tel" id="phone_number" name="phone_number" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="user_type">User Type:</label>
        <select id="user_type" name="user_type" required>
            <option value="Restaurant">Restaurant</option>
            <option value="NGO">NGO</option>
        </select><br><br>

        <input type="submit" value="Login">
    </form>
</body>
</html>