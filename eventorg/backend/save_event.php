<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $organizer_id = $_SESSION['organizer_id'];

    // Insert event into database
    $sql = "INSERT INTO Events (title, description, date, created_by) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $description, $date, $organizer_id);
    
    if ($stmt->execute()) {
        echo "Event created successfully!";
        // Redirect to dashboard or event page
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
