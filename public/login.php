<?php
global $pdo;
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in both fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <!-- ✅ Use the external stylesheet -->
    <link rel="stylesheet" href="assets/style.css?v=<?php echo time(); ?>">
</head>

<body class="login-page">

<div class="login-wrapper">
    <div class="login-card">
        <h2>Login</h2>

        <?php if (!empty($error)) : ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form id="loginForm" method="POST">
            <label>Email</label>
            <input type="email" id="email" name="email" required>

            <label>Password</label>
            <input type="password" id="password" name="password" required>

            <div id="error-message"></div>
            <button type="submit">Login</button>
        </form>

        <p>Welcome to the Student Information System</p>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
<script src="assets/validation.js?v=<?php echo time(); ?>"></script>

</body>
</html>

