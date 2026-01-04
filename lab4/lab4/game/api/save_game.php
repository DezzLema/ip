<?php
// game/api/save_game.php

// Включите вывод ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Добавьте CORS заголовки
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../includes/config.php';
require_once '../../includes/db_connection.php';
require_once '../../includes/game_functions.php';

// Начинаем сессию если нужно
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Логируем входящий запрос
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Save game request\n", FILE_APPEND);
file_put_contents('debug.log', "Request method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
file_put_contents('debug.log', "Content type: " . $_SERVER['CONTENT_TYPE'] . "\n", FILE_APPEND);

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    file_put_contents('debug.log', "Wrong method\n", FILE_APPEND);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed', 'method' => $_SERVER['REQUEST_METHOD']]);
    exit;
}

// Получаем данные из POST запроса
$input = json_decode(file_get_contents('php://input'), true);

// Логируем полученные данные
file_put_contents('debug.log', "Input data: " . json_encode($input) . "\n", FILE_APPEND);

if (!$input) {
    $rawInput = file_get_contents('php://input');
    file_put_contents('debug.log', "Raw input: " . $rawInput . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data', 'raw' => $rawInput]);
    exit;
}

// Проверяем обязательные поля
$requiredFields = ['userId', 'difficulty', 'time', 'result'];
$missingFields = [];
foreach ($requiredFields as $field) {
    if (!isset($input[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    file_put_contents('debug.log', "Missing fields: " . implode(', ', $missingFields) . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode([
        'error' => 'Missing required fields',
        'missing' => $missingFields,
        'received' => array_keys($input)
    ]);
    exit;
}

// Проверяем авторизацию (сравниваем user_id из сессии и из запроса)
$sessionUserId = $_SESSION['user_id'] ?? null;
$requestUserId = $input['userId'];

file_put_contents('debug.log', "Session user_id: " . $sessionUserId . "\n", FILE_APPEND);
file_put_contents('debug.log', "Request user_id: " . $requestUserId . "\n", FILE_APPEND);

// Сравниваем user_id - они должны совпадать
if ($sessionUserId != $requestUserId) {
    file_put_contents('debug.log', "User ID mismatch\n", FILE_APPEND);
    http_response_code(401);
    echo json_encode([
        'error' => 'Unauthorized: user_id mismatch',
        'session_user' => $sessionUserId,
        'request_user' => $requestUserId
    ]);
    exit;
}

try {
    // Подготавливаем данные
    $gameData = [
        'difficulty' => $input['difficulty'],
        'time' => (int)$input['time'],
        'moves' => $input['moves'] ?? 0,
        'flags' => $input['flags'] ?? 0,
        'result' => $input['result']
    ];

    file_put_contents('debug.log', "Game data prepared: " . json_encode($gameData) . "\n", FILE_APPEND);

    // Сохраняем игровую сессию
    $result = saveGameSession($input['userId'], $gameData);

    file_put_contents('debug.log', "Game saved, result: " . json_encode($result) . "\n", FILE_APPEND);

    echo json_encode([
        'success' => true,
        'session_id' => $result['session_id'],
        'score' => $result['score'],
        'debug' => [
            'input_data' => $input,
            'game_data' => $gameData
        ]
    ]);

} catch (Exception $e) {
    file_put_contents('debug.log', "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents('debug.log', "Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);

    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>