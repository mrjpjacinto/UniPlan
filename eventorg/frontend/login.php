<!DOCTYPE html>
<html>
<head>
    <title>Organizer Login</title>
    <link rel="stylesheet" href="design/login.css"> 
</head>
</head>
<body>
    <div class = "main">
        <div class = "title"><h2>Activity Coordinator Login</h2></div>

        <div class = "logindiv">
            <form class = "input1 "method="post" action="../backend/auth_login.php">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Login">
            </form>
        </div>
    </div>
</body>
</html>
