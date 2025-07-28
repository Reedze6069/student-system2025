<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?? 'Student Management System' ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<header class="main-header">
    <h1>🎓 Student Management System</h1>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="../dashboard.php">🏠 Dashboard</a>
            <a href="../logout.php">🚪 Logout</a>
        <?php else: ?>
            <a href="../login.php">🔑 Login</a>
        <?php endif; ?>
    </nav>
</header>

<main class="content">
