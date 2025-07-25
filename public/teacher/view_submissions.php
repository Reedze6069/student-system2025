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
$assignment_id = $_GET['id'] ?? null;

if (!$assignment_id) {
    echo "❌ No assignment selected!";
    exit();
}

// ✅ Check if assignment belongs to this teacher
$assignStmt = $pdo->prepare("
    SELECT a.id, a.title, a.class_id, s.name AS subject_name
    FROM assignments a
    JOIN classes c ON a.class_id = c.id
    JOIN subjects s ON c.subject_id = s.id
    WHERE a.id = ? AND c.teacher_id = ?
");
$assignStmt->execute([$assignment_id, $teacher_id]);
$assignment = $assignStmt->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    echo "❌ Assignment not found or you’re not allowed!";
    exit();
}

// ✅ Handle grading form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];

    // ✅ 1) Update the submissions table first
    $updateStmt = $pdo->prepare("
        UPDATE submissions 
        SET grade = ?, feedback = ? 
        WHERE id = ?
    ");
    $updateStmt->execute([$grade, $feedback, $submission_id]);

    // ✅ 2) Get assignment + student details for this submission
    $detailsStmt = $pdo->prepare("
        SELECT s.assignment_id, s.user_id, a.class_id
        FROM submissions s 
        JOIN assignments a ON s.assignment_id = a.id
        WHERE s.id = ?
    ");
    $detailsStmt->execute([$submission_id]);
    $submissionDetails = $detailsStmt->fetch(PDO::FETCH_ASSOC);

    if ($submissionDetails) {
        $assignment_id = $submissionDetails['assignment_id'];
        $student_id = $submissionDetails['user_id'];
        $class_id = $submissionDetails['class_id'];

        // ✅ 3) Find the enrollment_id for this student in that class
        $enrollStmt = $pdo->prepare("
            SELECT id FROM enrollments 
            WHERE user_id = ? AND class_id = ?
        ");
        $enrollStmt->execute([$student_id, $class_id]);
        $enrollment = $enrollStmt->fetch(PDO::FETCH_ASSOC);

        if ($enrollment) {
            $enrollment_id = $enrollment['id'];

            // ✅ 4) Insert/Update grade for this enrollment
            $gradeStmt = $pdo->prepare("
                INSERT INTO grades (enrollment_id, grade, feedback, created_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    grade = VALUES(grade),
                    feedback = VALUES(feedback),
                    created_at = NOW()
            ");
            $gradeStmt->execute([
                $enrollment_id,
                $grade,
                $feedback
            ]);
        }
    }

    echo "<p style='color:green; font-weight:bold;'> Grade updated!</p>";
}


// ✅ Fetch all student submissions for this assignment
$submissionsStmt = $pdo->prepare("
    SELECT sub.id, sub.file_name, sub.fille_path, sub.student_comment, sub.submitted_at,
           sub.grade, sub.feedback, u.username AS student_name, u.email
    FROM submissions sub
    JOIN users u ON sub.user_id = u.id
    WHERE sub.assignment_id = ?
    ORDER BY sub.submitted_at ASC
");
$submissionsStmt->execute([$assignment_id]);
$submissions = $submissionsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Submissions - <?= htmlspecialchars($assignment['title']) ?></title>
    <link rel="stylesheet" href="../assets/style.css">

</head>
<body>
<h1>📥 Submissions for <?= htmlspecialchars($assignment['subject_name']) ?> - <?= htmlspecialchars($assignment['title']) ?></h1>

<?php if (empty($submissions)): ?>
    <p>❌ No submissions yet for this assignment.</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <tr>
            <th>Student</th>
            <th>Email</th>
            <th>File</th>
            <th>Comment</th>
            <th>Submitted At</th>
            <th>Grade</th>
            <th>Feedback</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($submissions as $sub): ?>
            <tr>
                <td><?= htmlspecialchars($sub['student_name']) ?></td>
                <td><?= htmlspecialchars($sub['email']) ?></td>
                <td>
                    <?php if ($sub['fille_path']): ?>
                        <a href="../../<?= htmlspecialchars($sub['fille_path']) ?>" target="_blank">📄 <?= htmlspecialchars($sub['file_name']) ?></a>
                    <?php else: ?>
                        ❌ No file
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($sub['student_comment']) ?></td>
                <td><?= $sub['submitted_at'] ?></td>
                <td><?= $sub['grade'] !== null ? htmlspecialchars($sub['grade']) : '-' ?></td>
                <td><?= htmlspecialchars($sub['feedback']) ?></td>
                <td>
                    <!-- Grading Form -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                        <input type="text" name="grade" placeholder="Grade" value="<?= $sub['grade'] ?>"><br>
                        <textarea name="feedback" placeholder="Feedback"><?= htmlspecialchars($sub['feedback']) ?></textarea><br>
                        <button type="submit">✅ Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<br>
<a href="assignments.php?class_id=<?= $assignment['class_id'] ?>">⬅ Back to Assignments</a>
</body>
</html>
