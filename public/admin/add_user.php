<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

// ✅ Only Admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Fetch roles for dropdown
$roles_stmt = $pdo->query("SELECT * FROM roles");
$roles = $roles_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = $_POST['role_id'];

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $password, $role_id]);

    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User</title>
</head>
<body>
<h1>Add New User</h1>
<form method="POST">
    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Role:</label><br>
    <select name="role_id" required>
        <?php foreach ($roles as $role): ?>
            <option value="<?= $role['id']; ?>"><?= htmlspecialchars($role['role_name']); ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Add User</button>
</form>
<br>
<a href="users.php">⬅ Back</a>
</body>
</html>
