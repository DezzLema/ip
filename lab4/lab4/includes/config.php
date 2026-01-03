<?php
// Настройки сайта
define('SITE_NAME', 'Creative UI Designer');
define('SITE_URL', 'http://localhost/your-project');
define('COPYRIGHT_YEAR', date('Y'));

// Пути
define('CSS_PATH', 'styles/');
define('IMG_PATH', 'img/');

// Статус пользователя (заглушка для демо)
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = null;
    $_SESSION['user_name'] = null;
}
?>