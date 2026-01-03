<?php
// includes/config.php

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'ui_designer_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // По умолчанию пустой в XAMPP

// Настройки сайта
define('SITE_NAME', 'Creative UI Designer');
define('SITE_URL', 'http://ui.local');
define('COPYRIGHT_YEAR', date('Y'));

// Пути
define('CSS_PATH', 'styles/');
define('IMG_PATH', 'img/');

// Статус пользователя
session_start();

// Подключение к БД
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Проверка подключения (для отладки)
// $pdo = getDBConnection();
// echo "Connected successfully!";
?>