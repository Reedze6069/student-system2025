<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
global $pdo;

// ✅ Only Teachers can delete
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$assignment_id = $_GET['id'] ?? null;
$class_id = $_GET['class_id'] ?? null;

if (!$assignment_id || !$class_id) {
    echo "❌ Missing parameters!";
    exit();
}

// ✅ Validate ownership
$stmt = $pdo->prepare("
    SELECT a.id FROM assignments a
    JOIN classes c ON a.class_id = c.id
    WHERE a.id = ? AND c.teacher_id = ?
");
$stmt->execute([$assignment_id, $teacher_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    echo "❌ Assignment not found or unauthorized!";
    exit();
}

// ✅ Delete associated file(s)
$pdo->prepare("DELETE FROM assignment_files WHERE assignment_id=?")->execute([$assignment_id]);

// ✅ Delete assignment
$pdo->prepare("DELETE FROM assignments WHERE id=?")->execute([$assignment_id]);

header("Location: assignments.php?class_id=" . $class_id);
exit();
