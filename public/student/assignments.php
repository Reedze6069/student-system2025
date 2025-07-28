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

// ✅ Get enrolled classes
$class_stmt = $pdo->prepare("
    SELECT c.id AS class_id, s.name AS subject_name, u.username AS teacher_name
    FROM enrollments e
    JOIN classes c ON e.class_id = c.id
    JOIN subjects s ON c.subject_id = s.id
    LEFT JOIN users u ON c.teacher_id = u.id
    WHERE e.user_id = ?
");
$class_stmt->execute([$student_id]);
$enrolled_classes = $class_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($enrolled_classes)) {
    echo "<h1>Assignments</h1>";
    echo "<p>❌ You are not enrolled in any classes.</p>";
    echo '<br><a href="../dashboard.php">⬅ Back to Dashboard</a>';
    exit();
}

// ✅ Get assignments
$class_ids = array_column($enrolled_classes, 'class_id');
$placeholders = implode(',', array_fill(0, count($class_ids), '?'));

$assignments_stmt = $pdo->prepare("
    SELECT a.id, a.title, a.description, a.due_date, a.created_at,
           c.id AS class_id, s.name AS subject_name, f.file_name, f.file_path,
           (SELECT COUNT(*) FROM submissions sub WHERE sub.assignment_id = a.id AND sub.user_id = ?) AS already_submitted
    FROM assignments a
    JOIN classes c ON a.class_id = c.id
    JOIN subjects s ON c.subject_id = s.id
    LEFT JOIN assignment_files f ON a.id = f.assignment_id
    WHERE a.class_id IN ($placeholders)
    ORDER BY a.due_date ASC
");

$params = array_merge([$student_id], $class_ids);
$assignments_stmt->execute($params);
$assignments = $assignments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Assignments</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">
<div class="dashboard-wrapper">
    <div class="dashboard-card student-view">
        <h1>📥 My Assignments</h1>

        <?php if (empty($assignments)): ?>
            <p>❌ You have no assignments yet!</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>Subject</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Attachment</th>
                        <th>Status</th>
                        <th>Submit</th>
                    </tr>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['subject_name']) ?></td>
                            <td><?= htmlspecialchars($a['title']) ?></td>
                            <td><?= htmlspecialchars($a['description']) ?></td>
                            <td><?= $a['due_date'] ?></td>
                            <td>
                                <?php if ($a['file_path']): ?>
                                    <a href="../../<?= htmlspecialchars($a['file_path']) ?>" target="_blank">📄 <?= htmlspecialchars($a['file_name']) ?></a>
                                <?php else: ?>
                                    No file
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $a['already_submitted'] > 0 ? '✅ Submitted' : '⏳ Pending' ?>
                            </td>
                            <td>
                                <?php if ($a['already_submitted'] == 0): ?>
                                    <a href="submit_assignment.php?id=<?= $a['id'] ?>">✏️ Submit</a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>

        <a class="back-link" href="../dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
