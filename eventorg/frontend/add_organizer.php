<?php
session_start();
if (!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php"); // Redirect if not admin
    exit();
}

include '../backend/db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $email = $_POST['email'];

    // Prepare SQL statement to insert organizer
    $sql = "INSERT INTO organizers (organizer_username, organizer_password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sss", $username, $password, $email);
        if ($stmt->execute()) {
            echo "Organizer added successfully!";
        } else {
            echo "Error adding organizer: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Organizer</title>
</head>
<body>
    <h2>Add Organizer</h2>
    <form method="post" action="add_organizer.php">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="submit" value="Add Organizer">
    </form>
    <p><a href="..backend/logout.php">Logout</a></p>
</body>
</html>
