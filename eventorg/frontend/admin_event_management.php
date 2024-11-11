<?php
session_start();
include '../backend/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle form submission for creating an event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Insert the event into the database with status set to 'pending'
    $stmt = $conn->prepare("INSERT INTO events (title, description, date, time, status, organizer_id) VALUES (?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("ssssi", $title, $description, $date, $time, $_SESSION['admin_id']); // Assuming admin ID is used as organizer ID
    $stmt->execute();

    header("Location: admin_event_management.php"); // Redirect after creation
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Event Management</title>
</head>
<body>
    <h1>Admin Event Management</h1>

    <!-- Button for Creating an Event -->
    <h2>Create a New Event</h2>
    <form method="post" action="admin_event_management.php">
        <input type="text" name="title" placeholder="Event Title" required>
        <textarea name="description" placeholder="Event Description" required></textarea>
        <input type="date" name="date" required>
        <input type="time" name="time" required>
        <input type="submit" name="create_event" value="Create Event">
    </form>

    <hr>

    <p>
        <button onclick="location.href='open_event_admin.php'">View Open Events</button>
    </p>
    <ul>
    
</body>
</html>
