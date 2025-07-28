<?php
global $pdo;
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$class_id = $_GET['id'] ?? null;

if ($class_id) {
    try {
        $pdo->beginTransaction(); // ✅ Start transaction

        // 1️⃣ Delete attendance linked to enrollments of this class
        $stmt = $pdo->prepare("
            DELETE a FROM attendance a
            JOIN enrollments e ON a.enrollment_id = e.id
            WHERE e.class_id = ?
        ");
        $stmt->execute([$class_id]);

        // 2️⃣ Delete enrollments for this class
        $stmt = $pdo->prepare("DELETE FROM enrollments WHERE class_id = ?");
        $stmt->execute([$class_id]);

        // 3️⃣ Delete assignments for this class
        $stmt = $pdo->prepare("DELETE FROM assignments WHERE class_id = ?");
        $stmt->execute([$class_id]);

        // 4️⃣ Finally delete the class
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);

        $pdo->commit(); // ✅ Commit all deletions
    } catch (Exception $e) {
        $pdo->rollBack(); // ❌ Rollback if something goes wrong
        die("Error deleting class: " . $e->getMessage());
    }
}

header("Location: classes.php");
exit();
?>
