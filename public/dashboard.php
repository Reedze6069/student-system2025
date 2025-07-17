<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role_id'];

echo "<h1>Welcome!</h1>";

if ($role == 1) {
    // ✅ Admin section
    echo "You are an Admin!<br><br>";
    echo '<a href="admin/users.php">👤 Manage Users</a><br>';
    echo '<a href="admin/classes.php">🏫 Manage Classes</a><br>';
    echo '<a href="admin/enrollments.php">📋 Manage Enrollments</a><br>'; // ✅ New link

} elseif ($role == 2) {
    // ✅ Teacher section
    echo "You are a Teacher!<br><br>";
    echo '<a href="teacher/assignments.php">📄 Manage Assignments (Coming Soon)</a><br>';

} else {
    // ✅ Student section
    echo "You are a Student!<br><br>";
    echo '<a href="student/assignments.php">📥 View Assignments (Coming Soon)</a><br>';
}

// ✅ Logout always shown
echo '<br><a href="logout.php">🚪 Logout</a>';

