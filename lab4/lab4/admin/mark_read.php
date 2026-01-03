<?php
require_once '../includes/config.php';
require_once '../includes/db_connection.php';

// Проверка авторизации и прав админа
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = Database::getInstance();
$message_id = $_GET['id'] ?? 0;

if ($message_id) {
    $db->query("UPDATE messages SET is_read = 1 WHERE id = ?", [$message_id]);
}

header('Location: messages.php');
exit;
?>