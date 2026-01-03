<?php
require_once '../includes/config.php';
require_once '../includes/db_connection.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$page = 'admin';
$page_title = 'Admin - Messages';
$db = Database::getInstance();

// Получаем сообщения
$messages = $db->fetchAll(
    "SELECT m.*, u.username 
     FROM messages m 
     LEFT JOIN users u ON m.user_id = u.id 
     ORDER BY m.created_at DESC"
);

ob_start();
?>
    <main class="main contact-main">
        <div class="container">
            <h1 class="contact-title">Messages (<?php echo count($messages); ?>)</h1>

            <div style="margin-bottom: 20px;">
                <a href="index.php" class="contact-btn">Dashboard</a>
                <a href="../logout.php" class="contact-btn" style="background-color: #ff6b6b;">Logout</a>
            </div>

            <?php if (empty($messages)): ?>
                <p>No messages yet.</p>
            <?php else: ?>
                <div class="messages-list">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-item" style="background: rgba(57,62,70,0.6); padding: 20px; margin-bottom: 15px; border-radius: 10px; border-left: 4px solid <?php echo $msg['is_read'] ? '#00ADB5' : '#ff6b6b'; ?>">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <div>
                                    <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
                                    <small><?php echo htmlspecialchars($msg['email']); ?></small>
                                    <?php if ($msg['username']): ?>
                                        <span style="background: #00ADB5; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">
                            User: <?php echo htmlspecialchars($msg['username']); ?>
                        </span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <small><?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?></small>
                                    <?php if (!$msg['is_read']): ?>
                                        <a href="mark_read.php?id=<?php echo $msg['id']; ?>" style="margin-left: 10px; color: #00ADB5;">Mark as read</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
<?php
$content = ob_get_clean();
include '../includes/header.php';
echo $content;
include '../includes/footer.php';
?>