<?php
session_start();
include '../backend/db.php'; // Include your database connection

// Ensure the event_id is passed from the previous page
if (!isset($_GET['event_id'])) {
    echo "No event selected.";
    exit();
}

$event_id = $_GET['event_id'];

// Fetch criteria for the event
$criteria_sql = "SELECT * FROM feedbackcriteria WHERE event_id = ?";
$criteria_stmt = $conn->prepare($criteria_sql);
$criteria_stmt->bind_param("i", $event_id);
$criteria_stmt->execute();
$criteria_result = $criteria_stmt->get_result();

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $comment = $_POST['comment']; // Capture the overall comment

    // Insert overall comment in the feedback table
    $feedback_stmt = $conn->prepare("INSERT INTO feedback (event_id, email, comment) VALUES (?, ?, ?)");
    $feedback_stmt->bind_param("iss", $event_id, $email, $comment);
    $feedback_stmt->execute();
    
    // Get the last inserted feedback ID
    $feedback_id = $conn->insert_id;

    // Insert feedback for each criterion
    while ($criterion = $criteria_result->fetch_assoc()) {
        $rating = $_POST['rating'][$criterion['id']]; // Get rating for each criterion

        // Insert criterion rating into the criterion_ratings table
        $criterion_stmt = $conn->prepare("INSERT INTO criterion_ratings (feedback_id, criterion_id, rating) VALUES (?, ?, ?)");
        $criterion_stmt->bind_param("iii", $feedback_id, $criterion['id'], $rating);
        $criterion_stmt->execute();
    }

    echo "Thank you for your feedback!";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Feedback</title>
</head>
<body>
    <h1>Feedback for Event</h1>
    <form method="post" action="">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <br>

        <h2>Rate Each Criterion:</h2>
        <?php
        // Resetting the criteria result pointer to the start
        $criteria_result->data_seek(0); 
        while ($criterion = $criteria_result->fetch_assoc()): ?>
            <label for="rating[<?php echo $criterion['id']; ?>]"><?php echo htmlspecialchars($criterion['criterion']); ?>:</label>
            <select name="rating[<?php echo $criterion['id']; ?>]" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
            <br>
        <?php endwhile; ?>

        <label for="comment">Overall Comment:</label>
        <textarea name="comment" required></textarea>
        <br>

        <button type="submit">Submit Feedback</button>
    </form>
</body>
</html>
