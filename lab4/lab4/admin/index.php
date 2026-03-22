<?php
require_once '../includes/config.php';
require_once '../includes/db_connection.php';

// Проверка авторизации и прав админа
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin');
    exit;
}

if (!isAdmin()) {
    die('<div style="text-align: center; padding: 50px; font-family: Arial;">
        <h1 style="color: #ff6b6b;">Access Denied</h1>
        <p>Admin privileges required to access this page.</p>
        <a href="../index.php" style="color: #00ADB5;">Return to Home</a>
    </div>');
}

$db = Database::getInstance();
$page = 'admin';
$page_title = 'Admin Dashboard';

// Получаем статистику
$stats = $db->fetch("
    SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM users WHERE role = 'admin') as admin_users,
        (SELECT COUNT(*) FROM users WHERE is_active = 1) as active_users,
        (SELECT COUNT(*) FROM messages) as total_messages,
        (SELECT COUNT(*) FROM messages WHERE is_read = 0) as unread_messages,
        (SELECT COUNT(*) FROM works) as total_works,
        (SELECT COUNT(*) FROM works WHERE is_published = 1) as published_works,
        (SELECT COUNT(*) FROM messages WHERE DATE(created_at) = CURDATE()) as today_messages
");

// Получаем последние сообщения
$recent_messages = $db->fetchAll("
    SELECT m.*, u.username 
    FROM messages m 
    LEFT JOIN users u ON m.user_id = u.id 
    ORDER BY m.created_at DESC 
    LIMIT 5
");

// Получаем последних пользователей
$recent_users = $db->fetchAll("
    SELECT id, username, email, role, created_at, is_active 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Получаем последние работы
$recent_works = $db->fetchAll("
    SELECT id, title, category, created_at, is_published 
    FROM works 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Получаем активность за последние 7 дней
$activity_data = $db->fetchAll("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as messages,
        (SELECT COUNT(*) FROM users WHERE DATE(created_at) = DATE(m.created_at)) as users
    FROM messages m
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
");

ob_start();
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
        <link rel="stylesheet" href="../styles/normalize.css">
        <link rel="stylesheet" href="../styles/style.css">
        <link rel="stylesheet" href="../styles/admin.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
    <?php
    include '../includes/header.php';
    ?>

    <main class="main">
        <div class="admin-container">
            <div class="admin-header">
                <div class="welcome-box">
                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['user_name']); ?>!</h1>
                    <p>Here's what's happening with your site today.</p>
                </div>

                <div class="admin-actions">
                    <a href="../index.php" class="admin-btn btn-secondary" target="_blank">🌐 View Site</a>
                    <a href="logout.php" class="admin-btn btn-danger">🚪 Logout</a>
                </div>
            </div>


            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid #00ADB5;">
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Users</div>
                    <div style="margin-top: 10px; font-size: 14px; color: #aaa;">
                        <?php echo $stats['admin_users']; ?> admins • <?php echo $stats['active_users']; ?> active
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #ff6b6b;">
                    <div class="stat-number"><?php echo $stats['unread_messages']; ?></div>
                    <div class="stat-label">Unread Messages</div>
                    <div style="margin-top: 10px; font-size: 14px; color: #aaa;">
                        <?php echo $stats['total_messages']; ?> total • <?php echo $stats['today_messages']; ?> today
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #28a745;">
                    <div class="stat-number"><?php echo $stats['published_works']; ?></div>
                    <div class="stat-label">Published Works</div>
                    <div style="margin-top: 10px; font-size: 14px; color: #aaa;">
                        <?php echo $stats['total_works']; ?> total • <?php echo $stats['total_works'] - $stats['published_works']; ?> drafts
                    </div>
                </div>

                <div class="stat-card" style="border-top: 4px solid #ffc107;">
                    <div class="stat-number"><?php echo $stats['today_messages']; ?></div>
                    <div class="stat-label">Messages Today</div>
                    <div style="margin-top: 10px; font-size: 14px; color: #aaa;">
                        Last 7 days: <?php echo array_sum(array_column($activity_data, 'messages')); ?>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
                <a href="messages.php" class="admin-btn btn-primary"> Manage Messages</a>
                <a href="users.php" class="admin-btn btn-primary"> Manage Users</a>
                <a href="works.php" class="admin-btn btn-primary"> Manage Works</a>
                <a href="../contact.php" class="admin-btn btn-secondary" target="_blank"> View Contact Form</a>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">Recent Messages</div>
                        <a href="messages.php" class="card-link">View All →</a>
                    </div>

                    <?php if (empty($recent_messages)): ?>
                        <div style="text-align: center; padding: 30px; color: #888;">
                            <div style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;">📭</div>
                            <p>No messages yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_messages as $msg): ?>
                            <div class="activity-item">
                                <div class="activity-icon icon-message">💬</div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?php echo htmlspecialchars($msg['name']); ?>
                                        <span class="status-badge <?php echo $msg['is_read'] ? 'badge-info' : 'badge-warning'; ?>" style="margin-left: 10px;">
                                    <?php echo $msg['is_read'] ? 'Read' : 'New'; ?>
                                </span>
                                    </div>
                                    <div class="activity-meta">
                                        <?php echo htmlspecialchars($msg['email']); ?> •
                                        <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">Recent Users</div>
                        <a href="users.php" class="card-link">View All →</a>
                    </div>

                    <?php if (empty($recent_users)): ?>
                        <div style="text-align: center; padding: 30px; color: #888;">
                            <div style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;">👤</div>
                            <p>No users yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_users as $user): ?>
                            <div class="activity-item">
                                <div class="activity-icon icon-user">👤</div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                        <span class="status-badge <?php echo $user['role'] == 'admin' ? 'badge-info' : 'badge-success'; ?>" style="margin-left: 10px;">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                                    </div>
                                    <div class="activity-meta">
                                        <?php echo htmlspecialchars($user['email']); ?> •
                                        <?php echo date('d M', strtotime($user['created_at'])); ?>
                                        <span class="status-badge <?php echo $user['is_active'] ? 'badge-success' : 'badge-danger'; ?>" style="margin-left: 10px;">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">Recent Works</div>
                        <a href="works.php" class="card-link">View All →</a>
                    </div>

                    <?php if (empty($recent_works)): ?>
                        <div style="text-align: center; padding: 30px; color: #888;">
                            <div style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;">🎨</div>
                            <p>No works yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_works as $work): ?>
                            <div class="activity-item">
                                <div class="activity-icon icon-work">🎨</div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?php echo htmlspecialchars($work['title']); ?>
                                        <span class="status-badge <?php echo $work['is_published'] ? 'badge-success' : 'badge-warning'; ?>" style="margin-left: 10px;">
                                    <?php echo $work['is_published'] ? 'Published' : 'Draft'; ?>
                                </span>
                                    </div>
                                    <div class="activity-meta">
                                        <?php echo htmlspecialchars($work['category']); ?> •
                                        <?php echo date('d M', strtotime($work['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>


            <div class="quick-stats">
                <div class="quick-stat">
                    <div class="quick-number"><?php echo $db->fetch("SELECT COUNT(*) as count FROM messages WHERE DATE(created_at) = CURDATE()")['count']; ?></div>
                    <div class="quick-label">Messages Today</div>
                </div>
                <div class="quick-stat">
                    <div class="quick-number"><?php echo $db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")['count']; ?></div>
                    <div class="quick-label">New Users Today</div>
                </div>
                <div class="quick-stat">
                    <div class="quick-number"><?php echo round($stats['total_messages'] > 0 ? ($stats['unread_messages'] / $stats['total_messages'] * 100) : 0, 1); ?>%</div>
                    <div class="quick-label">Unread Rate</div>
                </div>
                <div class="quick-stat">
                    <div class="quick-number"><?php echo round($stats['total_users'] > 0 ? ($stats['active_users'] / $stats['total_users'] * 100) : 0, 1); ?>%</div>
                    <div class="quick-label">Active Users</div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    </body>
    </html>
<?php
$content = ob_get_clean();
echo $content;
?>