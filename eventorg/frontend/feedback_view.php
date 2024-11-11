<?php
session_start();
include '../backend/db.php'; // Include your database connection

if (!isset($_SESSION['organizer_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

$event_sql = "SELECT * FROM events WHERE id = ?";
$event_stmt = $conn->prepare($event_sql);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc();

if (!$event) {
    echo "Event not found.";
    exit();
}

$feedback_sql = "SELECT * FROM feedback WHERE event_id = ?";
$feedback_stmt = $conn->prepare($feedback_sql);
$feedback_stmt->bind_param("i", $event_id);
$feedback_stmt->execute();
$feedback_result = $feedback_stmt->get_result();

$criteria_sql = "SELECT * FROM feedbackcriteria WHERE event_id = ?";
$criteria_stmt = $conn->prepare($criteria_sql);
$criteria_stmt->bind_param("i", $event_id);
$criteria_stmt->execute();
$criteria_result = $criteria_stmt->get_result();

$criteria = [];
while ($row = $criteria_result->fetch_assoc()) {
    $criteria[$row['id']] = $row['criterion'];
}

// Fetch average ratings for each criterion
$average_ratings = [];
$average_ratings_sql = "SELECT criterion_id, AVG(rating) as avg_rating FROM criterion_ratings GROUP BY criterion_id";
$average_ratings_stmt = $conn->prepare($average_ratings_sql);
$average_ratings_stmt->execute();
$average_ratings_result = $average_ratings_stmt->get_result();

while ($row = $average_ratings_result->fetch_assoc()) {
    $average_ratings[$row['criterion_id']] = $row['avg_rating'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Feedback</title>
    <link rel="stylesheet" href="style/feedback.css">
    <script src="../backend/js/feedback.js" defer></script> <!-- Link to your JavaScript file -->
</head>
<body>
    <div class="top-bar">Uni Plan: SPUSM EVENTS - Feedback for "<?php echo htmlspecialchars($event['title']); ?>"</div>

    <div class="feedback-container">
        <h1>Feedback for "<?php echo htmlspecialchars($event['title']); ?>"</h1>

        <h3>Average Ratings per Criterion:</h3>
        <ul>
            <?php foreach ($criteria as $id => $criterion): ?>
                <li><?php echo htmlspecialchars($criterion); ?>: <?php echo number_format($average_ratings[$id] ?? 0, 2); ?>/5</li>
            <?php endforeach; ?>
        </ul>

        <!-- Button to toggle Overall Feedback section -->
        <button id="toggle-overall-feedback">Show Feedbacks</button>
        
        <div id="overall-feedback" style="display: none;">
            <h3>Feedbacks:</h3>
            <?php if ($feedback_result->num_rows > 0): ?>
                <ul>
                    <?php while ($feedback = $feedback_result->fetch_assoc()): ?>
                        <li>
                            <p>Email: <?php echo htmlspecialchars($feedback['email']); ?></p>
                            <p>Comment: <?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No feedback available for this event.</p>
            <?php endif; ?>
        </div>

        <button onclick="window.history.back()">Back</button>
    </div>
</body>
</html>
