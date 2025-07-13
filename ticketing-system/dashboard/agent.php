<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header('Location: ../login.php');
    exit;
}
$agent_id = $_SESSION['user_id'];
// تیکت‌های آزاد یا اختصاص‌یافته به این agent
$stmt = $pdo->prepare('SELECT * FROM tickets WHERE agent_id IS NULL OR agent_id = ? ORDER BY status, created_at DESC');
$stmt->execute([$agent_id]);
$tickets = $stmt->fetchAll();
function status_fa($status) {
    switch ($status) {
        case 'open': return 'باز';
        case 'answered': return 'پاسخ داده شده';
        case 'pending': return 'در انتظار';
        case 'closed': return 'بسته';
        default: return $status;
    }
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
    <h2>تیکت‌های من و آزاد</h2>
    <a href="../logout.php">خروج</a>
    <table border="1" cellpadding="5" style="margin-top:10px;">
        <tr>
            <th>کد تیکت</th>
            <th>عنوان</th>
            <th>کاربر</th>
            <th>اولویت</th>
            <th>وضعیت</th>
            <th>تاریخ</th>
            <th>مشاهده</th>
        </tr>
        <?php foreach ($tickets as $t): ?>
        <tr>
            <td><?= htmlspecialchars($t['unique_code']) ?></td>
            <td><?= htmlspecialchars($t['title']) ?></td>
            <td>
                <?php
                $u = $pdo->prepare('SELECT first_name, last_name FROM users WHERE id = ?');
                $u->execute([$t['user_id']]);
                $user = $u->fetch();
                echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                ?>
            </td>
            <td><?= htmlspecialchars($t['priority']) ?></td>
            <td><?= status_fa($t['status']) ?></td>
            <td><?= htmlspecialchars($t['created_at']) ?></td>
            <td><a href="../ticket/agent_view.php?id=<?= $t['id'] ?>">مشاهده</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html> 