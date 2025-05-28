<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hash_passwords'])) {
    // Hash passwords in the hostels table
    $result = $conn->query("SELECT id, password FROM hostels");
    while ($row = $result->fetch_assoc()) {
        $hashed_password = password_hash($row['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE hostels SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $row['id']);
        $stmt->execute();
    }

    // Hash passwords in the students table
    $result = $conn->query("SELECT id, password FROM students");
    while ($row = $result->fetch_assoc()) {
        $hashed_password = password_hash($row['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $row['id']);
        $stmt->execute();
    }

    echo "Passwords have been hashed successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/logo.png">
    <title>Hash Passwords</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 50px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Hash Passwords</h1>
    <p>Click the button below to hash all passwords in the database.</p>
    <form method="POST">
        <button type="submit" name="hash_passwords">Hash Passwords</button>
    </form>
</body>
</html>