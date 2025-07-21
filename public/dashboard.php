<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role_id'];

echo "<h1>Welcome!</h1>";

if ($role == 1) {
    // ✅ Admin section stays here
    echo "You are an Admin!<br><br>";
    echo '<a href="admin/users.php">👤 Manage Users</a><br>';
    echo '<a href="admin/classes.php">🏫 Manage Classes</a><br>';
    echo '<a href="admin/enrollments.php">📋 Manage Enrollments</a><br>';

} elseif ($role == 2) {
    // ✅ Teacher gets redirected to their dashboard
    header("Location: teacher/dashboard.php");
    exit();

} else {
    // ✅ Student section (we’ll later redirect students too)
    echo "You are a Student!<br><br>";
    echo '<a href="student/assignments.php">📥 View Assignments (Coming Soon)</a><br>';
}

// ✅ Logout always shown for Admin/Student
echo '<br><a href="logout.php">🚪 Logout</a>';
?>
