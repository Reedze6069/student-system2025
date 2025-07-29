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
$assignment_id = $_GET['id'] ?? null;

if (!$assignment_id) {
    echo "❌ No assignment selected!";
    exit();
}

// ✅ Fetch assignment details (only if enrolled)
$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.description, a.due_date, s.name AS subject_name
    FROM assignments a
    JOIN classes c ON a.class_id = c.id
    JOIN subjects s ON c.subject_id = s.id
    JOIN enrollments e ON e.class_id = c.id
    WHERE a.id = ? AND e.user_id = ?
");
$stmt->execute([$assignment_id, $student_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    echo "❌ You are not allowed to submit this assignment.";
    exit();
}

// ✅ Check if already submitted
$alreadyStmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE assignment_id = ? AND user_id = ?");
$alreadyStmt->execute([$assignment_id, $student_id]);
$already_submitted = $alreadyStmt->fetchColumn() > 0;

// ✅ Handle submission form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_submitted) {
    $comment = $_POST['comment'] ?? null;
    $filePathDB = null;
    $originalFileName = null;

    if (!empty($_FILES['submission_file']['name'])) {
        $uploadDir = __DIR__ . "/../../uploads/submissions/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalFileName = basename($_FILES['submission_file']['name']);
        $newFileName = time() . "_" . $originalFileName;
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $targetPath)) {
            $filePathDB = "uploads/submissions/" . $newFileName;
        }
    }

    $insertStmt = $pdo->prepare("
        INSERT INTO submissions (assignment_id, user_id, file_name, fille_path, student_comment, submitted_at, grade, feedback)
        VALUES (?, ?, ?, ?, ?, NOW(), NULL, NULL)
    ");
    $insertStmt->execute([
        $assignment_id,
        $student_id,
        $originalFileName,
        $filePathDB,
        $comment
    ]);

    header("Location: my_submissions.php?success=1");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>📤 Submit Assignment</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">
<div class="dashboard-wrapper">
    <div class="dashboard-card student-view">
        <h1>📤 Submit Assignment</h1>

        <h3><?= htmlspecialchars($assignment['subject_name']) ?> - <?= htmlspecialchars($assignment['title']) ?></h3>
        <p><?= htmlspecialchars($assignment['description']) ?></p>
        <p><strong>Due Date:</strong> <?= htmlspecialchars($assignment['due_date']) ?></p>
        <hr>

        <?php if ($already_submitted): ?>
            <p style="color: green; font-weight: bold;">✅ You have already submitted this assignment!</p>
            <a href="assignments.php" class="back-link">⬅ Back to My Assignments</a>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data">
                <label for="submission_file">Upload Your File:</label><br>
                <input type="file" name="submission_file" id="submission_file" required><br><br>

                <label for="comment">Comment (optional):</label><br>
                <textarea name="comment" id="comment" rows="4" style="width: 100%;"></textarea><br><br>

                <button type="submit" class="btn-submit">✅ Submit Assignment</button>
            </form>

            <br>
            <a href="assignments.php" class="back-link">⬅ Back to My Assignments</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
