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

// ✅ Get class_id from URL
$class_id = $_GET['class_id'] ?? null;
if (!$class_id) {
    echo "❌ No class selected!";
    exit();
}

// ✅ Check if this class belongs to this teacher
$class_stmt = $pdo->prepare("
    SELECT c.id, s.name AS subject_name
    FROM classes c
    JOIN subjects s ON c.subject_id = s.id
    WHERE c.id = ? AND c.teacher_id = ?
");
$class_stmt->execute([$class_id, $teacher_id]);
$class = $class_stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    echo "❌ You are not assigned to this class!";
    exit();
}

// ✅ Handle new assignment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    // 1️⃣ Save assignment first
    $stmt = $pdo->prepare("
        INSERT INTO assignments (class_id, title, description, due_date) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$class_id, $title, $description, $due_date]);

    $assignment_id = $pdo->lastInsertId(); // get new assignment ID

    // 2️⃣ If a file was uploaded, handle it
    if (!empty($_FILES['assignment_file']['name'])) {
        $uploadDir = __DIR__ . "/../../uploads/assignments/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // create folder if not exists
        }

        $fileName = time() . "_" . basename($_FILES['assignment_file']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $targetPath)) {
            // Save file info in assignment_files table
            $filePathDB = "uploads/assignments/" . $fileName; // relative path for later download
            $fileStmt = $pdo->prepare("
                INSERT INTO assignment_files (assignment_id, file_name, file_path)
                VALUES (?, ?, ?)
            ");
            $fileStmt->execute([$assignment_id, $_FILES['assignment_file']['name'], $filePathDB]);
        }
    }

    // Refresh page
    header("Location: assignments.php?class_id=" . $class_id);
    exit();
}

// ✅ Fetch all assignments for this class
$assignments_stmt = $pdo->prepare("
    SELECT a.id, a.title, a.description, a.due_date, a.created_at,
           f.file_name, f.file_path
    FROM assignments a
    LEFT JOIN assignment_files f ON a.id = f.assignment_id
    WHERE a.class_id = ?
    ORDER BY a.created_at DESC
");
$assignments_stmt->execute([$class_id]);
$assignments = $assignments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Assignments - <?= htmlspecialchars($class['subject_name']) ?></title>
    <link rel="stylesheet" href="../assets/style.css">

</head>
<body>
<h1>Assignments for <?= htmlspecialchars($class['subject_name']) ?></h1>

<?php if (empty($assignments)): ?>
    <p>❌ No assignments created yet for this class.</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Due Date</th>
            <th>Attachment</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($assignments as $a): ?>
            <tr>
                <td><?= $a['id'] ?></td>
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
                <td><?= $a['created_at'] ?></td>
                <td>
                    <a href="edit_assignment.php?id=<?= $a['id'] ?>">✏️ Edit</a> |
                    <a href="delete_assignment.php?id=<?= $a['id'] ?>&class_id=<?= $class_id ?>"
                       onclick="return confirm('Are you sure you want to delete this assignment?');">🗑 Delete</a> |
                    <a href="view_submissions.php?id=<?= $a['id'] ?>">📥 View Submissions</a>
                </td>


            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<hr>

<h2>Add New Assignment</h2>
<form method="POST" enctype="multipart/form-data">
    <label>Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Description:</label><br>
    <textarea name="description" rows="4" cols="40"></textarea><br><br>

    <label>Due Date:</label><br>
    <input type="datetime-local" name="due_date" required><br><br>

    <label>Attach File (optional):</label><br>
    <input type="file" name="assignment_file"><br><br>

    <button type="submit">Save Assignment</button>
</form>

<br>
<a href="dashboard.php">⬅ Back to Teacher Dashboard</a>
</body>
</html>
