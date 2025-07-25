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
$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    echo "❌ No class selected!";
    exit();
}

// ✅ Verify teacher owns this class
$classStmt = $pdo->prepare("
    SELECT c.id, s.name AS subject_name, c.room, c.start_time, c.end_time
    FROM classes c
    JOIN subjects s ON c.subject_id = s.id
    WHERE c.id = ? AND c.teacher_id = ?
");
$classStmt->execute([$class_id, $teacher_id]);
$class = $classStmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    echo "❌ You are not allowed to manage this class!";
    exit();
}

// ✅ Fetch enrolled students + their enrollment_id
$studentsStmt = $pdo->prepare("
    SELECT e.id AS enrollment_id, u.username, u.email
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    WHERE e.class_id = ?
    ORDER BY u.username ASC
");
$studentsStmt->execute([$class_id]);
$students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = date('Y-m-d');
    $attendanceData = $_POST['attendance'] ?? [];

    foreach ($attendanceData as $enrollment_id => $status) {

        // ✅ Check if a record already exists for this student & date
        $checkStmt = $pdo->prepare("
            SELECT id FROM attendance 
            WHERE enrollment_id = ? AND date = ?
        ");
        $checkStmt->execute([$enrollment_id, $date]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // ✅ Update existing record instead of duplicating
            $updateStmt = $pdo->prepare("
                UPDATE attendance 
                SET status = ?, notes = NULL 
                WHERE id = ?
            ");
            $updateStmt->execute([$status, $existing['id']]);
        } else {
            // ✅ Insert new record if none exists
            $insertStmt = $pdo->prepare("
                INSERT INTO attendance (enrollment_id, status, date, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $insertStmt->execute([$enrollment_id, $status, $date]);
        }
    }

    echo "<p style='color: green; font-weight: bold;'>✅ Attendance saved/updated successfully for today ($date)!</p>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Attendance - <?= htmlspecialchars($class['subject_name']) ?></title>
    <link rel="stylesheet" href="../assets/style.css">

</head>
<body>
<h1>📋 Manage Attendance</h1>
<h3>Class: <?= htmlspecialchars($class['subject_name']) ?> | Room: <?= htmlspecialchars($class['room']) ?></h3>
<p><strong>Schedule:</strong> <?= $class['start_time'] ?> → <?= $class['end_time'] ?></p>
<hr>

<?php if (empty($students)): ?>
    <p>❌ No students enrolled in this class.</p>
<?php else: ?>
    <form method="POST">
        <table border="1" cellpadding="8">
            <tr>
                <th>Student</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['username']) ?></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td>
                        <select name="attendance[<?= $student['enrollment_id'] ?>]">
                            <option value="present">✅ Present</option>
                            <option value="absent">❌ Absent</option>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <br>
        <button type="submit">💾 Save Attendance</button>
    </form>
<?php endif; ?>

<br>
<a href="dashboard.php">⬅ Back to Teacher Dashboard</a>
</body>
</html>
