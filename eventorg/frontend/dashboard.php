<?php
session_start();
if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php"); // Redirect if not logged in as organizer
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Organizer Dashboard</title>
</head>
<body>
    <h1>Welcome to the Organizer Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['organizer_first_name'] . ' ' . $_SESSION['organizer_last_name']); ?>!</p> <!-- Welcome message -->
    <p>Your organizer ID is: <?php echo $_SESSION['organizer_id']; ?></p>
    <p><a href="../backend/logout.php">Logout</a></p>
    
    <h2>Manage Events</h2>
    <button onclick="location.href='create_event.php'">Create Event</button>
    <button onclick="location.href='open_event.php'">Open Event</button>

    <!-- Add additional dashboard functionality here -->
</body>
</html>
