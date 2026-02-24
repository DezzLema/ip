<?php
require_once '../includes/config.php';
require_once '../includes/db_connection.php';

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
$page_title = 'Admin - Messages';

// Фильтры и поиск
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all'; // all, read, unread
$page_num = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page_num - 1) * $per_page;

// Построение запроса с фильтрами
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(m.name LIKE ? OR m.email LIKE ? OR m.message LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($filter === 'read') {
    $where_conditions[] = "m.is_read = 1";
} elseif ($filter === 'unread') {
    $where_conditions[] = "m.is_read = 0";
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Получаем сообщения
$limit = (int)$per_page;
$offset_value = (int)$offset;

$query = "
    SELECT m.*, u.username 
    FROM messages m 
    LEFT JOIN users u ON m.user_id = u.id 
    $where_clause
    ORDER BY m.created_at DESC 
    LIMIT $limit OFFSET $offset_value
";

$messages = $db->fetchAll($query, $params);


$total_count = $db->fetch("
    SELECT COUNT(*) as total 
    FROM messages m 
    LEFT JOIN users u ON m.user_id = u.id 
    $where_clause
", $params)['total'];

$total_pages = ceil($total_count / $per_page);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_all_read'])) {
        $db->query("UPDATE messages SET is_read = 1 WHERE is_read = 0");
        $_SESSION['message'] = 'All messages marked as read';
        header('Location: messages.php');
        exit;
    }

    if (isset($_POST['delete_message'])) {
        $message_id = $_POST['message_id'];
        $db->query("DELETE FROM messages WHERE id = ?", [$message_id]);
        $_SESSION['message'] = 'Message deleted successfully';
        header('Location: messages.php' . ($search ? '?search=' . urlencode($search) : ''));
        exit;
    }

    if (isset($_POST['delete_all_read'])) {
        $db->query("DELETE FROM messages WHERE is_read = 1");
        $_SESSION['message'] = 'All read messages deleted';
        header('Location: messages.php');
        exit;
    }
}

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
        <style>
            .admin-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }

            .admin-header {
                background: rgba(34,40,49,0.9);
                padding: 20px;
                border-radius: 12px;
                margin-bottom: 30px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 20px;
            }

            .message-item {
                transition: all 0.3s ease;
                border-radius: 8px;
                margin-bottom: 15px;
                overflow: hidden;
                background: rgba(57,62,70,0.6);
            }

            .message-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }

            .unread {
                border-left: 4px solid #ff6b6b;
                background: rgba(255,107,107,0.05);
            }

            .read {
                border-left: 4px solid #00ADB5;
                background: rgba(0,173,181,0.05);
            }

            .message-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px 20px;
                cursor: pointer;
            }

            .message-content {
                padding: 0 20px;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
            }

            .message-content.expanded {
                padding: 20px;
                max-height: 500px;
            }

            .badge {
                padding: 3px 10px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
            }

            .badge-unread {
                background: rgba(255,107,107,0.2);
                color: #ff6b6b;
            }

            .badge-read {
                background: rgba(0,173,181,0.2);
                color: #00ADB5;
            }

            .badge-user {
                background: rgba(40,167,69,0.2);
                color: #28a745;
            }

            .filters {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            .filter-btn {
                padding: 8px 20px;
                border-radius: 20px;
                background: rgba(57,62,70,0.6);
                border: 1px solid rgba(255,255,255,0.1);
                color: #eee;
                cursor: pointer;
                transition: all 0.3s ease;
                text-decoration: none;
                display: inline-block;
            }

            .filter-btn:hover, .filter-btn.active {
                background: #00ADB5;
                color: white;
            }

            .search-box {
                display: flex;
                gap: 10px;
                align-items: center;
            }

            .search-input {
                padding: 10px 15px;
                border-radius: 8px;
                border: 1px solid rgba(255,255,255,0.1);
                background: rgba(255,255,255,0.05);
                color: #eee;
                min-width: 250px;
            }

            .action-buttons {
                display: flex;
                gap: 10px;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }

            .action-btn {
                padding: 10px 20px;
                border-radius: 8px;
                border: none;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s ease;
                text-decoration: none;
                display: inline-block;
            }

            .btn-primary {
                background: #00ADB5;
                color: white;
            }

            .btn-danger {
                background: rgba(255,107,107,0.2);
                color: #ff6b6b;
                border: 1px solid rgba(255,107,107,0.3);
            }

            .btn-secondary {
                background: rgba(57,62,70,0.6);
                color: #eee;
                border: 1px solid rgba(255,255,255,0.1);
            }

            .pagination {
                display: flex;
                justify-content: center;
                gap: 10px;
                margin-top: 30px;
                flex-wrap: wrap;
            }

            .page-link {
                padding: 8px 15px;
                border-radius: 6px;
                background: rgba(57,62,70,0.6);
                color: #eee;
                text-decoration: none;
                transition: all 0.3s ease;
            }

            .page-link:hover, .page-link.active {
                background: #00ADB5;
                color: white;
            }

            .empty-state {
                text-align: center;
                padding: 50px 20px;
                color: #888;
            }

            .empty-state-icon {
                font-size: 48px;
                margin-bottom: 20px;
                opacity: 0.5;
            }
        </style>
    </head>
    <body>
    <?php include '../includes/header.php'; ?>

    <main class="main">
        <div class="admin-container">
            <div class="admin-header">
                <div>
                    <h1 style="color: #00ADB5; margin-bottom: 10px;">Messages Management</h1>
                    <p style="color: #aaa; font-size: 14px;">
                        Total: <?php echo $total_count; ?> messages |
                        Unread: <?php echo $db->fetch("SELECT COUNT(*) as count FROM messages WHERE is_read = 0")['count']; ?>
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div class="filters">
                        <a href="?filter=all<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                           class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                        <a href="?filter=unread<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                           class="filter-btn <?php echo $filter === 'unread' ? 'active' : ''; ?>">Unread</a>
                        <a href="?filter=read<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                           class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>">Read</a>
                    </div>

                    <form method="GET" class="search-box">
                        <input type="text" name="search" class="search-input"
                               placeholder="Search messages..."
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="action-btn btn-primary">Search</button>
                        <?php if ($search): ?>
                            <a href="messages.php" class="action-btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="action-buttons">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="mark_all_read" class="action-btn btn-primary"
                            onclick="return confirm('Mark all messages as read?')">
                        📨 Mark All as Read
                    </button>
                </form>

                <form method="POST" style="display: inline;">
                    <button type="submit" name="delete_all_read" class="action-btn btn-danger"
                            onclick="return confirm('Delete all read messages? This action cannot be undone.')">
                        🗑️ Delete All Read
                    </button>
                </form>

                <a href="index.php" class="action-btn btn-secondary">← Dashboard</a>
                <a href="logout.php" class="action-btn btn-secondary" style="background-color: #ff6b6b;">Logout</a>
            </div>

            <div style="margin-top: 30px;">
                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3>No messages found</h3>
                        <p><?php echo $search ? 'Try a different search term' : 'No messages have been sent yet.'; ?></p>
                    </div>
                <?php else: ?>

                    <?php foreach ($messages as $msg): ?>
                        <div class="message-item <?php echo $msg['is_read'] ? 'read' : 'unread'; ?>" id="message-<?php echo $msg['id']; ?>">
                            <div class="message-meta" onclick="toggleMessage(<?php echo $msg['id']; ?>)">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(0,173,181,0.2);
                                        display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                        👤
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                            <strong style="color: #eee;"><?php echo htmlspecialchars($msg['name']); ?></strong>
                                            <span class="badge <?php echo $msg['is_read'] ? 'badge-read' : 'badge-unread'; ?>">
                                            <?php echo $msg['is_read'] ? 'READ' : 'NEW'; ?>
                                        </span>
                                            <?php if ($msg['username']): ?>
                                                <span class="badge badge-user">User: <?php echo htmlspecialchars($msg['username']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="color: #aaa; font-size: 14px;">
                                            <?php echo htmlspecialchars($msg['email']); ?> •
                                            <?php echo date('d M Y, H:i', strtotime($msg['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <form method="POST" style="display: inline;"
                                          onsubmit="return confirm('Delete this message?');">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" name="delete_message"
                                                style="background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 18px;">
                                            🗑️
                                        </button>
                                    </form>
                                    <span style="color: #aaa; cursor: pointer; font-size: 20px;"
                                          id="toggle-icon-<?php echo $msg['id']; ?>">▼</span>
                                </div>
                            </div>

                            <div class="message-content" id="content-<?php echo $msg['id']; ?>">
                                <div style="padding: 20px 0;">
                                    <div style="margin-bottom: 15px;">
                                        <strong style="color: #00ADB5;">Message:</strong>
                                        <p style="color: #eee; line-height: 1.6; margin-top: 10px; white-space: pre-wrap;">
                                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                        </p>
                                    </div>

                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;
                                        background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; margin-top: 20px;">
                                        <div>
                                            <strong style="color: #aaa; font-size: 12px;">IP Address</strong>
                                            <div style="color: #eee; font-family: monospace;"><?php echo htmlspecialchars($msg['ip_address'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div>
                                            <strong style="color: #aaa; font-size: 12px;">Browser</strong>
                                            <div style="color: #eee; font-size: 13px;"><?php echo htmlspecialchars(substr($msg['user_agent'] ?? 'N/A', 0, 50)); ?>...</div>
                                        </div>
                                        <div>
                                            <strong style="color: #aaa; font-size: 12px;">User ID</strong>
                                            <div style="color: #eee;"><?php echo $msg['user_id'] ? '#' . $msg['user_id'] : 'Guest'; ?></div>
                                        </div>
                                    </div>

                                    <?php if (!$msg['is_read']): ?>
                                        <div style="margin-top: 20px;">
                                            <a href="mark_read.php?id=<?php echo $msg['id']; ?>"
                                               class="action-btn btn-primary" style="padding: 8px 15px; font-size: 14px;">
                                                Mark as Read
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page_num > 1): ?>
                                <a href="?page=<?php echo $page_num - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $filter !== 'all' ? '&filter=' . $filter : ''; ?>"
                                   class="page-link">← Previous</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= min(5, $total_pages); $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $filter !== 'all' ? '&filter=' . $filter : ''; ?>"
                                   class="page-link <?php echo $i == $page_num ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($total_pages > 5): ?>
                                <span class="page-link" style="background: transparent;">...</span>
                                <a href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $filter !== 'all' ? '&filter=' . $filter : ''; ?>"
                                   class="page-link <?php echo $total_pages == $page_num ? 'active' : ''; ?>">
                                    <?php echo $total_pages; ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($page_num < $total_pages): ?>
                                <a href="?page=<?php echo $page_num + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $filter !== 'all' ? '&filter=' . $filter : ''; ?>"
                                   class="page-link">Next →</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Функция для раскрытия/скрытия сообщения
        function toggleMessage(messageId) {
            const content = document.getElementById('content-' + messageId);
            const icon = document.getElementById('toggle-icon-' + messageId);

            if (content.classList.contains('expanded')) {
                content.classList.remove('expanded');
                icon.textContent = '▼';
            } else {
                // Закрыть все другие открытые сообщения
                document.querySelectorAll('.message-content.expanded').forEach(el => {
                    el.classList.remove('expanded');
                    const id = el.id.split('-')[1];
                    document.getElementById('toggle-icon-' + id).textContent = '▼';
                });

                content.classList.add('expanded');
                icon.textContent = '▲';
            }
        }

        // Открыть сообщение из ссылки с якорем
        window.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#message-')) {
                const messageId = hash.replace('#message-', '');
                setTimeout(() => toggleMessage(messageId), 100);
            }
        });
    </script>

    <?php include '../includes/footer.php'; ?>
    </body>
    </html>
<?php
$content = ob_get_clean();
echo $content;
?>