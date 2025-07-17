<?php
global $pdo;
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Get user ID from URL
$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header("Location: users.php");
    exit();
}

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch roles
$roles_stmt = $pdo->query("SELECT * FROM roles");
$roles = $roles_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role_id = $_POST['role_id'];

    $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, role_id=? WHERE id=?");
    $stmt->execute([$username, $email, $role_id, $user_id]);

    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
</head>
<body>
<h1>Edit User</h1>
<form method="POST">
    <label>Username:</label><br>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required><br><br>

    <label>Role:</label><br>
    <select name="role_id" required>
        <?php foreach ($roles as $role): ?>
            <option value="<?= $role['id']; ?>" <?= $role['id'] == $user['role_id'] ? 'selected' : ''; ?>>
                <?= htmlspecialchars($role['role_name']); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Update</button>
</form>
<br>
<a href="users.php">⬅ Back</a>
</body>
</html>
