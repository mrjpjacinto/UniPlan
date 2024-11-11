<?php
session_start(); // Start session at the very beginning
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if both username and password are filled
    if (empty($_POST['username']) || empty($_POST['password'])) {
        echo "Please enter both username and password.";
        exit();
    }
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute SQL query
    $sql = "SELECT * FROM organizers WHERE organizer_username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $row['organizer_password'])) {
                // Store session variables
                $_SESSION['organizer_id'] = $row['organizer_id'];
                $_SESSION['organizer_first_name'] = $row['first_name'];
                $_SESSION['organizer_last_name'] = $row['last_name'];

                // Debugging: Uncomment to check if session ID is set (for testing only)
                // echo "Session Organizer ID set: " . $_SESSION['organizer_id'];

                // Redirect to dashboard
                header("Location: ../frontend/dashboard.php");
                exit();
            } else {
                echo "Invalid password!";
            }
        } else {
            echo "Username not found!";
        }
        
        // Close statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "Invalid request method.";
}
?>
