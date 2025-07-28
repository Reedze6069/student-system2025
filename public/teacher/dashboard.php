<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
global $pdo;

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

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
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-page">

<div class="dashboard-wrapper">
    <div class="dashboard-card">
        <h1>Welcome, Teacher!</h1>
        <h2>Your Assigned Classes</h2>

        <?php if (empty($classes)): ?>
            <p style="text-align:center; color:#d9534f;">❌ You currently have no assigned classes.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Room</th>
                    <th>Schedule</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
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
                </tbody>
            </table>
        <?php endif; ?>

        <a class="logout-btn" href="../logout.php"> Logout</a>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
