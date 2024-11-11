<?php
session_start();
include '../backend/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch the admin's first and last name from the session
$admin_first_name = $_SESSION['admin_first_name'] ?? 'Admin';
$admin_last_name = $_SESSION['admin_last_name'] ?? '';

// Fetch pending events without duplicating them
$sql = "SELECT * FROM events WHERE status = 'pending' ORDER BY date DESC";
$result = $conn->query($sql);

// Approve or reject events
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $update_sql = "UPDATE events SET status = 'approved' WHERE id = ?";
    } elseif ($action === 'reject') {
        $update_sql = "UPDATE events SET status = 'rejected' WHERE id = ?";
    }

    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    header("Location: admin_approval.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Event Approval</title>
    <link rel="stylesheet" href="../frontend/design/bulletin.css">
    <script src="js/bulletin.js"></script>
    <style>
        .event-box {
        background-color: #f4f4f4; /* Light grey background color */
        border: 1px solid #ccc;    /* Border around the box */
        padding: 20px;             /* Padding inside the box */
        margin-bottom: 20px;       /* Space between event boxes */
        border-radius: 8px;        /* Rounded corners */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Optional shadow for a subtle effect */
        }
        .checklist {
            display: none; /* Hide checklist items initially */
        }
    </style>
    <script>
        function toggleChecklist(eventId) {
            const checklist = document.getElementById(`checklist-${eventId}`);
            checklist.style.display = checklist.style.display === "none" || checklist.style.display === "" ? "block" : "none";
        }

        function toggleDescription(eventId) {
            const description = document.getElementById(eventId);
            description.style.display = description.style.display === "none" || description.style.display === "" ? "block" : "none";
        }
    </script>
</head>
<body>
    <div class="top-bar">
        <h1>Admin Event Approval</h1>
        <div class="loginsec">
        <button onclick="location.href='add_organizer.php'">Add Organizer</button>
        <button onclick="location.href='admin_event_management.php'">Create/Open Event</button>
        <p><a href="../backend/A_logout.php" style="color: white;">Logout</a></p>
    </div>
    </div>

    

    <div class="y-events">
        <div class="welcome-message" style="margin-top: 50px;">
            <p>Welcome, <?php echo htmlspecialchars($admin_first_name . ' ' . $admin_last_name); ?>!</p>
        </div>
        <h1>Pending Event Approvals</h1>
        <div class="event-container">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="event-box">
                    <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                    <span class="toggle-btn" onclick="toggleDescription(<?php echo $row['id']; ?>)">Show/Hide Description</span>
                    <p id="<?php echo $row['id']; ?>" class="description" style="display: none;">
                        <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                    </p>
                    <p>Date: <?php echo htmlspecialchars($row['date']); ?></p>
                    <p>Time: <?php echo date('g:i A', strtotime($row['time'])); ?></p>
                    
                    <button class="toggle-btn" onclick="toggleChecklist(<?php echo $row['id']; ?>)">Show/Hide Checklist Items</button>
                    <div id="checklist-<?php echo $row['id']; ?>" class="checklist">
                        <h3>Checklist Items:</h3>
                        <ul>
                            <?php 
                            // Fetch and display checklist items for this event
                            $sql_items = "SELECT item FROM checklist_items WHERE event_id = ?";
                            $stmt_items = $conn->prepare($sql_items);
                            $stmt_items->bind_param("i", $row['id']);
                            $stmt_items->execute();
                            $result_items = $stmt_items->get_result();
                            
                            if ($result_items->num_rows > 0) {
                                while ($item = $result_items->fetch_assoc()) {
                                    echo '<li>' . htmlspecialchars($item['item']) . '</li>';
                                }
                            } else {
                                echo "<li>No checklist items available.</li>";
                            }
                            ?>
                        </ul>
                    </div>

                    <form method="post" action="admin_approval.php">
                        <input type="hidden" name="event_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="action" value="approve" class="toggle-btn">Approve</button>
                        <button type="submit" name="action" value="reject" class="toggle-btn">Reject</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
