<?php
session_start();
require_once "../config/db.php"; // include database connection

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user by email
    $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // ✅ Login success
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id']; // e.g., 1=Admin, 2=Teacher, 3=Student

        // Redirect to dashboard
        header("Location: ../public/dashboard.php");
        exit();

    } else {
        // ✅ If login fails, redirect back with error flag
        header("Location: ../public/login.php?error=1");
        exit();
    }
}
?>
