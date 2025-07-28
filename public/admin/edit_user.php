<?php
global $pdo;
session_start();
require_once __DIR__ . "/../../config/db.php";

// ✅ Only Admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// ✅ Get user ID from URL
$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header("Location: users.php");
    exit();
}

// ✅ Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ If user not found, redirect back
if (!$user) {
    header("Location: users.php");
    exit();
}

// ✅ Fetch roles
$roles_stmt = $pdo->query("SELECT * FROM roles");
$roles = $roles_stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role_id = $_POST['role_id'];

    $update_stmt = $pdo->prepare("UPDATE users SET username=?, email=?, role_id=? WHERE id=?");
    $update_stmt->execute([$username, $email, $role_id, $user_id]);

    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/style.css?v=<?= time(); ?>">
</head>
<body class="dashboard-page">

<div class="dashboard-wrapper">
    <div class="dashboard-card wide-view">

        <h1>Edit User</h1>

        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username"
                   value="<?= htmlspecialchars($user['username']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($user['email']); ?>" required>

            <label for="role_id">Role:</label>
            <select id="role_id" name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id']; ?>" <?= $role['id'] == $user['role_id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($role['role_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-submit">Update</button>
        </form>

        <a class="back-link" href="users.php">⬅ Back</a>

    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
