<?php
// game/my_stats.php

require_once '../includes/config.php';
require_once '../includes/db_connection.php';
require_once '../includes/game_functions.php';

$page = 'game';
$page_title = 'My Game Statistics';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?message=Please login to view your statistics');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Получаем статистику
$stats = getUserGameStats($userId);

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
        <link rel="stylesheet" href="../styles/my_stats.css">
    </head>
    <body>
    <?php include '../includes/header.php'; ?>

    <main class="main">
        <div class="container stats-container">


            <div class="stat-card">
                <h2 style="color: #00ADB5; margin-bottom: 20px;"> Overall Statistics</h2>
                <div class="stats-grid">
                    <div>
                        <div class="stat-value"><?php echo $stats['overall']['total_games'] ?? 0; ?></div>
                        <div class="stat-label">Total Games</div>
                    </div>
                    <div>
                        <div class="stat-value"><?php echo $stats['overall']['games_won'] ?? 0; ?></div>
                        <div class="stat-label">Games Won</div>
                    </div>
                    <div>
                        <?php
                        $totalGames = $stats['overall']['total_games'] ?? 0;
                        $gamesWon = $stats['overall']['games_won'] ?? 0;
                        $winRate = $totalGames > 0 ? round(($gamesWon / $totalGames) * 100, 1) : 0;
                        ?>
                        <div class="stat-value"><?php echo $winRate; ?>%</div>
                        <div class="stat-label">Win Rate</div>
                    </div>
                    <div>
                        <div class="stat-value"><?php echo $stats['overall']['best_score'] ?? 0; ?></div>
                        <div class="stat-label">Best Score</div>
                    </div>
                    <div>
                        <?php
                        $bestTime = $stats['overall']['best_time'] ?? 0;
                        $timeFormatted = $bestTime ? floor($bestTime / 60) . ':' . sprintf('%02d', $bestTime % 60) : 'N/A';
                        ?>
                        <div class="stat-value"><?php echo $timeFormatted; ?></div>
                        <div class="stat-label">Best Time</div>
                    </div>
                    <div>
                        <div class="stat-value"><?php echo round($stats['overall']['avg_time'] ?? 0, 1); ?>s</div>
                        <div class="stat-label">Average Time</div>
                    </div>
                </div>
            </div>

            <!-- Статистика по сложностям -->
            <div class="stat-card" style="margin-top: 30px;">
                <h2 style="color: #00ADB5; margin-bottom: 20px;"> Statistics by Difficulty</h2>

                <?php if (empty($stats['by_difficulty'])): ?>
                    <div class="empty-state">
                        <div class="empty-icon"></div>
                        <p>No games played yet. Start playing to see your statistics!</p>
                        <a href="index.php" class="game-btn" style="display: inline-block; margin-top: 20px; text-decoration: none;">🎮 Play First Game</a>
                    </div>
                <?php else: ?>
                    <div class="difficulty-tabs" id="difficultyTabs">
                        <button class="tab-btn active" data-difficulty="all">All Difficulties</button>
                        <?php foreach ($stats['by_difficulty'] as $diff): ?>
                            <button class="tab-btn" data-difficulty="<?php echo $diff['difficulty']; ?>">
                                <?php echo ucfirst($diff['difficulty']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <table id="difficultyTable">
                        <thead>
                        <tr>
                            <th>Difficulty</th>
                            <th>Games</th>
                            <th>Won</th>
                            <th>Win Rate</th>
                            <th>Best Time</th>
                            <th>Best Score</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($stats['by_difficulty'] as $diff): ?>
                            <?php
                            $winRate = $diff['total'] > 0 ? round(($diff['won'] / $diff['total']) * 100, 1) : 0;
                            $bestTime = $diff['best_time'] ? floor($diff['best_time'] / 60) . ':' . sprintf('%02d', $diff['best_time'] % 60) : 'N/A';
                            ?>
                            <tr data-difficulty="<?php echo $diff['difficulty']; ?>">
                                <td>
                                    <span style="font-weight: 600; color:
                                        <?php echo $diff['difficulty'] == 'beginner' ? '#28a745' :
                                        ($diff['difficulty'] == 'intermediate' ? '#ffc107' : '#ff6b6b'); ?>">
                                        <?php echo ucfirst($diff['difficulty']); ?>
                                    </span>
                                </td>
                                <td><?php echo $diff['total']; ?></td>
                                <td><?php echo $diff['won']; ?></td>
                                <td><?php echo $winRate; ?>%</td>
                                <td><?php echo $bestTime; ?></td>
                                <td><?php echo $diff['best_score'] ?? 0; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Последние игры -->
            <div class="stat-card" style="margin-top: 30px;">
                <h2 style="color: #00ADB5; margin-bottom: 20px;"> Recent Games</h2>

                <?php if (empty($stats['recent_games'])): ?>
                    <div class="empty-state">
                        <div class="empty-icon"></div>
                        <p>No games played yet.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Difficulty</th>
                            <th>Time</th>
                            <th>Moves</th>
                            <th>Score</th>
                            <th>Result</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($stats['recent_games'] as $game): ?>
                            <?php
                            $timeFormatted = floor($game['total_time'] / 60) . ':' . sprintf('%02d', $game['total_time'] % 60);
                            $dateFormatted = date('M d, H:i', strtotime($game['created_at']));
                            ?>
                            <tr>
                                <td><?php echo $dateFormatted; ?></td>
                                <td>
                                    <span style="color:
                                        <?php echo $game['difficulty'] == 'beginner' ? '#28a745' :
                                        ($game['difficulty'] == 'intermediate' ? '#ffc107' : '#ff6b6b'); ?>">
                                        <?php echo ucfirst($game['difficulty']); ?>
                                    </span>
                                </td>
                                <td><?php echo $timeFormatted; ?></td>
                                <td><?php echo $game['moves_count']; ?></td>
                                <td><?php echo $game['score']; ?></td>
                                <td>
                                    <span class="badge <?php echo $game['game_state'] == 'won' ? 'badge-won' : 'badge-lost'; ?>">
                                        <?php echo ucfirst($game['game_state']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Табы для фильтрации по сложности
        document.addEventListener('DOMContentLoaded', function() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tableRows = document.querySelectorAll('#difficultyTable tbody tr');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Убираем активный класс у всех
                    tabBtns.forEach(b => b.classList.remove('active'));
                    // Добавляем активный класс текущей
                    this.classList.add('active');

                    const difficulty = this.dataset.difficulty;

                    // Фильтруем строки таблицы
                    tableRows.forEach(row => {
                        if (difficulty === 'all' || row.dataset.difficulty === difficulty) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>

    <?php include '../includes/footer.php'; ?>
    </body>
    </html>

<?php
$content = ob_get_clean();
echo $content;
?>