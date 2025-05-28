<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$hostel_name = $_SESSION['admin_name'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

$weekday = $meal_type = $items = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weekday = $_POST['weekday'];
    $meal_type = $_POST['meal_type'];
    $items = $_POST['items'];

    if (empty($weekday) || empty($meal_type) || empty($items)) {
        $message = "All fields are required.";
    } else {
        $insert_query = $conn->prepare("INSERT INTO menu_items (hostel_name, weekday, meal_type, items) VALUES (?, ?, ?, ?)");
        $insert_query->bind_param("ssss", $hostel_name, $weekday, $meal_type, $items);

        if ($insert_query->execute()) {
            $message = "Menu item added successfully!";
        } else {
            $message = "Error adding menu item: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/logo.png">
    <title>Add Menu</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
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

        .results-container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            gap: 20px;
        }

        .weekdays-column, .meals-column, .items-column {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            flex: 1;
            margin-bottom: 30px;
        }

        .weekdays-column {
            max-width: 200px;
        }

        .meals-column {
            max-width: 200px;
        }

        .items-column {
            flex: 2;
        }

        .weekday, .meal-type {
            cursor: pointer;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .weekday:hover, .meal-type:hover {
            background-color: #e9e9e9;
        }

        .weekday.active, .meal-type.active {
            background-color: #007bff;
            color: #fff;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th, .items-table td {
            padding: 10px;
            margin-bottom: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        h2 {
            color: #000000;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        h3 {
            color: #000000;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .items-table th {
            background-color: #007bff;
            color: #fff;
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

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        select, input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
            height: 150px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">Hostel Dashboard</div>
        <ul>
            <li><a href="../hostel_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="add_students.php"><i class="fas fa-plus-circle"></i> Add Student Details</a></li>
            <li><a href="view_students.php"><i class="fas fa-eye"></i> View Students</a></li>
            <li><a href="vote_items.php"><i class="fas fa-eye"></i> Current Voting Menu</a></li>
            <li><a href="add_menu.php" class="active"><i class="fas fa-plus-circle"></i> Add to Menu</a></li>
            <li><a href="view_voting_result.php"><i class="fas fa-eye"></i> View Voting Results</a></li>
            <li><a href="grievances.php"><i class="fas fa-comment-dots"></i> View Grievances</a></li>
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

        <h1>Add Menu Items for <?= htmlspecialchars($hostel_name) ?></h1>
        <div class="results-container">
            <div class="weekdays-column">
                <h2>Weekdays</h2>
                <div class="weekday" onclick="selectWeekday('Monday')">Monday</div>
                <div class="weekday" onclick="selectWeekday('Tuesday')">Tuesday</div>
                <div class="weekday" onclick="selectWeekday('Wednesday')">Wednesday</div>
                <div class="weekday" onclick="selectWeekday('Thursday')">Thursday</div>
                <div class="weekday" onclick="selectWeekday('Friday')">Friday</div>
                <div class="weekday" onclick="selectWeekday('Saturday')">Saturday</div>
                <div class="weekday" onclick="selectWeekday('Sunday')">Sunday</div>
            </div>

            <div class="meals-column" id="meals-column">
                <h2>Meal Types</h2>
                <div class="meal-type" onclick="selectMealType('breakfast')">Breakfast</div>
                <div class="meal-type" onclick="selectMealType('lunch')">Lunch</div>
                <div class="meal-type" onclick="selectMealType('snacks')">Snacks</div>
                <div class="meal-type" onclick="selectMealType('dinner')">Dinner</div>
            </div>

            <div class="items-column" id="items-column">
                <h2>Enter Menu Items</h2>
                <form action="add_menu.php" method="post">
                    <input type="hidden" name="weekday" id="selected-weekday">
                    <input type="hidden" name="meal_type" id="selected-meal-type">
                    <label for="items">Menu Items:</label>
                    <textarea name="items" id="items" placeholder="Enter menu items (e.g., Bread/Butter/Jam, Paneer/Rice/Curd)" required></textarea>
                    <button type="submit">Add Menu Item</button>
                </form>
                <?php if (!empty($message)): ?>
                    <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
            </div>
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

        function selectWeekday(weekday) {
            document.querySelectorAll('.weekday').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.weekday').forEach(el => {
                if (el.innerText.trim() === weekday) {
                    el.classList.add('active');
                }
            });
            document.getElementById('selected-weekday').value = weekday;
        }

        function selectMealType(mealType) {

    document.querySelectorAll('.meal-type').forEach(el => el.classList.remove('active'));
    
    const mealTypeElements = document.querySelectorAll('.meal-type');
    mealTypeElements.forEach(el => {
        if (el.innerText.trim().toLowerCase() === mealType.toLowerCase()) {
            el.classList.add('active');
        }
    });

    document.getElementById('selected-meal-type').value = mealType;
}
    </script>
</body>
</html>