<?php
require_once __DIR__ . '/../config.php';
session_start();
// Navigation token check
if (!isset($_SESSION['nav_token']) || !isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
    header('Location: ../login.php');
    exit;
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
// حذف کاربر
if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    if ($uid !== $_SESSION['user_id']) { // admin خودش را حذف نکند
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]);
    }
    header('Location: users.php');
    exit;
}
$stmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
    <a href="index.php">بازگشت به پنل مدیریت</a>
    <h2>لیست کاربران</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>نام</th>
            <th>ایمیل</th>
            <th>موبایل</th>
            <th>نقش</th>
            <th>تاریخ ثبت</th>
            <th>عملیات</th>
        </tr>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['mobile']) ?></td>
            <td><?= htmlspecialchars($u['role']) ?></td>
            <td><?= htmlspecialchars($u['created_at']) ?></td>
            <td>
                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                    <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('حذف کاربر؟')">حذف</a>
                <?php else: ?>
                    ---
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html> 