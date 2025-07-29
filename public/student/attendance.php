<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
global $pdo;

// ✅ Only Students can access
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// ✅ Fetch attendance records for this student
$stmt = $pdo->prepare("
    SELECT a.date, a.status, s.name AS subject_name, c.room 
    FROM attendance a
    JOIN enrollments e ON a.enrollment_id = e.id
    JOIN classes c ON e.class_id = c.id
    JOIN subjects s ON c.subject_id = s.id
    WHERE e.user_id = ?
    ORDER BY a.date DESC
");
$stmt->execute([$student_id]);
$attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Attendance</title>
    <link rel="stylesheet" href="../assets/style.css?v=<?= time(); ?>">
</head>
<body class="dashboard-page">

<div class="dashboard-wrapper">
    <!-- ✅ Use wider attendance-view card -->
    <div class="dashboard-card attendance-view">

        <h1>My Attendance</h1>

        <?php if (empty($attendance_records)): ?>
            <p style="text-align:center; color:red; font-weight:bold;">
                 No attendance records yet.
            </p>
        <?php else: ?>
            <!-- ✅ Attendance table wrapper for better spacing -->
            <div class="attendance-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Room</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['date']) ?></td>
                            <td><?= htmlspecialchars($record['subject_name']) ?></td>
                            <td><?= htmlspecialchars($record['room']) ?></td>
                            <td>
                                <?= $record['status'] === 'present'
                                    ? "✅ Present"
                                    : "❌ Absent" ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- ✅ Cleaner back link -->
        <a class="back-link" href="../dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
