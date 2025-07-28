<?php
global $pdo;
session_start();
require_once __DIR__ . "/../../config/db.php";

// ✅ Only Admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// ✅ Fetch subjects
$subjects = $pdo->query("SELECT id, name FROM subjects")->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch teachers
$teachers_stmt = $pdo->prepare("SELECT id, username FROM users WHERE role_id = 2");
$teachers_stmt->execute();
$teachers = $teachers_stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'] ?: null;
    $room = $_POST['room'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $stmt = $pdo->prepare("
        INSERT INTO classes (subject_id, teacher_id, room, start_time, end_time) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$subject_id, $teacher_id, $room, $start_time, $end_time]);

    header("Location: classes.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Class</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">
<div class="dashboard-wrapper">
    <div class="dashboard-card wide-view">
        <h1>Add New Class</h1>

        <form method="POST">
            <label for="subject_id">Subject:</label>
            <select id="subject_id" name="subject_id" required>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="teacher_id">Assign Teacher:</label>
            <select id="teacher_id" name="teacher_id">
                <option value="">-- No Teacher --</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['username']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="room">Room:</label>
            <input id="room" type="text" name="room" required>

            <label for="start_time">Start Time:</label>
            <input id="start_time" type="datetime-local" name="start_time" required>

            <label for="end_time">End Time:</label>
            <input id="end_time" type="datetime-local" name="end_time" required>

            <button type="submit" class="btn-submit">Add Class</button>
        </form>

        <a class="back-link" href="classes.php">⬅ Back</a>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
