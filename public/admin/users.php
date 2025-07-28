<?php
global $pdo;
session_start();
require_once __DIR__ . "/../../config/db.php";

// ✅ Only allow Admin (role_id = 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// ✅ Fetch all users
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
    <link rel="stylesheet" href="../assets/style.css?v=<?= time(); ?>">
</head>
<body class="dashboard-page">

<div class="dashboard-wrapper">
    <div class="table-page-wrapper">

        <h1>Manage Users</h1>

        <a href="add_user.php" class="dashboard-btn">+ Add New User</a>

        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']); ?></td>
                    <td><?= htmlspecialchars($user['username']); ?></td>
                    <td><?= htmlspecialchars($user['email']); ?></td>
                    <td><?= htmlspecialchars($user['role_name']); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $user['id']; ?>">✏️ Edit</a> |
                        <a href="delete_user.php?id=<?= $user['id']; ?>" onclick="return confirm('Delete this user?')">🗑 Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <a class="back-link" href="../dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
