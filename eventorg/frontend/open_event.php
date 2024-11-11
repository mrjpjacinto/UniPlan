<?php
session_start();
if (!isset($_SESSION['organizer_id'])) {
    header("Location: login.php"); // Redirect if not logged in as organizer
    exit();
}

include '../backend/db.php'; // Include your database connection

$organizer_id = $_SESSION['organizer_id'];
$sql = "SELECT * FROM events WHERE organizer_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
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
    $delete_sql = "DELETE FROM events WHERE id = ? AND organizer_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $event_id, $organizer_id);
    $delete_stmt->execute();
    
    header("Location: open_event.php");
    exit();
}

// Handle deployment when checklist is 100% complete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deploy_event_id'])) {
    $deploy_event_id = $_POST['deploy_event_id'];
    $progress = getChecklistProgress($deploy_event_id, $conn);
    
    if ($progress == 100) {
        $update_sql = "UPDATE events SET status = 'upcoming' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $deploy_event_id);
        $update_stmt->execute();
        
        echo "<p>Event successfully deployed to Upcoming Events!</p>";
    } else {
        echo "<p>Checklist not complete. Cannot deploy event.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Open Events</title>
    <link rel="stylesheet" href="design/open_event.css">
    <link rel="stylesheet" href="design/bulletin.css">
    <script src="../backend/js/bulletin.js"></script>
</head>
<body>
    <div class="top-bar">Uni Plan: SPUSM EVENTS</div>

    <div class="loginsec"></div>

    <div class="y-events"><h1>Your Events</h1></div>
    
    <div class="event-container">
        <ul>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($event = $result->fetch_assoc()): ?>
                    <?php $progress = getChecklistProgress($event['id'], $conn); ?>
                    <li>
                        <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                        
                        <!-- Show Description Button -->
                        <button class="toggle-btn" onclick="toggleDescription(<?php echo $event['id']; ?>)">Show Description</button>
                        
                        <!-- View Checklist Button -->
                        <button class="toggle-btn" onclick="location.href='event_checklist.php?event_id=<?php echo $event['id']; ?>'">View Checklist</button>

                        <!-- View Feedback Button -->
                        <button class="toggle-btn" onclick="location.href='feedback_view.php?event_id=<?php echo $event['id']; ?>'">View Feedback</button>

                        <p id="<?php echo $event['id']; ?>" class="description" style="display: none;"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        <p>Date: <?php echo htmlspecialchars($event['date']); ?></p>
                        <p>Time: <?php echo date('g:i A', strtotime($event['time'])); ?></p>
                        <p>Status: <?php echo htmlspecialchars($event['status']); ?></p>

                        <!-- Progress Indicator -->
                        <p>Checklist Progress: <?php echo $progress; ?>%</p>

                       

                        <!-- Create Feedback Button if the event is approved -->
                        <?php if (strtolower($event['status']) == 'approved'): ?>
                            <button onclick="location.href='feedback_create.php?event_id=<?php echo $event['id']; ?>'">Create Feedback</button>
                        <?php endif; ?>

                        <!-- Edit Button -->
                        <button onclick="location.href='edit_event.php?event_id=<?php echo $event['id']; ?>'">Edit Event</button>

                        <!-- Delete Event Form -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="delete_event_id" value="<?php echo $event['id']; ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this event?');">Delete Event</button>
                        </form>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No events found for you.</p>
            <?php endif; ?>
        </ul>
    </div>
    
    
</body>
</html>
