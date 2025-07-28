<?php
global $pdo;
session_start();
require_once __DIR__ . "/../../config/db.php";

// ✅ Only Admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// ✅ Fetch roles
$roles_stmt = $pdo->query("SELECT * FROM roles");
$roles = $roles_stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Handle submission
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
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">
<div class="dashboard-wrapper">
    <div class="dashboard-card wide-view">
        <h1>Add New User</h1>

        <form method="POST">
            <label for="username">Username:</label>
            <input id="username" type="text" name="username" required>

            <label for="email">Email:</label>
            <input id="email" type="email" name="email" required>

            <label for="password">Password:</label>
            <input id="password" type="password" name="password" required>

            <label for="role_id">Role:</label>
            <select id="role_id" name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id']; ?>"><?= htmlspecialchars($role['role_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-submit">Add User</button>
        </form>

        <a class="back-link" href="users.php">⬅ Back</a>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
