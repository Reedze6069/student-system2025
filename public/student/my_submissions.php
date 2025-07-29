<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
global $pdo;

//  Only students can access
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

//  Fetch all submissions for this student
$stmt = $pdo->prepare("
    SELECT sub.id, sub.submitted_at, sub.file_name, sub.fille_path, sub.student_comment,
           sub.grade, sub.feedback,
           a.title AS assignment_title, a.due_date,
           s.name AS subject_name
    FROM submissions sub
    JOIN assignments a ON sub.assignment_id = a.id
    JOIN classes c ON a.class_id = c.id
    JOIN subjects s ON c.subject_id = s.id
    WHERE sub.user_id = ?
    ORDER BY sub.submitted_at DESC
");
$stmt->execute([$student_id]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>📄 My Submissions</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">
<div class="dashboard-wrapper">
    <div class="dashboard-card student-view">
        <h1>📄 My Submissions</h1>

        <?php if (empty($submissions)): ?>
            <p>❌ You have not submitted any assignments yet.</p>
        <?php else: ?>
            <?php if (isset($_GET['success'])): ?>
                <p style="color: green; font-weight: bold;"> Assignment submitted successfully!</p>
            <?php endif; ?>

            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>Subject</th>
                        <th>Assignment</th>
                        <th>Due Date</th>
                        <th>Submitted File</th>
                        <th>Your Comment</th>
                        <th>Submitted At</th>
                        <th>Grade</th>
                        <th>Feedback</th>
                    </tr>
                    <?php foreach ($submissions as $sub): ?>
                        <tr>
                            <td><?= htmlspecialchars($sub['subject_name']) ?></td>
                            <td><?= htmlspecialchars($sub['assignment_title']) ?></td>
                            <td><?= $sub['due_date'] ?></td>
                            <td>
                                <?php if ($sub['fille_path']): ?>
                                    <a href="../../<?= htmlspecialchars($sub['fille_path']) ?>" target="_blank">📄 <?= htmlspecialchars($sub['file_name']) ?></a>
                                <?php else: ?>
                                    ❌ No file
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($sub['student_comment']) ?: '-' ?></td>
                            <td><?= $sub['submitted_at'] ?></td>
                            <td><?= $sub['grade'] !== null ? htmlspecialchars($sub['grade']) : '⏳ Pending' ?></td>
                            <td><?= $sub['feedback'] ?: 'No feedback yet' ?></td>
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
