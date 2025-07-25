<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
global $pdo;

//  Only Admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

//  Fetch all classes with subject & teacher info
$stmt = $pdo->query("
    SELECT c.id, s.name AS subject_name, u.username AS teacher_name, c.room
    FROM classes c
    JOIN subjects s ON c.subject_id = s.id
    LEFT JOIN users u ON c.teacher_id = u.id
    ORDER BY c.id ASC
");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Enrollments</title>
    <link rel="stylesheet" href="../assets/style.css">

</head>
<body>
<h1>Manage Student Enrollments</h1>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Subject</th>
        <th>Teacher</th>
        <th>Room</th>
        <th>Action</th>
    </tr>
    <?php foreach ($classes as $class): ?>
        <tr>
            <td><?= $class['id'] ?></td>
            <td><?= htmlspecialchars($class['subject_name']) ?></td>
            <td><?= htmlspecialchars($class['teacher_name'] ?? 'No Teacher') ?></td>
            <td><?= htmlspecialchars($class['room']) ?></td>
            <td>
                <a href="manage_enrollment.php?class_id=<?= $class['id'] ?>">Manage Enrollment</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<br>
<a href="../dashboard.php">⬅ Back to Dashboard</a>
</body>
</html>
