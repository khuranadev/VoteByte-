<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SESSION['email'] !== 'hostel.admin@kiit.ac.in') {
    header("Location: index.php");
    exit;
}

$result = $conn->query("SELECT id, username, email FROM hostels WHERE email != 'hostel.admin@kiit.ac.in'");


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $hostel_id = $_POST['hostel_id'];
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);  // Hash the new password

    $stmt = $conn->prepare("UPDATE hostels SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $hostel_id);
    $stmt->execute();
    $stmt->close();

    echo "Password reset successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/logo.png">
    <title>View Hostel Login Credentials</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>

        body {
            font-family: 'Roboto', sans-serif; 
            background-color: #f4f4f4;
            display: flex;
            font-weight: 700;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            padding-top: 20px;
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 40px;
            font-size: 24px;
            font-weight: 600;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin: 20px 0;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: #ddd;
            font-size: 18px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .sidebar ul li a:hover {
            background-color: #007bff;
            color: #fff;
            transform: translateX(5px);
        }

        .sidebar ul li a.active {
            background-color: #0056b3;
        }

        .main-content {
            margin-left: 270px;
            width: calc(100% - 270px);
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        table {
        min-width: 700px;
        width: 100%;
        border-collapse: collapse;
        }

        th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        white-space: nowrap; 
        }

        th {
        background: #3498db;
        color: #fff;
        }
        
        th:nth-child(1), td:nth-child(1) { width: 20%; } /* Username */
        th:nth-child(2), td:nth-child(2) { width: 20%; } /* Email */
        th:nth-child(3), td:nth-child(3) { width: 10%; } /* Actions */
        th:nth-child(4), td:nth-child(4) { width: 30%; } /* Reset Password */

        .actions a {
            margin-right: 10px;
            color: #3498db;
            text-decoration: none;
        }

        .actions a:hover {
            text-decoration: underline;
        }

        .reset-password-form input {
        width: 70%;
        min-width: 150px;}

        .reset-password-form button {
            padding: 5px 10px;
            background-color: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .reset-password-form button:hover {
            background-color: #218838;
        }

        .back-button {
            margin-top: 20px;
        }

        .back-button a {
            color: #3498db;
            text-decoration: none;
            font-size: 16px;
        }

        .back-button a:hover {
            text-decoration: underline;
        }

        .sidebar .logout-button {
            position: absolute;
            bottom: 40px;
            left: 0;
            width: 100%;
        }

        .sidebar .logout-button a {
            display: block;
            padding: 12px 20px;
            background-color: #dc3545;
            color: #fff;
            text-decoration: none;
            font-size: 18px;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .sidebar .logout-button a:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    
    <div class="sidebar">
        <div class="logo">Hostel Admin</div>
        <ul>
            <li> <a href="../main_hostel_dashboard.php" class="sidebar-link" id="homeButton">
            <i class="fas fa-home"></i> Home</a></li>
            <li> <a href="add_hostel.php" class="sidebar-link">
            <i class="fas fas fa-plus-circle"></i> Add Hostel Credentials
        </a></li> 
        <li> <a href="view_hostel.php" class="sidebar-link active">
            <i class="fas fa-eye"></i> View Hostel Credentials
        </a></li> 
    </ul>

        <div class="logout-button">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>  Logout</a>
        </div><br>
    </div>

    <div class="main-content">
        <div class="form-container">
            <h1>View Hostel Login Credentials</h1>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                        <th>Reset Password</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td class="actions">
                                <a href="edit_hostel.php?id=<?php echo $row['id']; ?>">Edit</a>
                                <a href="delete_hostel.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this hostel?');">Delete</a>
                            </td>
                            <td>

                                <form class="reset-password-form" action="" method="POST">
                                    <input type="hidden" name="hostel_id" value="<?php echo $row['id']; ?>">
                                    <input type="password" name="new_password" placeholder="Enter new password" required>
                                    <button type="submit" name="reset_password">Reset</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="back-button">
                <a href="../main_hostel_dashboard.php">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <script>

        function updateClock() {
            var now = new Date();
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            var seconds = now.getSeconds().toString().padStart(2, '0');
            var timeString = hours + ':' + minutes + ':' + seconds;
            document.getElementById('clock').innerText = timeString;
        }
        setInterval(updateClock, 1000); 
        updateClock(); 
    </script>
</body>
</html>
