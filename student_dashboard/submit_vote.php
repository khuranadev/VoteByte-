<?php
include '../config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    die("User not logged in.");
}

// Decode the JSON input
$votes = json_decode($_POST['votes'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Invalid JSON input.");
}

// Get hostel_name from POST data
if (!isset($_POST['hostel_name'])) {
    die("Hostel name not provided.");
}
$hostel_name = $_POST['hostel_name'];

$email = $_SESSION['email'];

// Start a transaction
$conn->begin_transaction();

try {
    foreach ($votes as $day => $meals) {
        // Validate input
        if (!isset($meals['breakfast'], $meals['lunch'], $meals['snacks'], $meals['dinner'])) {
            throw new Exception("Invalid meal data for day: $day");
        }

        $breakfast = $meals['breakfast'];
        $lunch = $meals['lunch'];
        $snacks = $meals['snacks'];
        $dinner = $meals['dinner'];

        // Prepare the SQL statement
        $query = $conn->prepare("INSERT INTO votes (student_email, hostel_name, weekday, breakfast, lunch, snacks, dinner) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$query) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Bind parameters and execute
        $query->bind_param("sssssss", $email, $hostel_name, $day, $breakfast, $lunch, $snacks, $dinner);
        if (!$query->execute()) {
            throw new Exception("Execute failed: " . $query->error);
        }
    }

    // Commit the transaction
    $conn->commit();
    echo "Votes submitted successfully!";
} catch (Exception $e) {
    // Rollback the transaction in case of error
    $conn->rollback();
    die("Error: " . $e->getMessage());
}

// Close the connection
$conn->close();
?>