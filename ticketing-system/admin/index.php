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
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
    <h2>پنل مدیریت</h2>
    <ul>
        <li><a href="users.php">مدیریت کاربران</a></li>
        <li><a href="roles.php">تغییر نقش کاربران (افزودن پشتیبان)</a></li>
        <li><a href="add_agent.php">افزودن پشتیبان جدید</a></li>
        <li><a href="../logout.php">خروج</a></li>
    </ul>
</body>
</html> 