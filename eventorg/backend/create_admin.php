<?php
include 'db.php'; // Include your database connection

$admin_username = 'admin';
$admin_password = password_hash('spusmadmin123', PASSWORD_DEFAULT); // Hash the password

$sql = "INSERT INTO Admins (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $admin_username, $admin_password);
$stmt->execute();

echo "Admin user created successfully!";
?>
