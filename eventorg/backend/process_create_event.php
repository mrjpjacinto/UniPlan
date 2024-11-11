<?php
session_start(); // Ensure session is started
include 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $date = $_POST['date'] ?? null;
    $time = $_POST['time'] ?? null;
    $organizer_id = $_SESSION['organizer_id']; // Assuming the session has the organizer's ID
    $checklist = $_POST['checklist'] ?? []; // Retrieve checklist items, default to an empty array if not set

    // Check if any required fields are null
    if (is_null($title) || is_null($description) || is_null($date) || is_null($time)) {
        die('Error: All fields are required!');
    }

    // Prepare SQL statement to insert event
    $sql = "INSERT INTO events (title, description, date, time, organizer_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Check if prepare was successful
    if ($stmt === false) {
        die('MySQL prepare error: ' . htmlspecialchars($conn->error));
    }

    // Bind parameters
    $stmt->bind_param("ssssi", $title, $description, $date, $time, $organizer_id);

    // Execute the statement to create the event
    if ($stmt->execute()) {
        $event_id = $stmt->insert_id; // Get the ID of the newly created event
        echo "Event created successfully!<br>";

        // Insert checklist items if any were provided
        if (!empty($checklist)) {
            $sql_checklist = "INSERT INTO checklist_items (event_id, item, is_completed) VALUES (?, ?, 0)";
            $stmt_checklist = $conn->prepare($sql_checklist);

            // Check if checklist prepare was successful
            if ($stmt_checklist === false) {
                die('MySQL prepare error (checklist): ' . htmlspecialchars($conn->error));
            }

            // Insert each checklist item
            foreach ($checklist as $item) {
                $stmt_checklist->bind_param("is", $event_id, $item);
                if (!$stmt_checklist->execute()) {
                    echo "Error adding checklist item: " . htmlspecialchars($stmt_checklist->error) . "<br>";
                }
            }
            echo "Checklist items added successfully!<br>";
        }

        // Redirect or continue processing
        // header("Location: dashboard.php"); 
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }

    // Close the statements and connection
    $stmt->close();
    if (isset($stmt_checklist)) $stmt_checklist->close();
}

$conn->close();
?>
