<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel = "stylesheet" href = "design/login.css">
</head>
<body>
    <div class = "main">
        <div class = "logindiv">
            <div class = "title"><h2 >Admin Login</h2></div>
            <form class = "input1" method="post" action="../backend/admin_auth.php">
                <input type="text" name="admin_username" placeholder="Username" required>
                <input type="password" name="admin_password" placeholder="Password" required>

                <input class = "loginbut" type="submit" value="Login">
            </form>
        </div>
    </div>
</body>
</html>
