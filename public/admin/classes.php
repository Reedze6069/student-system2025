<?php
global $pdo;
session_start();
require_once __DIR__ . "/../../config/db.php";

// ✅ Only Admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

// ✅ Fetch all classes with subject + teacher info
$stmt = $pdo->query("
  SELECT c.id, s.name AS subject_name, u.username AS teacher_name, c.room, c.start_time, c.end_time
  FROM classes c
  JOIN subjects s ON c.subject_id = s.id
  LEFT JOIN users u ON c.teacher_id = u.id
  ORDER BY c.id ASC
");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Classes</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="dashboard-page">
<div class="dashboard-wrapper">
    <div class="dashboard-card teacher-view">
        <h1>Manage Classes</h1>

        <!-- ✅ Inline Feedback Message Boxes -->
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #fff3f3; border: 1px solid #f5c2c2; padding: 10px 15px; border-radius: 6px; color: #b10000; font-weight: 500; max-width: 600px; margin: 10px 0;">
                <?= $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #e8f8ec; border: 1px solid #c1e6cd; padding: 10px 15px; border-radius: 6px; color: #2f7a41; font-weight: 500; max-width: 600px; margin: 10px 0;">
                <?= $_SESSION['success']; ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <a class="dashboard-btn" href="add_class.php">+ Add New Class</a>
        <br><br>

        <table>
            <tr>
                <th>ID</th>
                <th>Subject</th>
                <th>Teacher</th>
                <th>Room</th>
                <th>Schedule</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($classes as $class): ?>
                <tr>
                    <td><?= $class['id'] ?></td>
                    <td><?= htmlspecialchars($class['subject_name']) ?></td>
                    <td><?= $class['teacher_name'] ?? 'Not Assigned' ?></td>
                    <td><?= htmlspecialchars($class['room']) ?></td>
                    <td>
                        <?= htmlspecialchars($class['start_time']) ?> →
                        <?= htmlspecialchars($class['end_time']) ?>
                    </td>
                    <td>
                        <a href="edit_class.php?id=<?= $class['id'] ?>">Edit</a> |
                        <a href="delete_class.php?id=<?= $class['id'] ?>" onclick="return confirm('Delete this class?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <br>
        <a class="back-link" href="../dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
