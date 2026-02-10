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

        // Обновляем таблицу лидеров
        updateLeaderboard($userId, $difficulty, $totalTime, $score, $gameState);

        return ['session_id' => $sessionId, 'score' => $score];

    } catch (Exception $e) {
        error_log("Error saving game session: " . $e->getMessage());
        throw $e;
    }
}



function updateLeaderboard($userId, $difficulty, $time, $score, $result) {
    $db = Database::getInstance();

    // Проверяем, есть ли уже запись
    $existing = $db->fetch(
        "SELECT * FROM game_leaderboard 
        WHERE user_id = ? AND difficulty = ?",
        [$userId, $difficulty]
    );

    $totalGames = 1;
    $gamesWon = ($result === 'won') ? 1 : 0;
    $bestTime = $time;
    $bestScore = $score;

    if ($existing) {
        $totalGames = $existing['total_games'] + 1;
        $gamesWon = $existing['games_won'] + (($result === 'won') ? 1 : 0);
        $bestTime = ($time < $existing['best_time'] || $existing['best_time'] === null) ? $time : $existing['best_time'];
        $bestScore = ($score > $existing['best_score']) ? $score : $existing['best_score'];

        $db->query(
            "UPDATE game_leaderboard 
            SET best_time = ?, best_score = ?, total_games = ?, 
                games_won = ?, win_rate = ROUND((? / ?) * 100, 2),
                last_played = NOW(), updated_at = NOW()
            WHERE user_id = ? AND difficulty = ?",
            [
                $bestTime,
                $bestScore,
                $totalGames,
                $gamesWon,
                $gamesWon,
                $totalGames,
                $userId,
                $difficulty
            ]
        );
    } else {
        $winRate = ($gamesWon / $totalGames) * 100;

        $db->query(
            "INSERT INTO game_leaderboard 
            (user_id, difficulty, best_time, best_score, 
             total_games, games_won, win_rate, last_played) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $userId,
                $difficulty,
                $bestTime,
                $bestScore,
                $totalGames,
                $gamesWon,
                $winRate
            ]
        );
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


function getLeaderboard($difficulty = 'beginner', $limit = 20) {
    $db = Database::getInstance();

    return $db->fetchAll(
        "SELECT gl.*, u.username, u.full_name
        FROM game_leaderboard gl
        JOIN users u ON gl.user_id = u.id
        WHERE gl.difficulty = ?
        ORDER BY gl.best_score DESC, gl.best_time ASC
        LIMIT ?",
        [$difficulty, $limit]
    );
}


function getGlobalGameStats() {
    $db = Database::getInstance();

    return $db->fetchAll(
        "SELECT 
            DATE(created_at) as date,
            COUNT(*) as total_games,
            SUM(CASE WHEN game_state = 'won' THEN 1 ELSE 0 END) as games_won,
            AVG(total_time) as avg_time,
            SUM(score) as total_score
        FROM game_sessions 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC"
    );
}


function getTopPlayers($limit = 10) {
    $db = Database::getInstance();

    return $db->fetchAll(
        "SELECT 
            u.id,
            u.username,
            u.full_name,
            COUNT(gs.id) as total_games,
            SUM(CASE WHEN gs.game_state = 'won' THEN 1 ELSE 0 END) as games_won,
            SUM(gs.score) as total_score,
            MAX(gs.score) as best_score
        FROM users u
        LEFT JOIN game_sessions gs ON u.id = gs.user_id
        GROUP BY u.id
        HAVING total_games > 0
        ORDER BY total_score DESC
        LIMIT ?",
        [$limit]
    );
}
?>