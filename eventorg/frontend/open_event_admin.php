<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); // Redirect if not logged in as admin
    exit();
}

include '../backend/db.php'; // Include your database connection

// Fetch all events
$sql = "SELECT * FROM events ORDER BY date DESC"; // Fetch all events regardless of the organizer
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Function to calculate checklist progress percentage
function getChecklistProgress($event_id, $conn) {
    $total_items_sql = "SELECT COUNT(*) AS total FROM checklist_items WHERE event_id = ?";
    $completed_items_sql = "SELECT COUNT(*) AS completed FROM checklist_items WHERE event_id = ? AND is_completed = 1";
    
    $total_stmt = $conn->prepare($total_items_sql);
    $total_stmt->bind_param("i", $event_id);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_items = $total_result->fetch_assoc()['total'];
    
    if ($total_items == 0) return 0; // Avoid division by zero
    
    $completed_stmt = $conn->prepare($completed_items_sql);
    $completed_stmt->bind_param("i", $event_id);
    $completed_stmt->execute();
    $completed_result = $completed_stmt->get_result();
    $completed_items = $completed_result->fetch_assoc()['completed'];
    
    return round(($completed_items / $total_items) * 100);
}

// Handle deletion of an event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event_id'])) {
    $event_id = $_POST['delete_event_id'];
    
    // Delete the event
    $delete_sql = "DELETE FROM events WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $event_id);
    $delete_stmt->execute();
    
    // Redirect to refresh the event list
    header("Location: open_event_admin.php");
    exit();
}

// Handle approval/rejection of an event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $update_sql = "UPDATE events SET status = 'approved' WHERE id = ?";
    } else if ($action === 'reject') {
        $update_sql = "UPDATE events SET status = 'rejected' WHERE id = ?";
    }
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $event_id);
    $update_stmt->execute();
    
    // Redirect to refresh the event list
    header("Location: open_event_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Open Events</title>
    <link rel="stylesheet" href="desig/open_event.css"> <!-- Link to the new CSS file -->
    <link rel="stylesheet" href="design/bulletin.css"> <!-- Assuming you have a CSS file for other styling -->
    <script src="bulletin.js"></script> <!-- Assuming you have your existing toggle function here -->
</head>
<body>
    <div class="top-bar">Uni Plan: SPUSM EVENTS</div>

    <div class="y-events"><h1>All Events</h1></div>
    
    <div class="event-container">
    <ul>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($event = $result->fetch_assoc()): ?>
                <?php $progress = getChecklistProgress($event['id'], $conn); ?>
                <li>
                    <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                    <button class="toggle-btn" onclick="toggleDescription(<?php echo $event['id']; ?>)">Show Description</button>
                    <button class="toggle-btn" onclick="location.href='feedback_view.php?event_id=<?php echo $event['id']; ?>'">View Feedback</button>
                    <button class="toggle-btn" onclick="location.href='event_checklist.php?event_id=<?php echo $event['id']; ?>'">View Checklist</button>
                    <p id="<?php echo $event['id']; ?>" class="description" style="display: none;"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    <p>Date: <?php echo htmlspecialchars($event['date']); ?></p>
                    <p>Time: <?php echo date('g:i A', strtotime($event['time'])); ?></p>
                    <p>Status: <?php echo htmlspecialchars($event['status']); ?></p>

                    <!-- Progress Indicator -->
                    <p>Checklist Progress: <?php echo $progress; ?>%</p>

                    <!-- Show Approve/Reject buttons only if status is not approved or rejected -->
                    <?php if ($event['status'] !== 'approved' && $event['status'] !== 'rejected'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <button type="submit" name="action" value="approve" onclick="return confirm('Approve this event?');">Approve</button>
                            <button type="submit" name="action" value="reject" onclick="return confirm('Reject this event?');">Reject</button>
                        </form>
                    <?php endif; ?>

                    <!-- Edit Event Button -->
                    <button onclick="location.href='edit_event.php?event_id=<?php echo $event['id']; ?>'">Edit Event</button>

                    <!-- Delete Event Button -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_event_id" value="<?php echo $event['id']; ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this event?');">Delete Event</button>
                    </form>

                    <!-- Create Feedback Button -->
                    <button onclick="location.href='feedback_create.php?event_id=<?php echo $event['id']; ?>'">Create Feedback</button>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No events found.</p>
        <?php endif; ?>
    </ul>
</div>

    <script src="../backend/js/bulletin.js"></script>
</body>
</html>
