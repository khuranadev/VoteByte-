<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$hostel_name = $_SESSION['admin_name']; // Assuming the admin's name is the hostel name

// Fetch items available for voting from the `menu_items` table
$items_query = "SELECT id, weekday, meal_type, items FROM menu_items WHERE hostel_name = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("s", $hostel_name);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Organize items by weekday and meal type for easier display
$menu_items = [];
while ($row = $items_result->fetch_assoc()) {
    $weekday = $row['weekday'];
    $meal_type = $row['meal_type'];
    $item = [
        'id' => $row['id'],
        'items' => $row['items']
    ];

    if (!isset($menu_items[$weekday])) {
        $menu_items[$weekday] = [];
    }
    if (!isset($menu_items[$weekday][$meal_type])) {
        $menu_items[$weekday][$meal_type] = [];
    }
    $menu_items[$weekday][$meal_type][] = $item; // Add the item to the array
}

// Handle item deletion
if (isset($_GET['delete_item'])) {
    $item_id = $_GET['delete_item'];
    $delete_stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ? AND hostel_name = ?");
    $delete_stmt->bind_param("is", $item_id, $hostel_name);
    if ($delete_stmt->execute()) {
        // Refresh the page after deletion
        header("Location: vote_items.php");
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
    <title>Manage Menu Items</title>
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
            right: 40px;
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
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">Hostel Dashboard</div>
        <ul>
            <li><a href="../hostel_dashboard.php" id="homeButton"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="add_students.php" id="addStudentButton"><i class="fas fa-plus-circle"></i> Add Student Details</a></li>
            <li><a href="view_students.php"><i class="fas fa-eye"></i> View Students</a></li>
            <li><a href="vote_items.php" class="active" id="addStudentButton"><i class="fas fa-eye"></i> Current Voting Menu</a></li>
            <li><a href="add_menu.php" id="addStudentButton"><i class="fas fa-plus-circle"></i> Add to Menu</a></li>
            <li><a href="view_voting_result.php" id="addStudentButton"><i class="fas fa-eye"></i> View Voting Results</a></li>
            <li><a href="grievances.php" id="addStudentButton"><i class="fas fa-comment-dots"></i> View Grievances</a></li>
        </ul>
        <div class="logout-button">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="greeting">
            <h3>Welcome, <?php echo htmlspecialchars($admin_name); ?></h3>
        </div>

        <div class="datetime" id="datetime"></div>

        <h1>Manage Menu Items</h1>
        <div class="results-container">
            <!-- Left Column: Weekdays -->
            <div class="weekdays-column">
                <h2>Weekdays</h2>
                <?php
                $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                foreach ($weekdays as $weekday) :
                    if (isset($menu_items[$weekday])) :
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

            const data = <?php echo json_encode($menu_items); ?>;
            const items = data[weekday][mealType];

            if (items && items.length > 0) {
                const table = document.createElement("table");
                table.className = "items-table";
                table.innerHTML = `
                    <tr>
                        <th>Item</th>
                        <th>Action</th>
                    </tr>
                `;

                items.forEach(item => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${item.items}</td>
                        <td>
                            <form method="GET" style="display: inline;">
                                <input type="hidden" name="delete_item" value="${item.id}">
                                <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this item?');">Delete</button>
                            </form>
                        </td>
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