<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
global $pdo;

// ✅ Only Admins can delete
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// ✅ Get user ID
$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header("Location: users.php");
    exit();
}

// ✅ Check for dependencies (Enrollments & Attendance)
$checkEnrollments = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ?");
$checkEnrollments->execute([$user_id]);
$enrollmentCount = $checkEnrollments->fetchColumn();

$checkAttendance = $pdo->prepare("
    SELECT COUNT(*) FROM attendance a
    JOIN enrollments e ON a.enrollment_id = e.id
    WHERE e.user_id = ?
");
$checkAttendance->execute([$user_id]);
$attendanceCount = $checkAttendance->fetchColumn();

// ✅ If dependencies exist → block deletion
if ($enrollmentCount > 0 || $attendanceCount > 0) {
    echo "<h2>⚠️ Cannot delete this user</h2>";
    echo "<p>This user is linked to:</p>";
    echo "<ul>";
    if ($enrollmentCount > 0) echo "<li>$enrollmentCount enrollment(s)</li>";
    if ($attendanceCount > 0) echo "<li>$attendanceCount attendance record(s)</li>";
    echo "</ul>";
    echo "<p>Please remove related data before deleting this user.</p>";
    echo "<br><a href='users.php'>⬅ Back to Users</a>";
    exit();
}

// ✅ Safe to delete
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user_id]);

header("Location: users.php");
exit();
?>
