<?php
session_start();
include '../backend/db.php'; // Include your database connection

// Check if logged in as either an organizer or an admin
if (!isset($_SESSION['organizer_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect if not logged in as organizer or admin
    exit();
}

// Fetch the event to be edited
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    if (isset($_SESSION['organizer_id'])) {
        // Organizer can only access their own events
        $sql = "SELECT * FROM events WHERE id = ? AND organizer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $event_id, $_SESSION['organizer_id']);
    } else {
        // Admin can access any event
        $sql = "SELECT * FROM events WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $event_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "Event not found or you do not have permission to edit it.";
        exit();
    }

    $event = $result->fetch_assoc();
}

// Handle the update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Update the event
    $update_sql = "UPDATE events SET title = ?, description = ?, date = ?, time = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    
    // Bind parameters based on user role
    if (isset($_SESSION['organizer_id'])) {
        $update_sql .= " AND organizer_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssii", $title, $description, $date, $time, $event_id, $_SESSION['organizer_id']);
    } else {
        $update_stmt->bind_param("ssssi", $title, $description, $date, $time, $event_id);
    }
    $update_stmt->execute();

    // Redirect based on user role
    if (isset($_SESSION['admin_id'])) {
        header("Location: open_event_admin.php");
    } else {
        header("Location: open_event.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
</head>
<body>
    <h1>Edit Event</h1>
    <form method="post">
        <label>Title:</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required><br>

        <label>Description:</label><br>
        <textarea name="description" required><?php echo htmlspecialchars($event['description']); ?></textarea><br>

        <label>Date:</label><br>
        <input type="date" name="date" value="<?php echo htmlspecialchars($event['date']); ?>" required><br>

        <label>Time:</label><br>
        <input type="time" name="time" value="<?php echo htmlspecialchars($event['time']); ?>" required><br><br>

        <button type="submit">Update Event</button>
    </form>
</body>
</html>
