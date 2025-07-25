<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/style.css"> <!-- ✅ Correct CSS path -->
    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #333;
        }
        p {
            font-size: 18px;
            margin-bottom: 20px;
            color: #555;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            margin: 12px 0;
        }
        ul li a {
            display: block;
            text-decoration: none;
            color: #fff;
            background: #007bff;
            padding: 12px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        ul li a:hover {
            background: #0056b3;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
        }
        .logout-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome!</h1>

    <?php if ($role == 1): ?>
        <p>You are an <strong>Admin</strong>!</p>
        <ul>
            <li><a href="admin/users.php">👤 Manage Users</a></li>
            <li><a href="admin/classes.php">🏫 Manage Classes</a></li>
            <li><a href="admin/enrollments.php">📋 Manage Enrollments</a></li>
        </ul>

    <?php elseif ($role == 2): ?>
        <?php header("Location: teacher/dashboard.php"); exit(); ?>

    <?php else: ?>
        <p>You are a <strong>Student</strong>!</p>
        <ul>
            <li><a href="student/assignments.php">📥 View Assignments</a></li>
            <li><a href="student/my_submissions.php">📄 My Submissions</a></li>
            <li><a href="student/attendance.php">📅 My Attendance</a></li>
        </ul>
    <?php endif; ?>

    <a class="logout-btn" href="logout.php">🚪 Logout</a>
</div>

</body>
</html>
