<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['nav_token']) || !isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
    header('Location: ../login.php');
    exit;
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            background: var(--bg);
        }
        .sidebar {
            width: 220px;
            background: var(--bg);
            border-left: 1px solid var(--border);
            box-shadow: 0 0 16px #0001;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            padding: 0;
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 10;
            overflow-y: auto;
            max-height: 100vh;
        }
        .sidebar .user-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 32px 0 16px 0;
            border-bottom: 1px solid var(--border);
            background: var(--bg);
        }
        .sidebar .user-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--table-header);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }
        .sidebar .user-name {
            font-weight: bold;
            font-size: 1.1em;
        }
        .sidebar-menu {
            flex: 1;
            padding: 0;
            margin: 0;
            list-style: none;
        }
        .sidebar-menu li {
            display: flex;
            align-items: center;
            padding: 16px 24px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid var(--border);
            background: var(--bg);
        }
        .sidebar-menu li:hover, .sidebar-menu li.active {
            background: var(--table-header);
        }
        .sidebar-menu svg {
            margin-left: 12px;
            min-width: 22px;
            min-height: 22px;
        }
        .sidebar-menu .submenu {
            padding-right: 32px;
            background: var(--table-header);
            display: flex;
            flex-direction: column;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        .sidebar-menu .submenu.expanded {
            max-height: 500px;
            overflow: visible;
        }
        .main-panel {
            flex: 1;
            margin-right: 220px;
            padding: 32px 24px 24px 24px;
            background: var(--bg);
        }
        .user-details {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 2px 8px #0001;
            padding: 24px;
            margin-bottom: 32px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .user-details .change-pass-btn {
            margin-top: 8px;
            width: auto;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 1em;
        }
        @media (max-width: 800px) {
            .sidebar { width: 60px; }
            .sidebar .user-info, .sidebar-menu li span { display: none; }
            .main-panel { margin-right: 60px; }
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/phosphor-icons@1.4.2/src/css/phosphor.css">
</head>
<body>
<script src="../assets/js/theme.js"></script>
<div class="dashboard-container">
    <nav class="sidebar">
        <div class="user-info">
            <div class="user-icon">
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.418 0-8 2.015-8 4.5V21h16v-2.5c0-2.485-3.582-4.5-8-4.5Z"/></svg>
            </div>
            <div class="user-name"><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></div>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.418 0-8 2.015-8 4.5V21h16v-2.5c0-2.485-3.582-4.5-8-4.5Z"/></svg><span>پروفایل</span></li>
            <li onclick="window.location.href='../change_password.php'"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 11V7a5 5 0 0 0-10 0v4M5 11h14v10H5V11Zm7 4v2"/></svg><span>تغییر رمز عبور</span></li>
            <li onclick="window.location.href='users.php'"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3v4M8 3v4"/></svg><span>مدیریت کاربران</span></li>
            <li onclick="window.location.href='roles.php'"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.418 0-8 2.015-8 4.5V21h16v-2.5c0-2.485-3.582-4.5-8-4.5Z"/></svg><span>تغییر نقش کاربران</span></li>
            <li onclick="window.location.href='add_agent.php'"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 5v14m7-7H5"/></svg><span>افزودن پشتیبان جدید</span></li>
            <li onclick="window.location.href='../logout.php'"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 17l5-5-5-5M21 12H9"/><path d="M3 21V3h6"/></svg><span>خروج</span></li>
        </ul>
    </nav>
    <main class="main-panel">
        <div class="user-details">
            <div><b>نام کامل:</b> <?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></div>
            <div><b>ایمیل:</b> <?= htmlspecialchars($admin['email']) ?></div>
            <button class="change-pass-btn" onclick="window.location.href='../change_password.php'">تغییر رمز عبور</button>
        </div>
        <h2>پنل مدیریت</h2>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('tickets-toggle');
    var submenu = document.getElementById('tickets-submenu');
    submenu.classList.remove('expanded'); // default collapsed
    toggle.addEventListener('click', function() {
        submenu.classList.toggle('expanded');
    });
});
</script>
</body>
</html> 