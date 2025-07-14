<?php
require_once __DIR__ . '/../config.php';
session_start();
// Set navigation token if not set
if (!isset($_SESSION['nav_token'])) {
    $_SESSION['nav_token'] = bin2hex(random_bytes(16));
}
// Navigation token check
if (!isset($_SESSION['nav_token']) || !isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
    header('Location: ../login.php');
    exit;
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user_id]);
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
    <h2>تیکت‌های من</h2>
    <a href="../ticket/new.php">ارسال تیکت جدید</a> | <a href="../logout.php">خروج</a>
    <table border="1" cellpadding="5" style="margin-top:10px;">
        <tr>
            <th>کد تیکت</th>
            <th>عنوان</th>
            <th>اولویت</th>
            <th>وضعیت</th>
            <th>تاریخ</th>
            <th>مشاهده</th>
        </tr>
        <?php foreach ($tickets as $t): ?>
        <tr>
            <td><?= htmlspecialchars($t['unique_code']) ?></td>
            <td><?= htmlspecialchars($t['title']) ?></td>
            <td><?= htmlspecialchars($t['priority']) ?></td>
            <td><?= status_fa($t['status']) ?></td>
            <td><?= htmlspecialchars($t['created_at']) ?></td>
            <td><a href="../ticket/view.php?id=<?= $t['id'] ?>">مشاهده</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html> 