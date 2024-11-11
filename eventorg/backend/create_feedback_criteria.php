<?php
// Include database connection
include 'db.php'; // Make sure to update with your actual connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $criterion = trim($_POST['criterion']); // Get the criterion and trim whitespace

    // Check if the criterion is not empty
    if (!empty($criterion)) {
        // Prepare and execute the insert statement
        $stmt = $conn->prepare("INSERT INTO feedbackcriteria (event_id, criterion) VALUES (?, ?)");
        $stmt->bind_param("is", $event_id, $criterion);

        if ($stmt->execute()) {
            // Successfully inserted
            echo "Criterion added successfully!";
        } else {
            // Error in insertion
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Criterion cannot be empty.";
    }

    // Redirect back to the event dashboard or wherever you need
    header("Location: ../frontend/event_dashboard.php");
    exit();
} else {
    echo "Invalid request method.";
}
?>
