<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/logo.png">
    <title>Add Hostel Login Credentials</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            background-color: #f4f4f4;
            display: flex;
            height: 100vh; 
            margin: 0; 
            overflow: hidden; 
        }

        .main-content {
            margin-left: 270px;
            width: calc(100% - 270px);
            padding: 20px;
            overflow: auto; 
            height: 100vh; 
            box-sizing: border-box; 
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
            padding: 20px;
            overflow: auto; 
            height: 100vh; 
            box-sizing: border-box; 
        }
   
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 95%;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-group label {
            font-size: 18px;
            margin-right: 15px;
            width: 30%;
        }

        .form-group input {
            width: 40%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            margin-right:380px;
        }

        .btn-submit {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            width: 20%;
            margin-top: 20px;
        }

        .btn-submit:hover {
            background-color: #0056b3;
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
            <li><a href="../main_hostel_dashboard.php" class="sidebar-link" id="homeButton">
            <i class="fas fa-home"></i> Home</a></li>
            <li><a href="add_hostel.php" class="sidebar-link active">
            <i class="fas fas fa-plus-circle"></i> Add Hostel Credentials</a></li>
            <li><a href="view_hostel.php" class="sidebar-link">
            <i class="fas fa-eye"></i> View Hostel Credentials</a></li>
        </ul>

        <div class="logout-button">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>


    <div class="main-content">
        <div class="form-container">
            <h1>Add Hostel Login Credentials</h1>
            <form action="save_hostel.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-submit">Add Hostel</button>
            </form>
            <div class="back-button">
                <a href="../main_hostel_dashboard.php">Back to Dashboard</a>
            </div>
        </div>
    </div>

</body>
</html>
