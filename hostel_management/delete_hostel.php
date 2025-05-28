<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SESSION['email'] !== 'hostel.admin@kiit.ac.in') {
    header("Location: index.php");
    exit;
}

// Delete hostel credentials
if (isset($_GET['id'])) {
    $hostel_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM hostels WHERE id = ?");
    $stmt->bind_param("i", $hostel_id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../main_hostel_dashboard.php");
    exit;
}
?>