<?php
include '../config.php';
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];
$query = $conn->prepare("SELECT hostel_name FROM students WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();
$hostel_name = $row['hostel_name']; // Corrected variable name
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/logo.png">
    <title>Mess Voting</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Puddles&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Grandstander:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        {
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

        .vote-menu {
            width: 100%;
            max-width: 800px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .vote-menu label {
            display: block;
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: 500;
        }

        .vote-menu select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: none;
            background-color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
            margin-bottom: 20px;
        }

        .save-button {
            padding: 10px 20px;
            background-color: #3b82f6;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .save-button:hover {
            background-color: #2563eb;
        }

        .progress-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .progress-list li {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: 500;
        }

        .final-vote-section {
            text-align: center;
            margin-top: 40px;
        }

        .submit-button {
            padding: 15px 30px;
            background-color: #10b981; /* Green color for final action */
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
            max-width: 350px;
            margin: 10px auto;
            display: block;
        }

        .submit-button:hover {
            background-color: #059669;
        }

        .submit-button:disabled {
            background-color: #6b7280;
            cursor: not-allowed;
        }

        .final-vote-note {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 10px;
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
        <div class="logo">Student Dashboard</div>
        <ul>
            <li><a href="../student_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="menu.php"><i class="fas fa-list-alt"></i> View Menu</a></li>
            <li><a href="vote_menu.php" class="active"><i class="fas fa-utensils"></i> Vote Menu</a></li>
            <li><a href="view_results.php"><i class="fas fa-poll"></i> View Voting Results</a></li>
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

        <!-- Vote Menu -->
        <div class="vote-menu">
            <h2>Mess Voting - <?php echo $hostel_name; ?></h2>
            <p>Logged in as: <?php echo $_SESSION['email']; ?> | <a href="../logout.php">Logout</a></p>

            <label for="weekday">Select Weekday:</label>
            <select id="weekday">
                <option value="" disabled selected>Select a day</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
            </select>

            <div id="meal-options" style="display: none;">
                <h3>Meals for <span id="selected-day"></span></h3>

                <label for="breakfast">Breakfast:</label>
                <select id="breakfast"></select><br>

                <label for="lunch">Lunch:</label>
                <select id="lunch"></select><br>

                <label for="snacks">Snacks:</label>
                <select id="snacks"></select><br>

                <label for="dinner">Dinner:</label>
                <select id="dinner"></select><br>

                <!-- Save Vote Button -->
                <button id="save-vote" class="save-button">Save Vote for <span id="selected-day-button"></span></button>
            </div>

            <h3>Votes Progress</h3>
            <ul id="progress" class="progress-list"></ul>
            
            <!-- Submit Final Vote Button -->
            <div class="final-vote-section">
                <button id="submit-vote" class="submit-button" disabled>Submit Final Vote</button>
                <p class="final-vote-note">Note: Once submitted, votes cannot be changed.</p>
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

        document.querySelectorAll('.sidebar ul li a').forEach(link => {
            link.addEventListener('click', () => {
                document.querySelectorAll('.sidebar ul li a').forEach(item => item.classList.remove('active'));
                link.classList.add('active');
            });
        });

        let votes = {}; 

        $("#weekday").change(function() {
    let selectedDay = $(this).val();
    $("#selected-day").text(selectedDay);
    $("#selected-day-button").text(selectedDay); // Update button text
    $("#meal-options").show();

    // Fetch meal items for the selected weekday and hostel
    $.ajax({
        url: "fetch_meals.php", // Path to the fetch_meals.php file
        type: "POST",
        data: { 
            weekday: selectedDay,
            hostel_name: "<?php echo $hostel_name; ?>" // Pass the hostel name
        },
        success: function(response) {
            let mealOptions = JSON.parse(response);

            if (mealOptions) {
                // Populate each dropdown with the correct meal options
                $("#breakfast").html(generateOptions(mealOptions.breakfast));
                $("#lunch").html(generateOptions(mealOptions.lunch));
                $("#snacks").html(generateOptions(mealOptions.snacks));
                $("#dinner").html(generateOptions(mealOptions.dinner));
            } else {
                alert("Error fetching menu items.");
            }
        },
        error: function(xhr, status, error) {
            alert("AJAX request failed: " + error);
        }
    });
});

function generateOptions(items) {
    let options = '<option value="" disabled selected>Select an option</option>';
    if (Array.isArray(items)) {
        items.forEach(item => {
            options += `<option value="${item}">${item}</option>`;
        });
    }
    return options;
}

        $("#save-vote").click(function() {
            let day = $("#weekday").val();
            let breakfast = $("#breakfast").val();
            let lunch = $("#lunch").val();
            let snacks = $("#snacks").val();
            let dinner = $("#dinner").val();

            if (!breakfast || !lunch || !snacks || !dinner) {
                alert("Please select all meal options!");
                return;
            }

            votes[day] = { breakfast, lunch, snacks, dinner };

            updateProgress();
        });

        function updateProgress() {
            let progressList = $("#progress");
            progressList.empty();
            let daysVoted = Object.keys(votes).length;

            for (let day in votes) {
                let mealData = votes[day];
                progressList.append(`<li>${day}: B-${mealData.breakfast}, L-${mealData.lunch}, S-${mealData.snacks}, D-${mealData.dinner}</li>`);
            }

            $("#submit-vote").prop("disabled", daysVoted !== 7);
        }

        $("#submit-vote").click(function() {
    $.ajax({
        url: "submit_vote.php",
        type: "POST",
        data: { 
            votes: JSON.stringify(votes),
            hostel_name: "<?php echo $hostel_name; ?>" // Pass hostel_name
        },
        success: function(response) {
            alert(response);
        },
        error: function(xhr, status, error) {
            alert("Error submitting votes: " + error);
        }
    });
});
    </script>
</body>
</html>