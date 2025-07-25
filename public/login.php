<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* full height */
            margin: 0;
        }

        .login-card {
            background: #fff;
            padding: 30px;
            width: 400px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            text-align: center;
        }

        .login-card h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        .login-card label {
            display: block;
            text-align: left;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        .login-card input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .login-card button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s ease-in-out;
        }

        .login-card button:hover {
            background: #0056b3;
        }

        .login-card p {
            font-size: 14px;
            margin-top: 15px;
            color: #555;
        }
    </style>
</head>

<body>

<div class="login-card">
    <h2>Login</h2>

    <!-- ✅ Show error if login failed -->
    <?php if (isset($_GET['error'])): ?>
        <div id="error-message" class="show">
             Invalid email or password!
        </div>
    <?php else: ?>
        <div id="error-message"></div>
    <?php endif; ?>

    <form id="loginForm" method="POST" action="../actions/auth.php">
        <label>Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>

        <label>Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

        <button type="submit" name="login">Login</button>
    </form>
    <p>Welcome back to the Student Management System</p>
</div>

<script src="assets/validation.js"></script>
</body>
</html>