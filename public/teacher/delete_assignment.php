<?php
session_start();
require_once __DIR__ . "/../../config/db.php";
global $pdo;

// ✅ Only allow Teachers
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

$assignment_id = $_GET['id'] ?? null;
$class_id = $_GET['class_id'] ?? null; // ✅ Get class_id to preserve context

if (!$assignment_id) {
    die("❌ Invalid assignment ID.");
}

// ✅ Check if there are submissions for this assignment
$stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE assignment_id = ?");
$stmt->execute([$assignment_id]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    // ✅ Stop deletion & show a friendly message
    echo "<h2>⚠️ Cannot delete this assignment</h2>";
    echo "<p>This assignment already has <strong>$count submissions</strong>.</p>";
    echo "<p>Please review or remove submissions first before deleting.</p>";

    // ✅ Keep class_id in the link if it exists
    $backLink = $class_id ? "assignments.php?class_id={$class_id}" : "assignments.php";

    echo '<br><a href="' . htmlspecialchars($backLink) . '">⬅ Back to Assignments</a>';
    exit();
}

// ✅ If no submissions, allow deletion
$deleteStmt = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
$deleteStmt->execute([$assignment_id]);

// ✅ Redirect back after successful delete
$redirectLink = $class_id ? "assignments.php?class_id={$class_id}&msg=deleted" : "assignments.php?msg=deleted";
header("Location: $redirectLink");
exit();
?>
