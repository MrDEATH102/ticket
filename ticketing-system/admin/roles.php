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
// تغییر نقش
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = intval($_POST['uid'] ?? 0);
    $role = $_POST['role'] ?? '';
    if (in_array($role, ['user','agent']) && $uid !== $_SESSION['user_id']) {
        $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $uid]);
    }
    header('Location: roles.php');
    exit;
}
$stmt = $pdo->query("SELECT id, first_name, last_name, email, role FROM users WHERE role != 'admin' ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
    <a href="index.php">بازگشت به پنل مدیریت</a>
    <div class="h-sign">
        <h2>تغییر نقش کاربران (افزودن/حذف پشتیبان)</h2>
    </div>
    <table border="1" cellpadding="5">
        <tr>
            <th>نام</th>
            <th>ایمیل</th>
            <th>نقش فعلی</th>
            <th>تغییر نقش</th>
        </tr>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['role']) ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                    <select name="role">
                        <option value="user" <?= $u['role']==='user'?'selected':'' ?>>user</option>
                        <option value="agent" <?= $u['role']==='agent'?'selected':'' ?>>agent</option>
                    </select>
                    <button type="submit">ثبت</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html> 