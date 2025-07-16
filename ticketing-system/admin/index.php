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

        .sidebar-menu li:hover,
        .sidebar-menu li.active {
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
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

        .ticket-stats {
            display: flex;
            gap: 2rem;
            justify-content: flex-start;
            margin-bottom: 2rem;
        }

        .ticket-stats .stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 80px;
            background: var(--table-header);
            border-radius: 10px;
            padding: 12px 18px;
            box-shadow: 0 2px 8px #0001;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }

        .stat-label {
            font-size: 1rem;
            margin-top: 0.5rem;
        }

        @media (max-width: 800px) {
            .sidebar {
                width: 60px;
            }

            .sidebar .user-info,
            .sidebar-menu li span {
                display: none;
            }

            .main-panel {
                margin-right: 60px;
            }
        }

        .report-section {
            background: var(--bg);
            color: var(--text);
            border-radius: 12px;
            box-shadow: 0 2px 8px #0002;
            padding: 24px 12px 32px 12px;
            margin: 32px auto 0 auto;
            max-width: 98vw;
        }

        .report-table-container {
            width: 100%;
            overflow-x: auto;
            max-width: 100vw;
            margin-top: 16px;
        }

        .reports-table {
            min-width: 900px;
            width: 100%;
            background: var(--bg);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .reports-table th,
        .reports-table td {
            background: none;
            color: inherit;
        }

        .view-link {
            color: var(--primary) !important;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.2s;
        }

        .view-link:hover {
            text-decoration: underline;
            color: #125ea2 !important;
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
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.418 0-8 2.015-8 4.5V21h16v-2.5c0-2.485-3.582-4.5-8-4.5Z" />
                    </svg>
                </div>
                <div class="user-name"><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></div>
            </div>
            <ul class="sidebar-menu">
                <li class="active"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.418 0-8 2.015-8 4.5V21h16v-2.5c0-2.485-3.582-4.5-8-4.5Z" />
                    </svg><span>پروفایل</span></li>
                <li onclick="window.location.href='../change_password.php'"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M17 11V7a5 5 0 0 0-10 0v4M5 11h14v10H5V11Zm7 4v2" />
                    </svg><span>تغییر رمز عبور</span></li>
                <li onclick="window.location.href='users.php'"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="7" width="18" height="13" rx="2" />
                        <path d="M16 3v4M8 3v4" />
                    </svg><span>مدیریت کاربران</span></li>
                <li onclick="window.location.href='add_agent.php'"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 5v14m7-7H5" />
                    </svg><span>افزودن پشتیبان جدید</span></li>
                <li onclick="window.location.href='../logout.php'"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M16 17l5-5-5-5M21 12H9" />
                        <path d="M3 21V3h6" />
                    </svg><span>خروج</span></li>
            </ul>
        </nav>
        <main class="main-panel">
            <div class="user-details">
                <div><b>نام کامل:</b> <?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></div>
                <div><b>ایمیل:</b> <?= htmlspecialchars($admin['email']) ?></div>
                <button class="change-pass-btn" onclick="window.location.href='../change_password.php'">تغییر رمز عبور</button>
            </div>
            <h2>پنل مدیریت</h2>
            <?php
            // --- Ticket Report Section (copied from reports.php) ---
            // Get filter parameter
            $status_filter = $_GET['status'] ?? 'all';
            // Build query based on filter
            $query = "SELECT t.*, 
                  u.first_name as user_first_name, u.last_name as user_last_name, u.email as user_email,
                  a.first_name as agent_first_name, a.last_name as agent_last_name, a.email as agent_email
                  FROM tickets t 
                  LEFT JOIN users u ON t.user_id = u.id 
                  LEFT JOIN users a ON t.agent_id = a.id";
            $params = [];
            if ($status_filter !== 'all') {
                $query .= " WHERE t.status = ?";
                $params[] = $status_filter;
            }
            $query .= " ORDER BY t.created_at DESC";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $tickets = $stmt->fetchAll();
            // Get ticket counts for each status
            $counts_query = "SELECT status, COUNT(*) as count FROM tickets GROUP BY status";
            $counts_stmt = $pdo->prepare($counts_query);
            $counts_stmt->execute();
            $counts_result = $counts_stmt->fetchAll();
            $counts = [];
            foreach ($counts_result as $row) {
                $counts[$row['status']] = $row['count'];
            }
            // Get total count
            $total_query = "SELECT COUNT(*) as total FROM tickets";
            $total_stmt = $pdo->prepare($total_query);
            $total_stmt->execute();
            $total_count = $total_stmt->fetchColumn();
            ?>
            <!-- BEGIN REPORT SECTION -->
            <div class="report-section">
                <div class="report-header">
                    <h2>گزارش تیکت‌ها</h2>
                </div>
                <div class="ticket-stats">
                    <div class="stat">
                        <div class="stat-number"><?= $total_count ?></div>
                        <div class="stat-label">کل تیکت‌ها</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?= $counts['open'] ?? 0 ?></div>
                        <div class="stat-label">باز</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?= $counts['pending'] ?? 0 ?></div>
                        <div class="stat-label">در انتظار</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?= $counts['answered'] ?? 0 ?></div>
                        <div class="stat-label">پاسخ داده شده</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?= $counts['closed'] ?? 0 ?></div>
                        <div class="stat-label">بسته</div>
                    </div>
                </div>
                <div class="filter-tabs">
                    <a href="?status=all" class="filter-tab <?= $status_filter === 'all' ? 'active' : '' ?>">همه</a>
                    <a href="?status=open" class="filter-tab <?= $status_filter === 'open' ? 'active' : '' ?>">باز</a>
                    <a href="?status=pending" class="filter-tab <?= $status_filter === 'pending' ? 'active' : '' ?>">در انتظار</a>
                    <a href="?status=answered" class="filter-tab <?= $status_filter === 'answered' ? 'active' : '' ?>">پاسخ داده شده</a>
                    <a href="?status=closed" class="filter-tab <?= $status_filter === 'closed' ? 'active' : '' ?>">بسته</a>
                </div>
                <div class="report-table-container">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>عنوان و محتوا</th>
                                <th>کاربر</th>
                                <th>پشتیبان</th>
                                <th>وضعیت</th>
                                <th>تاریخ ایجاد</th>
                                <th>آخرین بروزرسانی</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tickets)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 32px; color: #666;">
                                        <?php if ($status_filter === 'all'): ?>
                                            هیچ تیکتی یافت نشد.
                                        <?php else: ?>
                                            هیچ تیکتی با وضعیت "<?= $status_filter ?>" یافت نشد.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td>#<?= $ticket['id'] ?></td>
                                        <td>
                                            <div class="ticket-title"><?= htmlspecialchars($ticket['title']) ?></div>
                                            <div class="ticket-preview"><?= htmlspecialchars(substr($ticket['content'], 0, 100)) ?>...</div>
                                        </td>
                                        <td>
                                            <div class="agent-info">
                                                <div class="agent-name"><?= htmlspecialchars($ticket['user_first_name'] . ' ' . $ticket['user_last_name']) ?></div>
                                                <div class="agent-email"><?= htmlspecialchars($ticket['user_email']) ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($ticket['agent_id']): ?>
                                                <div class="agent-info">
                                                    <div class="agent-name"><?= htmlspecialchars($ticket['agent_first_name'] . ' ' . $ticket['agent_last_name']) ?></div>
                                                    <div class="agent-email"><?= htmlspecialchars($ticket['agent_email']) ?></div>
                                                </div>
                                            <?php else: ?>
                                                <div class="no-agent">تخصیص نیافته</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_classes = [
                                                'open' => 'status-open',
                                                'pending' => 'status-pending',
                                                'answered' => 'status-answered',
                                                'closed' => 'status-closed'
                                            ];
                                            $status_labels = [
                                                'open' => 'باز',
                                                'pending' => 'در انتظار',
                                                'answered' => 'پاسخ داده شده',
                                                'closed' => 'بسته'
                                            ];
                                            ?>
                                            <span class="status-badge <?= $status_classes[$ticket['status']] ?>">
                                                <?= $status_labels[$ticket['status']] ?>
                                            </span>
                                        </td>
                                        <td><?= date('Y/m/d H:i', strtotime($ticket['created_at'])) ?></td>
                                        <td><?= date('Y/m/d H:i', strtotime($ticket['updated_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END REPORT SECTION -->
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