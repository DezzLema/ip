<?php
// game/api/get_stats.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../includes/config.php';
require_once '../../includes/db_connection.php';
require_once '../../includes/game_functions.php';

session_start();

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$difficulty = $_GET['difficulty'] ?? null;

try {
    // Получаем статистику
    $stats = getUserGameStats($userId);

    // Фильтруем по сложности если нужно
    if ($difficulty) {
        $filteredStats = array_filter($stats['by_difficulty'], function($item) use ($difficulty) {
            return $item['difficulty'] === $difficulty;
        });
        $stats['by_difficulty'] = array_values($filteredStats);
    }

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>