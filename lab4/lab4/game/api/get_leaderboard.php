<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../includes/config.php';
require_once '../../includes/db_connection.php';
require_once '../../includes/game_functions.php';

$difficulty = $_GET['difficulty'] ?? 'beginner';
$limit = $_GET['limit'] ?? 20;

// Валидация сложности
$validDifficulties = ['beginner', 'intermediate', 'expert'];
if (!in_array($difficulty, $validDifficulties)) {
    $difficulty = 'beginner';
}

try {
    // Получаем таблицу лидеров
    $leaderboard = getLeaderboard($difficulty, $limit);

    // Форматируем время
    foreach ($leaderboard as &$row) {
        if ($row['best_time'] !== null) {
            $minutes = floor($row['best_time'] / 60);
            $seconds = $row['best_time'] % 60;
            $row['best_time_formatted'] = sprintf('%d:%02d', $minutes, $seconds);
        } else {
            $row['best_time_formatted'] = 'N/A';
        }
    }

    echo json_encode([
        'success' => true,
        'difficulty' => $difficulty,
        'leaderboard' => $leaderboard
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>