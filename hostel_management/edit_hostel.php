<?php
session_start();
require 'config.php';

// Ensure only authorized users can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SESSION['email'] !== 'hostel.admin@kiit.ac.in') {
    header("Location: index.php");
    exit;
}

// Fetch hostel details for editing
$hostel_id = $_GET['id']; // Assuming the hostel ID is passed via URL
$stmt = $conn->prepare("SELECT username, email FROM hostels WHERE id = ?");
$stmt->bind_param("i", $hostel_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/logo.png">
    <title>Edit Hostel Login Credentials</title>
    <style>
        /* Reuse the same styles as add_hostel.php */
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Edit Hostel Login Credentials</h1>
        <form action="update_hostel.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $hostel_id; ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">New Password (leave blank to keep current)</label>
                <input type="password" id="password" name="password">
            </div>
            <button type="submit" class="btn-submit">Update Hostel</button>
        </form>
        <div class="back-button">
            <a href="view_hostel.php">Back to View</a><br>
            <a href="../main_hostel_dashboard.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>