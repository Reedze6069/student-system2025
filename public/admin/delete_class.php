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
        // 🔍 Check if class has linked grades
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM grades g
            JOIN enrollments e ON g.enrollment_id = e.id
            WHERE e.class_id = ?
        ");
        $stmt->execute([$class_id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Cannot delete this class — students have been graded.";
            header("Location: classes.php");
            exit();
        }

        // 🔍 Check if class has linked submissions
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM submissions s
            JOIN assignments a ON s.assignment_id = a.id
            WHERE a.class_id = ?
        ");
        $stmt->execute([$class_id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Cannot delete this class — student submissions exist.";
            header("Location: classes.php");
            exit();
        }

        // ✅ Safe to delete
        $pdo->beginTransaction();

        // 1. Delete attendance linked to enrollments
        $stmt = $pdo->prepare("
            DELETE a FROM attendance a
            JOIN enrollments e ON a.enrollment_id = e.id
            WHERE e.class_id = ?
        ");
        $stmt->execute([$class_id]);

        // 2. Delete enrollments
        $stmt = $pdo->prepare("DELETE FROM enrollments WHERE class_id = ?");
        $stmt->execute([$class_id]);

        // 3. Delete assignments
        $stmt = $pdo->prepare("DELETE FROM assignments WHERE class_id = ?");
        $stmt->execute([$class_id]);

        // 4. Delete class
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);

        $pdo->commit();
        $_SESSION['success'] = "Class deleted successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "An error occurred while deleting the class.";
    }
}

header("Location: classes.php");
exit();
?>
