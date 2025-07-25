<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
global $pdo;

// ✅ Only Teachers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// ✅ Fetch all classes for this teacher
$stmt = $pdo->prepare("
    SELECT c.id, s.name AS subject_name, c.room, c.start_time, c.end_time
    FROM classes c
    JOIN subjects s ON c.subject_id = s.id
    WHERE c.teacher_id = ?
    ORDER BY c.start_time ASC
");
$stmt->execute([$teacher_id]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">

</head>
<body>
<h1>Welcome, Teacher!</h1>
<h2>Your Assigned Classes</h2>

<?php if (empty($classes)): ?>
    <p>❌ You currently have no assigned classes.</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Subject</th>
            <th>Room</th>
            <th>Schedule</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($classes as $class): ?>
            <tr>
                <td><?= $class['id'] ?></td>
                <td><?= htmlspecialchars($class['subject_name']) ?></td>
                <td><?= htmlspecialchars($class['room']) ?></td>
                <td><?= $class['start_time'] ?> → <?= $class['end_time'] ?></td>
                <td>
                    <a href="assignments.php?class_id=<?= $class['id'] ?>">📄 Manage Assignments</a> |
                    <a href="attendance.php?class_id=<?= $class['id'] ?>">📋 Manage Attendance</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<br>
<a href="../logout.php">🚪 Logout</a>
</body>
</html>
