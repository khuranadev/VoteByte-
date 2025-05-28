<?php
session_start();
require 'config.php';

$message = ""; // Variable to store success or error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id FROM students WHERE email = ? UNION SELECT id FROM hostels WHERE email = ?");
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Here you would typically send an email with reset instructions
        $message = "Password reset instructions have been sent to your email.";
    } else {
        $message = "No account found with that email.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/logo.png">
    <title>Forgot Password</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Grandstander', cursive;
            background: radial-gradient(
                circle,
                #ffa200 0%, /* Center */
                #cc00ff 100% /* Vignette */
            );
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('assets/food-icons.png') repeat;
            opacity: 1;
            z-index: -1;
        }

        .forgot-password-container {
            background: rgba(30, 28, 28, 0.91);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 2s ease-in-out 0.5s forwards;
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
            font-size: 42px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-family: 'Luckiest Guy', cursive;
            background: rgb(255, 255, 255);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 1s ease-out 1s forwards;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 1s ease-out 1.5s forwards;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: rgb(178, 178, 178);
            font-size: 15px;
            font-weight: 600;
        }

        .form-group input {
            width: 93%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            color: #333;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-family: 'Grandstander', cursive;
            font-weight: 500;
        }

        .form-group input:focus {
            border-color: rgb(169, 0, 254);
            outline: none;
            box-shadow: 0 0 8px rgba(52, 199, 89, 0.3);
        }

        .btn-reset {
            background: rgba(133, 93, 244, 0.94);
            color: #fff;
            border: none;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 18px;
            font-family: 'Luckiest Guy', cursive;
            font-weight: 100;
            cursor: pointer;
            width: 70%;
            transition: background 0.3s ease, transform 0.2s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 1s ease-out 2s forwards;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
        }

        .message {
            color: #ff3b30;
            margin-bottom: 20px;
            font-size: 14px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 1s ease-out 2.5s forwards;
            font-weight: 600;
        }

        .message.success {
            color: #34C759;
        }

        .back-to-login {
            margin-top: 20px;
            font-size: 15px;
            color: #ff3b30;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 1s ease-out 3s forwards;
            font-family: 'Grandstander', cursive;
            font-weight: 600;
        }

        .back-to-login a {
            color: #ff3b30;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-to-login a:hover {
            color: #ff1a1a;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Puddles&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Grandstander:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="forgot-password-container">
        <h1>Forgot Password?</h1>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'sent') !== false) ? 'success' : ''; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn-reset">Send Reset Instructions</button>
        </form>
        <div class="back-to-login">
            <a href="index.php">Back to Login</a>
        </div>
    </div>
</body>
</html>