<?php
session_start();
require 'config.php';

// Ensure only authorized users can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SESSION['email'] !== 'hostel.admin@kiit.ac.in') {
    header("Location: index.php");
    exit;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        die("All fields are required.");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new hostel credentials into the database
    $stmt = $conn->prepare("INSERT INTO hostels (username, email, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("sss", $username, $email, $hashed_password);
    if ($stmt->execute()) {
        // Redirect to the hostels list page with a success message
        header("Location: view_hostel.php?success=1");
        exit;
    } else {
        die("Error saving hostel credentials: " . $stmt->error);
    }

    $stmt->close();
} else {
    // If the form is not submitted, redirect to the add hostel page
    header("Location: add_hostel.php");
    exit;
}
?>