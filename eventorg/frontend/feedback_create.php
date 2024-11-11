<?php
session_start();
if (!isset($_SESSION['organizer_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect if not logged in as either admin or organizer
    exit();
}

include '../backend/db.php'; // Include your database connection

// Check if the event ID is set
if (!isset($_GET['event_id'])) {
    die("Event ID not specified.");
}

$event_id = $_GET['event_id'];

// Fetch the event title from the database
$sql_event = "SELECT title FROM events WHERE id = ?";
$stmt_event = $conn->prepare($sql_event);
$stmt_event->bind_param("i", $event_id);
$stmt_event->execute();
$result_event = $stmt_event->get_result();

if ($result_event->num_rows > 0) {
    $event = $result_event->fetch_assoc();
    $event_title = $event['title']; // Get the event title
} else {
    die("Event not found.");
}

// Handle form submission for creating feedback criteria
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if multiple criteria are provided as an array (if adding multiple criteria at once)
    $criteria = is_array($_POST['criterion']) ? $_POST['criterion'] : [$_POST['criterion']];

    $sql = "INSERT INTO feedbackcriteria (event_id, criterion) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    // Insert each criterion into the database
    foreach ($criteria as $criterion) {
        $stmt->bind_param("is", $event_id, $criterion);
        if ($stmt->execute()) {
            echo "Criterion '$criterion' added successfully.<br>";
        } else {
            echo "Error: " . $stmt->error . "<br>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Feedback Criteria</title>
    <link rel="stylesheet" href="design/styles.css"> <!-- Link to your CSS file -->
</head>
<body>
    <div class="top-bar">Create Feedback Criteria</div>

    <h1>Add Feedback Criteria for Event: <?php echo htmlspecialchars($event_title); ?></h1>

    <!-- Create Evaluation Criteria for an Event -->
    <form method="post" action="feedback_create.php?event_id=<?php echo $event_id; ?>">
        <label>Criterion:</label>
        <input type="text" name="criterion[]" required>
        <button type="button" onclick="addCriterion()">Add Another Criterion</button>
        <br><br>
        <button type="submit">Submit Criteria</button>
    </form>
    
    <a href="open_event.php">Back to Events</a>

    <script>
        // JavaScript function to dynamically add input fields for multiple criteria
        function addCriterion() {
            const form = document.querySelector('form');
            const newCriterion = document.createElement('input');
            newCriterion.type = 'text';
            newCriterion.name = 'criterion[]';
            newCriterion.required = true;
            form.insertBefore(newCriterion, form.querySelector('button[type="button"]'));
            form.insertBefore(document.createElement('br'), form.querySelector('button[type="button"]'));
        }
    </script>
</body>
</html>
