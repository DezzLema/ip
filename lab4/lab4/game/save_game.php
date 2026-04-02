<?php

require_once '../includes/config.php';
require_once '../includes/db_connection.php';
require_once '../includes/game_functions.php';

session_start();

// Принимаем POST запрос
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из POST
    $userId = $_POST['userId'] ?? $_SESSION['user_id'] ?? null;
    $difficulty = $_POST['difficulty'] ?? 'beginner';
    $time = (int)($_POST['time'] ?? 0);
    $moves = (int)($_POST['moves'] ?? 0);
    $flags = (int)($_POST['flags'] ?? 0);
    $result = $_POST['result'] ?? 'lost';
    
    // Проверяем авторизацию
    if (!$userId || $userId != $_SESSION['user_id']) {
        echo "Error: Unauthorized";
        exit;
    }
    
    // Подготавливаем данные
    $gameData = [
        'difficulty' => $difficulty,
        'time' => $time,
        'moves' => $moves,
        'flags' => $flags,
        'result' => $result
    ];
    
    // Сохраняем в БД
    $result = saveGameSession($userId, $gameData);
    
    // Возвращаем результат
    echo json_encode([
        'success' => true,
        'score' => $result['score']
    ]);
} else {
    echo "Method not allowed";
}
?>