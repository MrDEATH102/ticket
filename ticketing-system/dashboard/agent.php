<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['nav_token']) || !isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
    header('Location: ../login.php');
    exit;
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header('Location: ../login.php');
    exit;
}
$agent_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();
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
    <link rel="stylesheet" href="https://unpkg.com/phosphor-icons@1.4.2/src/css/phosphor.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<script src="../assets/js/theme.js"></script>
<div class="dashboard-container">
    <nav class="sidebar">
        <div class="user-info">
            <div class="user-icon">
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.418 0-8 2.015-8 4.5V21h16v-2.5c0-2.485-3.582-4.5-8-4.5Z"/></svg>
            </div>
            <div class="user-name"><?= htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']) ?></div>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.418 0-8 2.015-8 4.5V21h16v-2.5c0-2.485-3.582-4.5-8-4.5Z"/></svg><span>پروفایل</span></li>
            <li onclick="window.location.href='../change_password.php'"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 11V7a5 5 0 0 0-10 0v4M5 11h14v10H5V11Zm7 4v2"/></svg><span>تغییر رمز عبور</span></li>
            <li id="tickets-toggle" class="tickets-toggle"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7h18M3 12h18M3 17h18"/></svg><span>تیکت‌ها</span></li>
            <ul class="sidebar-menu submenu" id="tickets-submenu">
                <li><a href="agent.php?status=open" id="filter-open" class="ticket-filter<?= (isset($_GET['status']) && $_GET['status'] === 'open') ? ' active' : '' ?>"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 12l2 2 4-4"/></svg><span>باز</span></a></li>
                <li><a href="agent.php?status=pending" id="filter-pending" class="ticket-filter<?= (isset($_GET['status']) && $_GET['status'] === 'pending') ? ' active' : '' ?>"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg><span>در انتظار</span></a></li>
                <li><a href="agent.php?status=answered" id="filter-answered" class="ticket-filter<?= (isset($_GET['status']) && $_GET['status'] === 'answered') ? ' active' : '' ?>"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 12l2 2 4-4"/></svg><span>پاسخ داده شده</span></a></li>
                <li><a href="agent.php?status=closed" id="filter-closed" class="ticket-filter<?= (isset($_GET['status']) && $_GET['status'] === 'closed') ? ' active' : '' ?>"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M6 12h12"/></svg><span>بسته</span></a></li>
            </ul>
            <li onclick="window.location.href='../logout.php'"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 17l5-5-5-5M21 12H9"/><path d="M3 21V3h6"/></svg><span>خروج</span></li>
        </ul>
    </nav>
    <main class="main-panel">
        <div class="user-details">
            <div><b>نام کامل:</b> <?= htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']) ?></div>
            <div><b>ایمیل:</b> <?= htmlspecialchars($agent['email']) ?></div>
            <button class="change-pass-btn" onclick="window.location.href='../change_password.php'">تغییر رمز عبور</button>
        </div>
    <h2>تیکت‌های من و آزاد</h2>
    <table border="1" cellpadding="5" class="dashboard-table">
        <tr>
            <th>کد تیکت</th>
            <th>عنوان</th>
            <th>کاربر</th>
            <th>اولویت</th>
            <th>وضعیت</th>
            <th>تاریخ</th>
            <th>مشاهده</th>
        </tr>
            <?php
            $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
            if ($status_filter && in_array($status_filter, ['open','pending','answered','closed'])) {
                $stmt = $pdo->prepare('SELECT * FROM tickets WHERE (agent_id IS NULL OR agent_id = ?) AND status = ? ORDER BY status, created_at DESC');
                $stmt->execute([$agent_id, $status_filter]);
                $tickets = $stmt->fetchAll();
            } else {
                $stmt = $pdo->prepare('SELECT * FROM tickets WHERE agent_id IS NULL OR agent_id = ? ORDER BY status, created_at DESC');
                $stmt->execute([$agent_id]);
                $tickets = $stmt->fetchAll();
            }
            ?>
        <?php foreach ($tickets as $t): ?>
            <tr id="<?= htmlspecialchars($t['status']) ?>">
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