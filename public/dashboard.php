<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role_id'];

echo "<h1>Welcome!</h1>";

if ($role == 1) {
    echo "You are an Admin!";
} elseif ($role == 2) {
    echo "You are a Teacher!";
} else {
    echo "You are a Student!";
}

echo '<br><br><a href="logout.php">Logout</a>';
