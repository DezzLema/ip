<?php
require_once 'db_connection.php';


function saveGameSession($userId, $gameData) {
    // Логируем вызов функции
    error_log("saveGameSession called: userId=$userId, data=" . json_encode($gameData));

    $db = Database::getInstance();

    // Подготавливаем данные
    $difficulty = $gameData['difficulty'] ?? 'beginner';
    $gameState = $gameData['result'] ?? 'lost';
    $totalTime = (int)($gameData['time'] ?? 0);
    $score = calculateScore($totalTime, $difficulty, $gameData['moves'] ?? 0, $gameState);

    // Логируем рассчитанные данные
    error_log("Calculated score: $score, time: $totalTime, state: $gameState");

    try {
        // Сохраняем игровую сессию
        $db->query(
            "INSERT INTO game_sessions 
            (user_id, difficulty, game_state, total_time, moves_count, flags_used, score) 
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $difficulty,
                $gameState,
                $totalTime,
                $gameData['moves'] ?? 0,
                $gameData['flags'] ?? 0,
                $score
            ]
        );

        $sessionId = $db->lastInsertId();
        error_log("Game session saved with ID: $sessionId");

        return ['session_id' => $sessionId, 'score' => $score];

    } catch (Exception $e) {
        error_log("Error saving game session: " . $e->getMessage());
        throw $e;
    }
}



function calculateScore($time, $difficulty, $moves, $result) {
    if ($result !== 'won') {
        return 0;
    }

    // Базовые очки в зависимости от сложности
    $baseScore = [
        'beginner' => 1000,
        'intermediate' => 2500,
        'expert' => 5000
    ];

    // Бонус за скорость (меньше времени = больше бонус)
    $timeBonus = max(0, 1000 - $time);

    // Бонус за эффективность (меньше ходов = больше бонус)
    $efficiencyBonus = max(0, 500 - ($moves * 2));

    // Штраф за использование флагов
    $flagPenalty = 0; // Можно добавить логику

    $totalScore = $baseScore[$difficulty] + $timeBonus + $efficiencyBonus - $flagPenalty;

    return max(100, $totalScore); // Минимум 100 очков
}


function getUserGameStats($userId) {
    $db = Database::getInstance();

    $stats = $db->fetch(
        "SELECT 
            COUNT(*) as total_games,
            SUM(CASE WHEN game_state = 'won' THEN 1 ELSE 0 END) as games_won,
            SUM(CASE WHEN game_state = 'lost' THEN 1 ELSE 0 END) as games_lost,
            AVG(total_time) as avg_time,
            MAX(score) as best_score,
            MIN(total_time) as best_time
        FROM game_sessions 
        WHERE user_id = ?",
        [$userId]
    );

    // Получаем статистику по сложностям
    $byDifficulty = $db->fetchAll(
        "SELECT difficulty, 
                COUNT(*) as total,
                SUM(CASE WHEN game_state = 'won' THEN 1 ELSE 0 END) as won,
                MIN(total_time) as best_time,
                MAX(score) as best_score
         FROM game_sessions 
         WHERE user_id = ?
         GROUP BY difficulty",
        [$userId]
    );

    // Получаем последние игры
    $recentGames = $db->fetchAll(
        "SELECT * FROM game_sessions 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10",
        [$userId]
    );

    return [
        'overall' => $stats,
        'by_difficulty' => $byDifficulty,
        'recent_games' => $recentGames
    ];
}
?>