<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
global $pdo;

// ✅ Only Admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) {
    header("Location: enrollments.php");
    exit();
}

// ✅ Fetch class info
$class_stmt = $pdo->prepare("
    SELECT c.id, s.name AS subject_name, u.username AS teacher_name
    FROM classes c
    JOIN subjects s ON c.subject_id = s.id
    LEFT JOIN users u ON c.teacher_id = u.id
    WHERE c.id = ?
");
$class_stmt->execute([$class_id]);
$class = $class_stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    echo "Class not found!";
    exit();
}

// ✅ Fetch all students (role_id = 3)
$students_stmt = $pdo->query("SELECT id, username, email FROM users WHERE role_id = 3 ORDER BY username ASC");
$students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch already enrolled students
$enrolled_stmt = $pdo->prepare("SELECT user_id FROM enrollments WHERE class_id = ?");
$enrolled_stmt->execute([$class_id]);

$enrolled_students = [];
while ($row = $enrolled_stmt->fetch(PDO::FETCH_ASSOC)) {
    $enrolled_students[] = (int)$row['user_id'];
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_students = $_POST['students'] ?? [];

    // 1. Delete grades linked to this class
    $pdo->prepare("DELETE FROM grades WHERE enrollment_id IN (SELECT id FROM enrollments WHERE class_id = ?)")->execute([$class_id]);

    // 2. Delete attendance for this class
    $pdo->prepare("DELETE FROM attendance WHERE enrollment_id IN (SELECT id FROM enrollments WHERE class_id = ?)")->execute([$class_id]);

    // 3. Delete old enrollments
    $pdo->prepare("DELETE FROM enrollments WHERE class_id = ?")->execute([$class_id]);

    // 4. Insert updated enrollments
    $insert_stmt = $pdo->prepare("INSERT INTO enrollments (class_id, user_id) VALUES (?, ?)");
    foreach ($selected_students as $student_id) {
        $insert_stmt->execute([$class_id, $student_id]);
    }

    header("Location: enrollments.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Enrollment - <?= htmlspecialchars($class['subject_name']) ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">

<div class="dashboard-wrapper">
    <div class="dashboard-card teacher-view">
        <h1>Manage Enrollment for <?= htmlspecialchars($class['subject_name']) ?></h1>
        <p>Teacher: <?= htmlspecialchars($class['teacher_name'] ?? 'No Teacher') ?></p>

        <form method="POST">
            <?php if (empty($students)): ?>
                <p>No students found. Please create some student accounts first.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Enroll</th>
                        <th>Student Name</th>
                        <th>Email</th>
                    </tr>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="students[]" value="<?= $student['id'] ?>"
                                    <?= in_array((int)$student['id'], $enrolled_students, true) ? 'checked' : '' ?>>
                            </td>
                            <td><?= htmlspecialchars($student['username']) ?></td>
                            <td><?= htmlspecialchars($student['email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <br>
                <button type="submit" class="btn-submit">Save Enrollment</button>
            <?php endif; ?>
        </form>

        <br>
        <a class="back-link" href="enrollments.php">⬅ Back to Classes</a>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
