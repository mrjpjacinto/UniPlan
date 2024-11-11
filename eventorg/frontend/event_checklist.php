<?php
session_start();

// Check if the user is logged in as either an admin or an organizer
if (!isset($_SESSION['organizer_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include '../backend/db.php';

if (!isset($_GET['event_id'])) {
    die("Event ID not specified.");
}

$event_id = $_GET['event_id'];

// Handle checklist item submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_item'])) {
    $new_item = $_POST['new_item'];
    $sql_insert = "INSERT INTO checklist_items (event_id, item, is_completed) VALUES (?, ?, 0)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("is", $event_id, $new_item);
    $stmt_insert->execute();
}

// Handle updating checklist item status
if (isset($_POST['toggle_completion']) && isset($_POST['checklist_id'])) {
    $checklist_id = $_POST['checklist_id'];
    $current_status = $_POST['is_completed'];
    $new_status = $current_status ? 0 : 1;  // Toggle between 0 and 1

    $sql_toggle = "UPDATE checklist_items SET is_completed = ? WHERE checklist_id = ?";
    $stmt_toggle = $conn->prepare($sql_toggle);
    $stmt_toggle->bind_param("ii", $new_status, $checklist_id);
    $stmt_toggle->execute();
}

// Fetch checklist items for this event
$sql_items = "SELECT * FROM checklist_items WHERE event_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $event_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

// Calculate progress
$total_items = $result_items->num_rows;
$completed_items = 0;
while ($item = $result_items->fetch_assoc()) {
    if ($item['is_completed'] == 1) {
        $completed_items++;
    }
}
$progress_percentage = $total_items > 0 ? ($completed_items / $total_items) * 100 : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Checklist</title>
</head>
<body>
    <h2>Event Checklist</h2>
    <p>Progress: <?php echo round($progress_percentage); ?>%</p>
    <div style="width: 100%; background-color: #ddd;">
        <div style="width: <?php echo $progress_percentage; ?>%; background-color: #4CAF50; height: 24px;"></div>
    </div>

    <h3>Checklist for Event ID: <?php echo $event_id; ?></h3>
    <ul>
        <?php
        $result_items->data_seek(0); // Reset result pointer to loop again
        while ($item = $result_items->fetch_assoc()) {
            ?>
            <li>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="checklist_id" value="<?php echo $item['checklist_id']; ?>">
                    <input type="hidden" name="is_completed" value="<?php echo $item['is_completed']; ?>">
                    <button type="submit" name="toggle_completion" style="background: none; border: none; color: <?php echo $item['is_completed'] ? 'green' : 'red'; ?>;">
                        <?php echo $item['is_completed'] ? '✔' : '✖'; ?>
                    </button>
                </form>
                <?php echo htmlspecialchars($item['item']); ?>
            </li>
        <?php } ?>
    </ul>

    <!-- Add new checklist item -->
    <form method="post">
        <input type="text" name="new_item" placeholder="New Checklist Item" required>
        <button type="submit">Add Item</button>
    </form>

    <!-- Back to Events link -->
    <a href="<?php echo isset($_SESSION['admin_id']) ? 'open_event_admin.php' : 'open_event.php'; ?>">Back to Events</a>
</body>
</html>
