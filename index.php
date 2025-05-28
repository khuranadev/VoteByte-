<?php
session_start();
require 'config.php';

$error_message = ""; // Variable to store error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = trim($_POST['username_or_email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Validate inputs
    if (empty($username_or_email) || empty($password) || empty($role)) {
        $error_message = "All fields are required.";
    } else {
        // Prepare the statement based on the selected role
        if ($role === 'admin') {
            $stmt = $conn->prepare("SELECT id, password, username FROM hostels WHERE email = ?");
        } else {
            $stmt = $conn->prepare("SELECT id, password, name FROM students WHERE email = ?");
        }

        if (!$stmt) {
            $error_message = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("s", $username_or_email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $stored_password, $name); // Fetch the name
                $stmt->fetch();

                
                if (password_verify($password, $stored_password)) {
                    
                    session_regenerate_id(true);

                    
                    $_SESSION['user_id'] = $id;
                    $_SESSION['role'] = $role;
                    $_SESSION['email'] = $username_or_email;
                    $_SESSION['admin_name'] = $name;
                    $_SESSION['student_name'] = $name; 

                    // Redirect based on role and email
                    if ($role === "admin") {
                        if ($username_or_email === 'hostel.admin@kiit.ac.in') {
                            header("Location: main_hostel_dashboard.php");
                        } else {
                            header("Location: hostel_dashboard.php");
                        }
                    } else {
                        header("Location: student_dashboard.php");
                    }
                    exit;
                } else {
                    $error_message = "Invalid credentials.";
                }
            } else {
                $error_message = "No account found.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/logo.png">
    <title>Login Page</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Grandstander', cursive; /* Apply Grandstander font to the entire page */
            background: radial-gradient(
                circle,
#ffa200 0%, /*Center */
#cc00ff 100% /* Vignette */
            ); /* Green gradient */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden; /* Prevent scrolling during animations */
            position: relative;
        }

        
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: 1024px 1024px; /* Adjust as needed */
            background: url('assets/food-icons.png') repeat; /* Add a food icon pattern */
            opacity: 1; /* Subtle opacity for the icons */
            z-index: -1;
        }

        #votebyte {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 120px;
            font-family: 'Rubik Puddles', cursive;
            color: white;
            font-weight: bold;
            text-align: center;
            white-space: nowrap; /* Ensure letters stay in one line */
            opacity: 0; /* Start invisible */
            animation: fadeIn 1s ease-in-out 0.5s forwards, glow 1s ease-in-out 0.5s 2;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        @keyframes glow {
            0%, 100% {
                text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
            }
            50% {
                text-shadow: 0 0 20px rgba(255, 255, 255, 1);
            }
        }

        @keyframes fallAndFadeOut {
            0% {
                transform: translate(-50%, -50%);
                opacity: 1;
            }
            100% {
                transform: translate(-50%, 100vh);
                opacity: 0;
            }
        }

        /* Login Container */
        .login-container {
            background: rgba(30, 28, 28, 0.91); /* Semi-transparent white */
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            opacity: 0; /* Start invisible */
            transform: translateY(20px); /* Start slightly below */
            animation: fadeInUp 2s ease-in-out 3.5s forwards; /* Animation */
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            margin: 0; 
            margin-bottom: 30px; 
            color: #333;
            font-size: 72px; 
            font-weight: 600; 
            text-transform: uppercase;
            letter-spacing: 4px; /* Wider letter spacing */
            font-family: 'Luckiest Guy', cursive; 
            background:rgb(255, 255, 255); 
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent; /* Gradient text effect */
            opacity: 0; /* Start invisible */
            transform: translateY(20px); /* Start slightly below */
            animation: fadeInUp 1s ease-out 4s forwards; /* Animation */
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
            opacity: 0; /* Start invisible */
            transform: translateY(20px); /* Start slightly below */
            animation: fadeInUp 1s ease-out 4.5s forwards; /* Animation */
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color:rgb(155, 155, 155);
            font-size: 14px;
            font-weight: 600; /* Bold weight for Grandstander */
        }

        .form-group input,
        .form-group select {
            width: 93%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            color: #333;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-family: 'Grandstander', cursive; /* Apply Grandstander font to inputs */
            font-weight: 500; /* Medium weight for inputs */
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color:rgb(169, 0, 254); /* Green border on focus */
            outline: none;
            box-shadow: 0 0 8px rgba(52, 199, 89, 0.3); /* Green shadow */
        }

        .role-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            opacity: 0; /* Start invisible */
            transform: translateY(20px); /* Start slightly below */
            animation: fadeInUp 1s ease-out 5s forwards; /* Animation */
            position: relative;
            padding: 10px;
            background: rgba(233, 233, 233, 0.3); /* Light gray background for the box */
            border-radius: 15px; /* Rounded corners for the box */
            border: 2px solid rgba(177, 149, 255, 0.89); /* Light green border for the box */
        }

        .role-buttons button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 700; /* Bold weight for Grandstander */
            cursor: pointer;
            background-color: #e5e5ea; /* Original button color */
            color: #333; /* Original text color */
            transition: background-color 0.3s ease, color 0.3s ease;
            font-family: 'Grandstander', cursive; /* Apply Grandstander font to role buttons */
            position: relative;
            z-index: 1;
        }

        .role-buttons button.selected {
            background-color:rgba(133, 93, 244, 0.94); /* Green for selected button */
            color: #fff; /* White text for selected button */
        }

        .role-buttons button:first-child {
            margin-right: 10px;
        }

        /* Transition effect */
        .role-buttons::before {
            content: "";
            position: absolute;
            top: 10px;
            left: 10px;
            width: calc(50% - 15px); /* Half the width minus padding and margin */
            height: calc(100% - 20px); /* Full height minus padding */
            background: rgba(133, 93, 244, 0.94); /* Light green fill */
            border-radius: 10px; /* Match the button border radius */
            transition: all 0.3s ease;
            z-index: 0;
        }

        /* Move the transition effect to the right when "Admin" is selected */
        .role-buttons[data-selected="admin"]::before {
            left: calc(50% + 5px); /* Move to the right */
        }

        .btn-login {
            background:rgba(133, 93, 244, 0.94); /* Green gradient */
            color: #fff;
            border: none;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 28px; /* Larger font size */
            font-family: 'Luckiest Guy', cursive;
            font-weight: 400;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s ease, transform 0.2s ease;
            opacity: 0; /* Start invisible */
            transform: translateY(20px); /* Start slightly below */
            animation: fadeInUp 1s ease-out 5.5s forwards; /* Animation */
        }

        .btn-login:hover {
            /* Reverse gradient on hover */
            transform: translateY(-2px);
        }

        .error-message {
            color: #ff3b30;
            margin-bottom: 20px;
            font-size: 14px;
            opacity: 0; /* Start invisible */
            transform: translateY(20px); /* Start slightly below */
            animation: fadeInUp 1s ease-out 6s forwards; /* Animation */
            font-weight: 600; /* Bold weight for Grandstander */
        }

        .forgot-password {
            margin-top: 20px;
            font-size: 14px;
            color: #ff3b30; /* Red for "Forgot Password" */
            opacity: 0; /* Start invisible */
            transform: translateY(20px); /* Start slightly below */
            animation: fadeInUp 1s ease-out 6.5s forwards; /* Animation */
            font-family: 'Grandstander', cursive; /* Apply Grandstander font to "Forgot Password" */
            font-weight: 600; /* Bold weight for Grandstander */
        }

        .forgot-password a {
            color: #ff3b30;
            text-decoration: none;
            font-weight: 600; /* Bold weight for Grandstander */
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #ff1a1a; /* Darker red on hover */
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Puddles&display=swap" rel="stylesheet"> <!-- Rubik Puddles font -->
    <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&display=swap" rel="stylesheet"> <!-- Luckiest Guy font -->
    <link href="https://fonts.googleapis.com/css2?family=Grandstander:wght@400;500;600;700&display=swap" rel="stylesheet"> <!-- Grandstander font -->
    <script>
        // JavaScript to handle role selection
        function selectRole(role) {
            const roleButtons = document.querySelector('.role-buttons');
            document.querySelectorAll('.role-buttons button').forEach(button => {
                button.classList.remove('selected');
            });
            document.querySelector(`.role-buttons button[data-role="${role}"]`).classList.add('selected');
            document.querySelector('input[name="role"]').value = role;

            // Update the transition effect direction
            roleButtons.setAttribute('data-selected', role);
        }

        // Fall and fade out animation
        setTimeout(() => {
            const votebyte = document.getElementById('votebyte');
            votebyte.style.animation = 'fallAndFadeOut 2s ease-in-out forwards';
        }, 3500); // Wait for glow to complete
    </script>
</head>
<body>
    <!-- VOTEBYTE Text -->
    <div id="votebyte">VOTEBYTE</div>

    <!-- Login Container -->
    <div class="login-container">
        <h1>LOGIN</h1>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username_or_email">Username or Email</label>
                <input type="text" id="username_or_email" name="username_or_email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="role-buttons" data-selected="student">
                <button type="button" data-role="student" onclick="selectRole('student')">Student</button>
                <button type="button" data-role="admin" onclick="selectRole('admin')">Admin</button>
            </div>
            <input type="hidden" name="role" value="student"> <!-- Default role -->
            <button type="submit" class="btn-login">LOGIN</button>
        </form>
        <div class="forgot-password">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>

    <script>
        // Set the default role to "student" and highlight it
        document.addEventListener('DOMContentLoaded', () => {
            selectRole('student');
        });
    </script>
</body>
</html>