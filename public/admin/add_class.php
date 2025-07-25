<?php
global $pdo, $pdo;
session_start();
require_once __DIR__ . "/../../config/db.php";
// ✅ db.php loads $pdo

// ✅ Only Admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// ✅ Fetch subjects for dropdown
$subjects_stmt = $pdo->query("SELECT id, name FROM subjects");
$subjects = $subjects_stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch only Teachers
$teachers_stmt = $pdo->prepare("SELECT id, username FROM users WHERE role_id = 2");
$teachers_stmt->execute();
$teachers = $teachers_stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'] ?: null; // optional
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
<body>
<h1>Add New Class</h1>
<form method="POST">
    <label>Subject:</label><br>
    <select name="subject_id" required>
        <?php foreach ($subjects as $subject): ?>
            <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Assign Teacher:</label><br>
    <select name="teacher_id">
        <option value="">-- No Teacher --</option>
        <?php foreach ($teachers as $teacher): ?>
            <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['username']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Room:</label><br>
    <input type="text" name="room" required><br><br>

    <label>Start Time:</label><br>
    <input type="datetime-local" name="start_time" required><br><br>

    <label>End Time:</label><br>
    <input type="datetime-local" name="end_time" required><br><br>

    <button type="submit">Add Class</button>
</form>
<br>
<a href="classes.php">⬅ Back</a>
</body>
</html>
