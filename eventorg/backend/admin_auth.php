<?php
include 'db.php'; // Include your database connection

session_start(); // Start the session at the top

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_username = $_POST['admin_username'];
    $admin_password = $_POST['admin_password'];

    // Prepare and execute SQL query
    $sql = "SELECT * FROM admins WHERE username = ?"; // Ensure the table name is correct
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $admin_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Use password_verify to check the password
        if (password_verify($admin_password, $row['password'])) {
            // Set session variables for admin login
            $_SESSION['is_admin'] = true; // Indicate admin login
            $_SESSION['admin_id'] = $row['id']; // Store admin ID for further use
            $_SESSION['admin_first_name'] = $row['first_name']; // Store admin first name
            $_SESSION['admin_last_name'] = $row['last_name']; // Store admin last name

            header("Location: ../frontend/admin_approval.php"); // Redirect to admin approval page
            exit();
        } else {
            // Invalid password
            echo "<p style='color:red;'>Invalid password. Please try again.</p>";
        }
    } else {
        // Invalid username
        echo "<p style='color:red;'>Invalid username. Please try again.</p>";
    }
}
?>
