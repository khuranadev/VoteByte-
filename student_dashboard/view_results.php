<?php
session_start();
if ($_SESSION['role'] !== "student") {
    header("Location: ../index.php");
    exit;
}

require '../config.php';

// Fetch the student's hostel name from the session or database
$email = $_SESSION['email'];
$query = $conn->prepare("SELECT hostel_name FROM students WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();
$hostel_name = $row['hostel_name'];

// Query to fetch voting results grouped by weekday and meal type
$query = "
    SELECT 
        weekday, 
        breakfast AS item, 
        'breakfast' AS meal_type, 
        COUNT(*) AS vote_count 
    FROM votes 
    WHERE hostel_name = ? 
    GROUP BY weekday, breakfast
    UNION ALL
    SELECT 
        weekday, 
        lunch AS item, 
        'lunch' AS meal_type, 
        COUNT(*) AS vote_count 
    FROM votes 
    WHERE hostel_name = ? 
    GROUP BY weekday, lunch
    UNION ALL
    SELECT 
        weekday, 
        snacks AS item, 
        'snacks' AS meal_type, 
        COUNT(*) AS vote_count 
    FROM votes 
    WHERE hostel_name = ? 
    GROUP BY weekday, snacks
    UNION ALL
    SELECT 
        weekday, 
        dinner AS item, 
        'dinner' AS meal_type, 
        COUNT(*) AS vote_count 
    FROM votes 
    WHERE hostel_name = ? 
    GROUP BY weekday, dinner
    ORDER BY weekday, meal_type, vote_count DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $hostel_name, $hostel_name, $hostel_name, $hostel_name);
$stmt->execute();
$result = $stmt->get_result();

// Organize data into a nested array for easier display
$data = [];
while ($row = $result->fetch_assoc()) {
    $weekday = $row['weekday'];
    $meal_type = $row['meal_type'];
    $item = $row['item'];
    $vote_count = $row['vote_count'];

    if (!isset($data[$weekday])) {
        $data[$weekday] = [];
    }
    if (!isset($data[$weekday][$meal_type])) {
        $data[$weekday][$meal_type] = [];
    }
    $data[$weekday][$meal_type][] = [
        'item' => $item,
        'vote_count' => $vote_count
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/logo.png">
    <title>Voting Results</title>
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

        .results-container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            gap: 20px;
        }

        .weekdays-column, .meals-column, .items-column {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
            flex: 1;
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
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .weekday:hover, .meal-type:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .weekday.active, .meal-type.active {
            background-color: rgba(255, 255, 255, 0.5); /* Transparent white for active selection */
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th, .items-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .items-table th {
            background-color: rgba(255, 255, 255, 0.2);
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
            <li><a href="view_results.php" class="active"><i class="fas fa-poll"></i> View Voting Results</a></li>
            <li><a href="raise_complaint.php"><i class="fas fa-comment-dots"></i> Raise Complaint</a></li>
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

        <!-- Voting Results -->
        <div class="results-container">
            <!-- Left Column: Weekdays -->
            <div class="weekdays-column">
                <h2>Weekdays</h2>
                <?php
                $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                foreach ($weekdays as $weekday) :
                    if (isset($data[$weekday])) :
                ?>
                        <div class="weekday" onclick="showMeals('<?php echo $weekday; ?>')">
                            <?php echo $weekday; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Middle Column: Meal Types -->
            <div class="meals-column" id="meals-column">
                <h2>Meal Types</h2>
                <!-- Meal types will be dynamically populated here -->
            </div>

            <!-- Right Column: Items -->
            <div class="items-column" id="items-column">
                <h2>Items</h2>
                <!-- Items will be dynamically populated here -->
            </div>
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

        // Function to show meal types for the selected weekday
        function showMeals(weekday) {
            const mealsColumn = document.getElementById("meals-column");
            mealsColumn.innerHTML = `<h2>Meal Types</h2>`;

            // Remove active class from all weekdays
            document.querySelectorAll('.weekday').forEach(el => el.classList.remove('active'));

            // Add active class to the selected weekday
            const selectedWeekday = Array.from(document.querySelectorAll('.weekday')).find(el => el.innerText === weekday);
            if (selectedWeekday) {
                selectedWeekday.classList.add('active');
            }

            const mealTypes = ['breakfast', 'lunch', 'snacks', 'dinner'];
            mealTypes.forEach(mealType => {
                const mealDiv = document.createElement("div");
                mealDiv.className = "meal-type";
                mealDiv.innerText = mealType.charAt(0).toUpperCase() + mealType.slice(1);
                mealDiv.onclick = () => showItems(weekday, mealType);
                mealsColumn.appendChild(mealDiv);
            });
        }

        // Function to show items for the selected meal type
        function showItems(weekday, mealType) {
            const itemsColumn = document.getElementById("items-column");
            itemsColumn.innerHTML = `<h2>Items</h2>`;

            // Remove active class from all meal types
            document.querySelectorAll('.meal-type').forEach(el => el.classList.remove('active'));

            // Add active class to the selected meal type
            const selectedMealType = Array.from(document.querySelectorAll('.meal-type')).find(el => el.innerText.toLowerCase() === mealType);
            if (selectedMealType) {
                selectedMealType.classList.add('active');
            }

            const data = <?php echo json_encode($data); ?>;
            const items = data[weekday][mealType];

            if (items && items.length > 0) {
                const table = document.createElement("table");
                table.className = "items-table";
                table.innerHTML = `
                    <tr>
                        <th>Item</th>
                        <th>Votes</th>
                    </tr>
                `;

                items.forEach(item => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${item.item}</td>
                        <td>${item.vote_count}</td>
                    `;
                    table.appendChild(row);
                });

                itemsColumn.appendChild(table);
            } else {
                itemsColumn.innerHTML += `<p>No items found for ${mealType}.</p>`;
            }
        }
    </script>
</body>
</html>