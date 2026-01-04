<?php
// game/api/save_game.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/config.php';
require_once '../../includes/db_connection.php';
require_once '../../includes/game_functions.php';

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Получаем данные из POST запроса
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Проверяем обязательные поля
if (!isset($input['userId'], $input['difficulty'], $input['time'], $input['result'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Проверяем авторизацию (опционально)
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $input['userId']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Сохраняем игровую сессию
    $result = saveGameSession($input['userId'], [
        'difficulty' => $input['difficulty'],
        'time' => $input['time'],
        'moves' => $input['moves'] ?? 0,
        'flags' => $input['flags'] ?? 0,
        'result' => $input['result']
    ]);

    echo json_encode([
        'success' => true,
        'session_id' => $result['session_id'],
        'score' => $result['score']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>