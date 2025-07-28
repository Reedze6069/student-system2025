<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role_id'];

// Redirect teachers immediately
if ($role == 2) {
    header("Location: teacher/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dashboard-page">
<div class="dashboard-wrapper">
    <div class="dashboard-card student-view">
        <h1>Welcome!</h1>

        <?php if ($role == 1): ?>
            <p>You are an <strong>Admin</strong>!</p>
            <a href="admin/users.php" class="dashboard-btn">👤 Manage Users</a>
            <a href="admin/classes.php" class="dashboard-btn">🏫 Manage Classes</a>
            <a href="admin/enrollments.php" class="dashboard-btn">📋 Manage Enrollments</a>

        <?php else: ?>
            <p>You are a <strong>Student</strong>!</p>
            <a href="student/assignments.php" class="dashboard-btn">📥 View Assignments</a>
            <a href="student/my_submissions.php" class="dashboard-btn">📄 My Submissions</a>
            <a href="student/attendance.php" class="dashboard-btn">📅 My Attendance</a>
        <?php endif; ?>

        <a class="logout-btn" href="logout.php"> Logout</a>
    </div>
</div>

<footer class="site-footer">
    <div class="footer-content">
        <p>&copy; 2025 Student Management System. All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy Policy</a> <span>|</span>
            <a href="#">Terms of Service</a> <span>|</span>
            <a href="#">Contact</a>
        </div>
    </div>
</footer>
</body>
</html>
