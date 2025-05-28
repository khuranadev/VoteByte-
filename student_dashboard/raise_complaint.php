<?php
session_start();
if ($_SESSION['role'] !== "student") {
    header("Location: ../index.php");
    exit;
}

require '../config.php';

$message = ""; // Variable to store success/error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch student details from session or database
    $student_email = $_SESSION['email'];

    // Fetch hostel name from the database
    $query = $conn->prepare("SELECT hostel_name FROM students WHERE email = ?");
    $query->bind_param("s", $student_email);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    $hostel_name = $row['hostel_name'];

    // Sanitize the complaint input
    $complaint_text = mysqli_real_escape_string($conn, $_POST['complaint']);

    
    $query = "
        INSERT INTO complaints (student_email, hostel_name, complaint_text, complaint_date, status) 
        VALUES (?, ?, ?, NOW(), 'Pending')
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $student_email, $hostel_name, $complaint_text);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = "<div class='alert success'>Complaint submitted successfully!</div>";
    } else {
        $message = "<div class='alert error'>Failed to submit complaint. Please try again.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/logo.png">
    <title>Raise a Complaint</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Puddles&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Grandstander:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Grandstander', cursive;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6, #fbbf24);
            color: #fff;
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
            padding: 20px 40px; 
            width: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .top-bar {
            width: 100%;
            background-color: rgba(255, 255, 255, 0.2); 
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            position: relative; 
        }

        .greeting {
            position: absolute;
            left: 20px;
            top: 50%; 
            transform: translateY(-50%); 
        }

        .greeting h2 {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .greeting h1 {
            font-size: 22px;
            font-weight: 600;
        }

        .vote-byte {
            font-family: 'Rubik Puddles', cursive; 
            font-size: 62px;
            font-weight: 600;
            color: #fff;
            text-align: center;
            flex: 1;
        }

        .datetime {
            position: absolute; 
            right: 20px; 
            top: 50%;
            transform: translateY(-50%); 
            font-size: 18px;
            font-weight: 500;
        }

        .complaint-form {
            width: 100%;
            max-width: 800px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .complaint-form textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: none;
            background-color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
            margin-bottom: 20px;
            resize: vertical;
        }

        .complaint-form button {
            padding: 10px 20px;
            background-color: #3b82f6;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .complaint-form button:hover {
            background-color: #2563eb;
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

        /* Alert Box Styles */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: 500;
            text-align: center;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">Student Dashboard</div>
        <ul>
            <li><a href="../student_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="menu.php"><i class="fas fa-list-alt"></i> View Menu</a></li>
            <li><a href="vote_menu.php"><i class="fas fa-utensils"></i> Vote Menu</a></li>
            <li><a href="view_results.php"><i class="fas fa-poll"></i> View Voting Results</a></li>
            <li><a href="#" class="active"><i class="fas fa-comment-dots"></i> Raise Complaint</a></li>
        </ul>
        <div class="logout-button">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="greeting">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Student'); ?></h2>
                <h1>STUDENT DASHBOARD</h1>
            </div>
            <div class="vote-byte">VOTEBYTE</div>
            <div class="datetime" id="datetime"></div>
        </div>

        <!-- Complaint Form -->
        <div class="complaint-form">
            <h1>Raise a Complaint</h1>
            <?php echo $message; // Display success/error message ?>
            <form method="POST">
                <textarea name="complaint" rows="5" placeholder="Enter your complaint here"></textarea>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <!-- JavaScript for Live Date and Time -->
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