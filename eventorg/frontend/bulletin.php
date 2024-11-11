<?php
include '../backend/db.php'; // Include your database connection

// Fetch upcoming approved events
$upcoming_sql = "SELECT * FROM events WHERE status = 'approved' AND date > NOW() ORDER BY date ASC";
$upcoming_result = $conn->query($upcoming_sql);
$upcoming_events = $upcoming_result->fetch_all(MYSQLI_ASSOC);

// Fetch recent approved events
$recent_sql = "SELECT * FROM events WHERE status = 'approved' AND date <= NOW() ORDER BY date DESC LIMIT 5"; // Limit to 5 recent events
$recent_result = $conn->query($recent_sql);
$recent_events = $recent_result->fetch_all(MYSQLI_ASSOC);

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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Bulletin</title>
    <link rel="stylesheet" href="design/bulletin.css"> 
</head>
<body>

    <div class="top-bar"> Uni Plan: SPUSM EVENTS</div>
   
    <div class="loginsec" style="margin-bottom: 20px;">
        <button onclick="location.href='admin_login.php'">Admin Login</button>
        <button onclick="location.href='login.php'">Organizer Login</button>
    </div>

    <div class="event-container">
       
        <div class="event-section upcoming">
            <h1>Upcoming Events</h1>
            <ul>
            <?php if (!empty($upcoming_events)): ?>
                <?php foreach ($upcoming_events as $event): ?>
                    <?php $progress = getChecklistProgress($event['id'], $conn); ?>
                    <?php if ($progress == 100): // Only display if the checklist is complete ?>
                        <li>
                            <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                            <button class="toggle-btn" onclick="toggleDescription(<?php echo $event['id']; ?>)">Show Description</button>
                            <p id="<?php echo $event['id']; ?>" class="description" style="display:none;"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                            <p>Date: <?php echo htmlspecialchars($event['date']); ?></p>
                            <p>Time: <?php echo date('g:i A', strtotime($event['time'])); ?></p>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No upcoming events.</p>
            <?php endif; ?>
            </ul>
        </div>

        <div class="event-section recent">
            <h1>Recent Events</h1>
            <ul>
            <?php if (!empty($recent_events)): ?>
                <?php foreach ($recent_events as $event): ?>
                    <?php $progress = getChecklistProgress($event['id'], $conn); ?>
                    <?php if ($progress == 100): // Only display if the checklist is complete ?>
                        <li>
                            <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                            <div class="tbuttons">
                                <button class="toggle-btn" onclick="toggleDescription(<?php echo $event['id']; ?>)">Show Description</button>
                                <button class="FB-button"><a href="feedback_form.php?event_id=<?php echo $event['id']; ?>">Give Feedback</a></button>
                            </div>
                            <p id="<?php echo $event['id']; ?>" class="description" style="display:none;"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                            <p>Date: <?php echo htmlspecialchars($event['date']); ?></p>
                            <p>Time: <?php echo date('g:i A', strtotime($event['time'])); ?></p>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No recent events.</p>
            <?php endif; ?>
            </ul>
        </div>
    </div>

    <script src="../backend/js/bulletin.js"></script> 
</body>
</html>
