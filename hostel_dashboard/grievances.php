<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$hostel_name = $_SESSION['admin_name']; // Assuming the admin's name is the hostel name

// Fetch complaints for the specific hostel
$stmt = $conn->prepare("SELECT id, student_email, complaint_text, complaint_date, status FROM complaints WHERE hostel_name = ? ORDER BY complaint_date DESC");
if (!$stmt) {
    die("Error in SQL query: " . $conn->error);
}
$stmt->bind_param("s", $hostel_name);
$stmt->execute();
$result = $stmt->get_result();

// Handle complaint status update
if (isset($_POST['update_status'])) {
    $complaint_id = $_POST['complaint_id'];
    $new_status = $_POST['status'];

    $update_stmt = $conn->prepare("UPDATE complaints SET status = ? WHERE id = ? AND hostel_name = ?");
    if (!$update_stmt) {
        die("Error in SQL query: " . $conn->error);
    }
    $update_stmt->bind_param("sis", $new_status, $complaint_id, $hostel_name);
    if ($update_stmt->execute()) {
        // Refresh the page after update
        header("Location: grievances.php");
        exit;
    }
    $update_stmt->close();
}

// Handle complaint deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_stmt = $conn->prepare("DELETE FROM complaints WHERE id = ? AND hostel_name = ?");
    if (!$delete_stmt) {
        die("Error in SQL query: " . $conn->error);
    }
    $delete_stmt->bind_param("is", $delete_id, $hostel_name);
    if ($delete_stmt->execute()) {
        // Refresh the page after deletion
        header("Location: grievances.php");
        exit;
    }
    $delete_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/logo.png">
    <title>View Complaints</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            background-color: #f4f4f4;
            display: flex;
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
            margin-left: 250px;
            padding: 40px;
            width: 100%;
            position: relative;
        }

        .main-content h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .table-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: #fff;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .status-pending {
            color: #ffc107;
        }

        .status-resolved {
            color: #28a745;
        }

        .status-in-progress {
            color: #17a2b8;
        }

        .datetime {
            position: absolute;
            top: 20px;
            right: 30px;
            text-align: right;
            font-size: 18px;
            color: #333;
            font-weight: 500;
        }

        .sidebar .logout-button {
            position: absolute;
            bottom: 20px;
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

        .update-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .update-form select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .update-form button {
            padding: 5px 10px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .update-form button:hover {
            background-color: #218838;
        }

        h2{
            color: #000000;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">Hostel Dashboard</div>

        <ul>
        <li><a href="../hostel_dashboard.php" id="homeButton"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="add_students.php" id="addStudentButton"><i class="fas fa-plus-circle"></i> Add Student Details</a></li>
            <li><a href="view_students.php"><i class="fas fa-eye"></i> View Students</a></li>
            <li><a href="vote_items.php" id="addStudentButton"><i class="fas fa-eye"></i> Current Voting Menu</a></li>
            <li><a href="add_menu.php" id="addStudentButton"><i class="fas fa-plus-circle"></i> Add to Menu</a></li>
            <li><a href="view_voting_result.php" id="addStudentButton"><i class="fas fa-eye"></i> View Voting Results</a></li>
            <li><a href="#" class="active" id="addStudentButton"><i class="fas fa-comment-dots"></i> View Grievances</a></li>
        </ul>

        <div class="logout-button">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="main-content">
        <div class="greeting">
            <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?></h2>
        </div>

        <div class="datetime" id="datetime"></div>

        <h1>View Complaints</h1>
        <div class="table-container">
            <table>
                <tr>
                    <th>Student Email</th>
                    <th>Complaint</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_email']); ?></td>
                    <td><?php echo htmlspecialchars($row['complaint_text']); ?></td>
                    <td><?php echo htmlspecialchars($row['complaint_date']); ?></td>
                    <td class="status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                        <?php echo htmlspecialchars($row['status']); ?>
                    </td>
                    <td>
                        <?php if ($row['status'] !== 'Resolved'): ?>
                            <form class="update-form" method="POST">
                                <input type="hidden" name="complaint_id" value="<?php echo $row['id']; ?>">
                                <select name="status">
                                    <option value="Pending" <?php echo $row['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Resolved" <?php echo $row['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </form>
                        <?php else: ?>
                            <a href="grievances.php?delete_id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this resolved complaint?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <script>
        function getOrdinal(n) {
            if (n % 10 === 1 && n !== 11) {
                return n + "st";
            } else if (n % 10 === 2 && n !== 12) {
                return n + "nd";
            } else if (n % 10 === 3 && n !== 13) {
                return n + "rd";
            } else {
                return n + "th";
            }
        }

        function updateClock() {
            var now = new Date();

            var dayOfWeek = now.toLocaleString('en-us', { weekday: 'long' });
            var day = now.getDate();
            var month = now.toLocaleString('en-us', { month: 'long' });
            var year = now.getFullYear();
            var formattedDate = `${getOrdinal(day)} ${month} ${year}`;

            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            var seconds = now.getSeconds().toString().padStart(2, '0');
            var formattedTime = `${hours}:${minutes}:${seconds}`;

            document.getElementById("datetime").innerHTML = `
                <div>${dayOfWeek}</div>
                <div>${formattedDate}</div>
                <div>${formattedTime}</div>
            `;
        }

        setInterval(updateClock, 1000);

        document.querySelectorAll('.sidebar ul li a').forEach(link => {
            link.addEventListener('click', () => {
                document.querySelectorAll('.sidebar ul li a').forEach(item => item.classList.remove('active'));
                link.classList.add('active');
            });
        });
    </script>
</body>
</html>