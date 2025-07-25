<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
global $pdo;

// ✅ Only students can access
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// ✅ Fetch attendance records for this student
$stmt = $pdo->prepare("
    SELECT 
        att.id, 
        att.status, 
        att.date AS attendance_date,
        c.id AS class_id, 
        s.name AS subject_name,
        t.username AS teacher_name
    FROM attendance att
    JOIN enrollments e ON att.enrollment_id = e.id
    JOIN classes c ON e.class_id = c.id
    JOIN subjects s ON c.subject_id = s.id
    LEFT JOIN users t ON c.teacher_id = t.id
    WHERE e.user_id = ?
    ORDER BY att.date DESC
");
$stmt->execute([$student_id]);
$attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Calculate attendance summary
$total_classes = count($attendance_records);
$total_present = count(array_filter($attendance_records, function ($row) {
    return strtolower(trim($row['status'])) === 'present';
}));
$total_absent = count(array_filter($attendance_records, function ($row) {
    return strtolower(trim($row['status'])) === 'absent';
}));


$attendance_rate = $total_classes > 0 ? round(($total_present / $total_classes) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>📊 My Attendance</title>
    <link rel="stylesheet" href="../assets/style.css">

</head>
<body>
<h1>📊 My Attendance</h1>

<?php if ($total_classes === 0): ?>
    <p>❌ No attendance records yet.</p>
<?php else: ?>
    <!-- ✅ Attendance Summary -->
    <h3>Summary:</h3>
    <p>
        ✅ Present: <strong><?= $total_present ?></strong><br>
        ❌ Absent: <strong><?= $total_absent ?></strong><br>
        📈 Attendance Rate: <strong><?= $attendance_rate ?>%</strong>
    </p>

    <hr>

    <!-- ✅ Detailed Records -->
    <table border="1" cellpadding="8">
        <tr>
            <th>Date</th>
            <th>Subject</th>
            <th>Teacher</th>
            <th>Status</th>
        </tr>
        <?php foreach ($attendance_records as $record): ?>
            <tr>
                <td><?= $record['attendance_date'] ?></td>
                <td><?= htmlspecialchars($record['subject_name']) ?></td>
                <td><?= htmlspecialchars($record['teacher_name'] ?? 'N/A') ?></td>
                <td>
                    <?php
                    $status = strtolower(trim($record['status']));
                    echo $status === 'present'
                        ? '✅ Present'
                        : '❌ Absent';
                    ?>
                </td>

            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<br>
<a href="../dashboard.php">⬅ Back to Dashboard</a>
</body>
</html>
