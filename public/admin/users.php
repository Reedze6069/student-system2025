<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

//  Only allow Admin (role_id = 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Fetch all users
$stmt = $pdo->query("SELECT u.id, u.username, u.email, r.role_name 
                     FROM users u 
                     JOIN roles r ON u.role_id = r.id 
                     ORDER BY u.id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
</head>
<body>
<h1>Manage Users</h1>
<a href="add_user.php">+ Add New User</a>
<br><br>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['id']); ?></td>
            <td><?= htmlspecialchars($user['username']); ?></td>
            <td><?= htmlspecialchars($user['email']); ?></td>
            <td><?= htmlspecialchars($user['role_name']); ?></td>
            <td>
                <a href="edit_user.php?id=<?= $user['id']; ?>">Edit</a> |
                <a href="delete_user.php?id=<?= $user['id']; ?>" onclick="return confirm('Delete this user?')">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<br>
<a href="../dashboard.php">⬅ Back to Dashboard</a>
</body>
</html>
