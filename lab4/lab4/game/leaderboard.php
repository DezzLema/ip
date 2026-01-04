<?php
// game/leaderboard.php

require_once '../includes/config.php';
require_once '../includes/db_connection.php';
require_once '../includes/game_functions.php';

$page = 'game';
$page_title = 'Game Leaderboard';

// Получаем параметры фильтрации
$difficulty = $_GET['difficulty'] ?? 'beginner';
$limit = $_GET['limit'] ?? 50;

// Валидация
$validDifficulties = ['beginner', 'intermediate', 'expert'];
if (!in_array($difficulty, $validDifficulties)) {
    $difficulty = 'beginner';
}

// Получаем данные лидерборда
$leaderboard = getLeaderboard($difficulty, $limit);

ob_start();
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
        <link rel="stylesheet" href="../styles/normalize.css">
        <link rel="stylesheet" href="../styles/style.css">
        <style>
            .leaderboard-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }

            .leaderboard-header {
                text-align: center;
                margin-bottom: 40px;
                padding: 30px;
                background: linear-gradient(135deg, rgba(0,173,181,0.1) 0%, rgba(34,40,49,0.8) 100%);
                border-radius: 15px;
                border: 2px solid #00ADB5;
            }

            .difficulty-filter {
                display: flex;
                justify-content: center;
                gap: 15px;
                margin-bottom: 30px;
                flex-wrap: wrap;
            }

            .filter-btn {
                padding: 12px 25px;
                background: rgba(57, 62, 70, 0.6);
                color: #eee;
                border: 2px solid rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .filter-btn.active {
                background: rgba(0, 173, 181, 0.3);
                color: #00ADB5;
                border-color: #00ADB5;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 173, 181, 0.2);
            }

            .filter-btn.beginner.active {
                background: rgba(40, 167, 69, 0.3);
                border-color: #28a745;
                color: #28a745;
            }

            .filter-btn.intermediate.active {
                background: rgba(255, 193, 7, 0.3);
                border-color: #ffc107;
                color: #ffc107;
            }

            .filter-btn.expert.active {
                background: rgba(255, 107, 107, 0.3);
                border-color: #ff6b6b;
                color: #ff6b6b;
            }

            .leaderboard-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 40px;
                background: rgba(57, 62, 70, 0.6);
                border-radius: 12px;
                overflow: hidden;
            }

            .leaderboard-table th {
                background: rgba(0, 173, 181, 0.2);
                color: #00ADB5;
                padding: 20px 15px;
                font-weight: 600;
                text-align: left;
                border-bottom: 2px solid rgba(0, 173, 181, 0.3);
            }

            .leaderboard-table td {
                padding: 18px 15px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                color: #eee;
            }

            .leaderboard-table tr:hover {
                background: rgba(255, 255, 255, 0.05);
            }

            .leaderboard-table tr:nth-child(1) {
                background: rgba(255, 215, 0, 0.1);
            }

            .leaderboard-table tr:nth-child(2) {
                background: rgba(192, 192, 192, 0.1);
            }

            .leaderboard-table tr:nth-child(3) {
                background: rgba(205, 127, 50, 0.1);
            }

            .rank {
                font-weight: bold;
                font-size: 18px;
                text-align: center;
                width: 70px;
            }

            .rank-1 { color: #ffd700; }
            .rank-2 { color: #c0c0c0; }
            .rank-3 { color: #cd7f32; }

            .player-info {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .player-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: #00ADB5;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                color: white;
            }

            .player-name {
                font-weight: 600;
            }

            .player-username {
                color: #aaa;
                font-size: 14px;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-top: 40px;
            }

            .stat-box {
                background: rgba(57, 62, 70, 0.6);
                padding: 25px;
                border-radius: 12px;
                text-align: center;
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .stat-number {
                font-size: 36px;
                font-weight: bold;
                color: #00ADB5;
                margin-bottom: 10px;
            }

            .stat-label {
                color: #aaa;
                font-size: 14px;
            }

            .empty-state {
                text-align: center;
                padding: 60px 20px;
                color: #888;
            }

            .empty-icon {
                font-size: 64px;
                margin-bottom: 20px;
                opacity: 0.5;
            }

            .medal {
                font-size: 24px;
                margin-right: 10px;
            }

            .time-cell {
                font-family: 'Courier New', monospace;
                font-weight: bold;
            }

            @media (max-width: 768px) {
                .leaderboard-table {
                    font-size: 14px;
                }

                .leaderboard-table th,
                .leaderboard-table td {
                    padding: 12px 10px;
                }

                .player-info {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 5px;
                }

                .filter-btn {
                    padding: 10px 15px;
                    font-size: 14px;
                }
            }
        </style>
    </head>
    <body>
    <?php include '../includes/header.php'; ?>

    <main class="main">
        <div class="container leaderboard-container">
            <div class="leaderboard-header">
                <h1 style="color: #00ADB5; margin-bottom: 10px; font-size: 36px;">
                    🏆 Minesweeper Leaderboard
                </h1>
                <p style="color: #aaa; max-width: 600px; margin: 0 auto;">
                    Compete with other players and climb to the top! Only the best scores are saved.
                </p>
                <div style="margin-top: 20px;">
                    <a href="index.php" class="game-btn" style="display: inline-block; text-decoration: none;">🎮 Play Now</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="my_stats.php" class="game-btn" style="display: inline-block; text-decoration: none; background: #393E46;">📊 My Stats</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Фильтр по сложности -->
            <div class="difficulty-filter">
                <button class="filter-btn beginner <?php echo $difficulty == 'beginner' ? 'active' : ''; ?>"
                        onclick="window.location.href='?difficulty=beginner'">
                    🟢 Beginner
                </button>
                <button class="filter-btn intermediate <?php echo $difficulty == 'intermediate' ? 'active' : ''; ?>"
                        onclick="window.location.href='?difficulty=intermediate'">
                    🟡 Intermediate
                </button>
                <button class="filter-btn expert <?php echo $difficulty == 'expert' ? 'active' : ''; ?>"
                        onclick="window.location.href='?difficulty=expert'">
                    🔴 Expert
                </button>
            </div>

            <!-- Таблица лидеров -->
            <?php if (empty($leaderboard)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🏆</div>
                    <h3 style="color: #aaa; margin-bottom: 15px;">No Scores Yet</h3>
                    <p>Be the first to set a record!</p>
                    <a href="index.php" class="game-btn" style="display: inline-block; margin-top: 20px; text-decoration: none;">🎮 Play First Game</a>
                </div>
            <?php else: ?>
                <table class="leaderboard-table">
                    <thead>
                    <tr>
                        <th class="rank">Rank</th>
                        <th>Player</th>
                        <th>Best Score</th>
                        <th>Best Time</th>
                        <th>Games Played</th>
                        <th>Win Rate</th>
                        <th>Last Played</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($leaderboard as $index => $player): ?>
                        <?php
                        $rank = $index + 1;
                        $medal = $rank <= 3 ? ['🥇', '🥈', '🥉'][$rank - 1] : '';

                        // Форматируем время
                        $bestTimeFormatted = 'N/A';
                        if ($player['best_time'] !== null) {
                            $minutes = floor($player['best_time'] / 60);
                            $seconds = $player['best_time'] % 60;
                            $bestTimeFormatted = sprintf('%d:%02d', $minutes, $seconds);
                        }

                        // Форматируем дату
                        $lastPlayed = $player['last_played']
                            ? date('M d, Y', strtotime($player['last_played']))
                            : 'N/A';

                        // Аватар (первые буквы имени)
                        $initials = strtoupper(substr($player['username'], 0, 2));
                        $colors = ['#00ADB5', '#28a745', '#ffc107', '#ff6b6b', '#6f42c1'];
                        $colorIndex = $player['user_id'] % count($colors);
                        ?>
                        <tr>
                            <td class="rank rank-<?php echo $rank; ?>">
                                <span class="medal"><?php echo $medal; ?></span>
                                <?php echo $rank; ?>
                            </td>
                            <td>
                                <div class="player-info">
                                    <div class="player-avatar" style="background: <?php echo $colors[$colorIndex]; ?>;">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div>
                                        <div class="player-name">
                                            <?php echo htmlspecialchars($player['full_name'] ?: $player['username']); ?>
                                        </div>
                                        <div class="player-username">
                                            @<?php echo htmlspecialchars($player['username']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size: 20px; font-weight: bold; color: #00ADB5;">
                                <?php echo number_format($player['best_score']); ?>
                            </td>
                            <td class="time-cell">
                                <?php echo $bestTimeFormatted; ?>
                            </td>
                            <td><?php echo $player['total_games']; ?></td>
                            <td>
                                <span style="color:
                                    <?php echo $player['win_rate'] >= 50 ? '#28a745' :
                                    ($player['win_rate'] >= 30 ? '#ffc107' : '#ff6b6b'); ?>">
                                    <?php echo $player['win_rate']; ?>%
                                </span>
                            </td>
                            <td style="color: #aaa; font-size: 14px;">
                                <?php echo $lastPlayed; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Статистика лидерборда -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($leaderboard); ?></div>
                        <div class="stat-label">Players on Leaderboard</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number">
                            <?php
                            $topScore = !empty($leaderboard) ? max(array_column($leaderboard, 'best_score')) : 0;
                            echo number_format($topScore);
                            ?>
                        </div>
                        <div class="stat-label">Top Score</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number">
                            <?php
                            $bestTime = null;
                            foreach ($leaderboard as $player) {
                                if ($player['best_time'] !== null && ($bestTime === null || $player['best_time'] < $bestTime)) {
                                    $bestTime = $player['best_time'];
                                }
                            }
                            if ($bestTime !== null) {
                                echo floor($bestTime / 60) . ':' . sprintf('%02d', $bestTime % 60);
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </div>
                        <div class="stat-label">Best Time</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number">
                            <?php
                            $avgWinRate = !empty($leaderboard)
                                ? round(array_sum(array_column($leaderboard, 'win_rate')) / count($leaderboard), 1)
                                : 0;
                            echo $avgWinRate . '%';
                            ?>
                        </div>
                        <div class="stat-label">Average Win Rate</div>
                    </div>
                </div>

                <!-- Информация -->
                <div style="margin-top: 40px; padding: 20px; background: rgba(0,173,181,0.1); border-radius: 12px;">
                    <h3 style="color: #00ADB5; margin-bottom: 15px;">ℹ️ How the Leaderboard Works</h3>
                    <div style="color: #aaa; line-height: 1.6;">
                        <p><strong>Scoring System:</strong> Only winning games count. Scores are calculated based on time, difficulty, and efficiency.</p>
                        <p><strong>Ranking:</strong> Players are ranked by their best score on each difficulty level.</p>
                        <p><strong>Requirements:</strong> You must be logged in to save your scores to the leaderboard.</p>
                        <p><strong>Updates:</strong> The leaderboard updates in real-time after each completed game.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    </body>
    </html>

<?php
$content = ob_get_clean();
echo $content;
?>