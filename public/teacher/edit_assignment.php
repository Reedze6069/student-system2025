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

// ✅ Fetch assignment + class info (to validate ownership)
$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.description, a.due_date, a.class_id, f.file_name, f.file_path
    FROM assignments a
    LEFT JOIN assignment_files f ON a.id = f.assignment_id
    JOIN classes c ON a.class_id = c.id
    WHERE a.id = ? AND c.teacher_id = ?
");
$stmt->execute([$assignment_id, $teacher_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    echo "❌ Assignment not found or you don’t have permission!";
    exit();
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    // Update assignment
    $updateStmt = $pdo->prepare("
        UPDATE assignments SET title = ?, description = ?, due_date = ? WHERE id = ?
    ");
    $updateStmt->execute([$title, $description, $due_date, $assignment_id]);

    // ✅ Optional file upload
    if (!empty($_FILES['assignment_file']['name'])) {
        $uploadDir = __DIR__ . "/../../uploads/assignments/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES['assignment_file']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $targetPath)) {
            $filePathDB = "uploads/assignments/" . $fileName;

            // Delete old record if exists
            $pdo->prepare("DELETE FROM assignment_files WHERE assignment_id = ?")->execute([$assignment_id]);

            // Insert new file
            $fileStmt = $pdo->prepare("
                INSERT INTO assignment_files (assignment_id, file_name, file_path)
                VALUES (?, ?, ?)
            ");
            $fileStmt->execute([$assignment_id, $_FILES['assignment_file']['name'], $filePathDB]);
        }
    }

    header("Location: assignments.php?class_id=" . $assignment['class_id']);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Assignment</title>
    <link rel="stylesheet" href="../assets/style.css?v=<?= time(); ?>">
</head>
<body class="dashboard-page">
<div class="dashboard-wrapper">
    <div class="dashboard-card teacher-view">
        <h1>Edit Assignment</h1>

        <form method="POST" enctype="multipart/form-data">
            <label>Title:</label><br>
            <input type="text" name="title" value="<?= htmlspecialchars($assignment['title']) ?>" required><br><br>

            <label>Description:</label><br>
            <textarea name="description" rows="4" cols="40"><?= htmlspecialchars($assignment['description']) ?></textarea><br><br>

            <label>Due Date:</label><br>
            <input type="datetime-local" name="due_date" value="<?= date('Y-m-d\TH:i', strtotime($assignment['due_date'])) ?>" required><br><br>

            <?php if ($assignment['file_path']): ?>
                <p>Current File:
                    <a href="../../<?= htmlspecialchars($assignment['file_path']) ?>" target="_blank">
                        📄 <?= htmlspecialchars($assignment['file_name']) ?>
                    </a>
                </p>
            <?php else: ?>
                <p>No file currently attached.</p>
            <?php endif; ?>

            <label>Upload New File (optional):</label><br>
            <input type="file" name="assignment_file"><br><br>

            <button type="submit" class="btn-submit">Update Assignment</button>
        </form>

        <br>
        <a class="back-link" href="assignments.php?class_id=<?= $assignment['class_id'] ?>">⬅ Back to Assignments</a>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
