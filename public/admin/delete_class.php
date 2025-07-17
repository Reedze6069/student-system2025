<?php
global $pdo, $pdo;
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$class_id = $_GET['id'] ?? null;
if ($class_id) {
    $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->execute([$class_id]);
}

header("Location: classes.php");
exit();
?>
