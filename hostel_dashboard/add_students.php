<?php
session_start();
require 'config.php';

// Ensure only authorized users can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Fetch the hostel name (admin_name) associated with the logged-in admin
$hostel_name = $_SESSION['admin_name'];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = trim($_POST['name']);
    $roll_number = trim($_POST['roll_number']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);


    if (empty($name) || empty($roll_number) || empty($email) || empty($password)) {
        die("All fields are required.");
    }


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }


    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO students (name, roll_number, hostel_name, email, password) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("sssss", $name, $roll_number, $hostel_name, $email, $hashed_password);
    if ($stmt->execute()) {
        header("Location: view_students.php");
        exit;
    } else {
        die("Error saving student details: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/logo.png">
    <title>Add Students</title>
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
            margin-bottom: 30px; /* Added space below the heading */
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Added shadow for the container */
            max-width: 500px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-group input[readonly] {
            background-color: #f9f9f9;
            cursor: not-allowed;
        }

        .submit-btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 12px 20px;
            font-size: 18px;
            font-weight: 500;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        h2{
            color: #000000;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .submit-btn:hover {
            background-color: #0056b3;
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
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">Hostel Dashboard</div>

        <ul>
        <li><a href="../hostel_dashboard.php"  id="homeButton"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="#" class="active" id="addStudentButton"><i class="fas fa-plus-circle"></i> Add Student Details</a></li>
            <li><a href="view_students.php"><i class="fas fa-eye"></i> View Students</a></li>
            <li><a href="vote_items.php" id="addStudentButton"><i class="fas fa-eye"></i> Current Voting Menu</a></li>
            <li><a href="add_menu.php" id="addStudentButton"><i class="fas fa-plus-circle"></i> Add to Menu</a></li>
            <li><a href="view_voting_result.php" id="addStudentButton"><i class="fas fa-eye"></i> View Voting Results</a></li>
            <li><a href="grievances.php" id="addStudentButton"><i class="fas fa-comment-dots"></i> View Grievances</a></li>
        </ul>

        <div class="logout-button">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>  Logout</a>
        </div>
    </div>
    <div class="main-content">
        <div class="greeting">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h2> <!-- Display admin name -->
        </div>

        <div class="datetime" id="datetime"></div> <!-- Clock for Date and Time -->

        <h1>Add Students</h1>
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="roll_number">Roll Number</label>
                    <input type="text" id="roll_number" name="roll_number" required>
                </div>
                <div class="form-group">
                    <label for="email">Email ID</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="hostel_name">Hostel Name</label>
                    <input type="text" id="hostel_name" name="hostel_name" value="<?php echo htmlspecialchars($hostel_name); ?>" readonly>
                </div>
                <button type="submit" class="submit-btn">Add Student</button>
            </form>
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