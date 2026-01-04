<?php
// game/index.php

require_once '../includes/config.php';
require_once '../includes/db_connection.php';
require_once '../includes/game_functions.php';

$page = 'game';
$page_title = 'Minesweeper Game';

// Проверяем авторизацию (как в works.php)
if (!isset($_SESSION['user_id'])) {
    // Можно играть без регистрации, но статистика не сохранится
    $userMessage = '<div class="info-message" style="background-color: rgba(0,173,181,0.1); color: #00ADB5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <strong>👋 Guest Mode</strong><br>
        You can play, but your scores won\'t be saved. <a href="../login.php">Login</a> or <a href="../register.php">register</a> to save your progress!
    </div>';
    $isLoggedIn = false;
    $userId = null;
} else {
    $userMessage = '<div class="info-message" style="background-color: rgba(40,167,69,0.1); color: #28a745; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        Welcome back, <strong>' . htmlspecialchars($_SESSION['user_name']) . '</strong>! 
        Your games will be saved to your profile.
    </div>';
    $isLoggedIn = true;
    $userId = $_SESSION['user_id'];
}

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
        <link rel="stylesheet" href="minesweeper.css">
        <style>
            /* ПЕРЕОПРЕДЕЛЯЕМ стили для игровой страницы */
            .main.game-page {
                display: block; /* Убираем flex из стилей style.css */
                padding: 20px 0; /* Уменьшаем паддинг для игровой страницы */
            }

            .main.game-page .container {
                display: block; /* Убираем flex из стилей style.css */
                max-width: 900px;
                margin: 0 auto;
                padding: 20px;
                width: 100%;
            }

            /* Стили для быстрых ссылок */
            .quick-links {
                display: flex;
                gap: 15px;
                margin: 20px 0;
                flex-wrap: wrap;
            }

            .quick-link {
                padding: 10px 20px;
                background: rgba(0, 173, 181, 0.1);
                color: #00ADB5;
                border-radius: 8px;
                text-decoration: none;
                border: 1px solid rgba(0, 173, 181, 0.3);
                transition: all 0.3s;
            }

            .quick-link:hover {
                background: rgba(0, 173, 181, 0.2);
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
    <?php include '../includes/header.php'; ?>

    <main class="main game-page"> <!-- Добавляем специальный класс для игровой страницы -->
        <div class="container">
            <?php echo $userMessage; ?>

            <h1 style="color: #00ADB5; text-align: center; margin-bottom: 30px;">
                🎮 Minesweeper
            </h1>

            <div class="quick-links">
                <a href="#game" class="quick-link">▶️ Play Now</a>
                <?php if ($isLoggedIn): ?>
                    <a href="my_stats.php" class="quick-link">📊 My Statistics</a>
                <?php endif; ?>
                <a href="leaderboard.php" class="quick-link">🏆 Leaderboard</a>
                <a href="#how-to-play" class="quick-link">❓ How to Play</a>
            </div>

            <!-- Выбор сложности -->
            <div class="difficulty-selector" style="margin: 30px 0;">
                <h2 style="color: #eee; margin-bottom: 15px;">Select Difficulty:</h2>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <button class="difficulty-btn active" data-difficulty="beginner">
                        🟢 Beginner (9×9, 10 mines)
                    </button>
                    <button class="difficulty-btn" data-difficulty="intermediate">
                        🟡 Intermediate (16×16, 40 mines)
                    </button>
                    <button class="difficulty-btn" data-difficulty="expert">
                        🔴 Expert (30×16, 99 mines)
                    </button>
                </div>
            </div>

            <!-- Игровое поле -->
            <div id="minesweeper-game">
                <div class="game-header">
                    <div class="counter mines-counter">💣 <span id="mines-count">10</span></div>
                    <button id="reset-btn" class="game-btn">😊</button>
                    <div class="counter timer">⏱️ <span id="game-timer">0</span>s</div>
                </div>

                <div id="game-board" class="game-board">
                    <!-- Игровое поле будет сгенерировано JavaScript -->
                </div>

                <div class="controls" style="margin-top: 20px;">
                    <button id="hint-btn" class="game-btn">💡 Hint (3 left)</button>
                    <button id="pause-btn" class="game-btn">⏸️ Pause</button>
                    <button id="new-game-btn" class="game-btn">🔄 New Game</button>
                </div>
            </div>

            <!-- Статистика текущей игры -->
            <div id="current-game-stats" style="margin-top: 30px; padding: 20px; background: rgba(57,62,70,0.6); border-radius: 10px;">
                <h3 style="color: #00ADB5; margin-bottom: 15px;">Current Game Stats</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                    <div>
                        <div style="color: #aaa; font-size: 14px;">Moves</div>
                        <div id="moves-count" style="font-size: 24px; font-weight: bold;">0</div>
                    </div>
                    <div>
                        <div style="color: #aaa; font-size: 14px;">Flags</div>
                        <div id="flags-count" style="font-size: 24px; font-weight: bold;">0</div>
                    </div>
                    <div>
                        <div style="color: #aaa; font-size: 14px;">Score</div>
                        <div id="current-score" style="font-size: 24px; font-weight: bold; color: #00ADB5;">0</div>
                    </div>
                    <div>
                        <div style="color: #aaa; font-size: 14px;">Status</div>
                        <div id="game-status" style="font-size: 24px; font-weight: bold; color: #28a745;">Playing</div>
                    </div>
                </div>
            </div>

            <!-- Инструкция -->
            <div id="how-to-play" style="margin-top: 50px; padding: 30px; background: rgba(57,62,70,0.6); border-radius: 15px;">
                <h2 style="color: #00ADB5; margin-bottom: 20px;">❓ How to Play Minesweeper</h2>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div>
                        <h3 style="color: #eee; margin-bottom: 10px;">🎯 Objective</h3>
                        <p style="color: #aaa;">Clear the board without detonating any mines. Use numbers to deduce mine locations.</p>
                    </div>

                    <div>
                        <h3 style="color: #eee; margin-bottom: 10px;">🖱️ Controls</h3>
                        <ul style="color: #aaa; list-style: none; padding: 0;">
                            <li>✅ <strong>Left click</strong>: Reveal a cell</li>
                            <li>🚩 <strong>Right click</strong>: Place/remove flag</li>
                            <li>🔄 <strong>Double click</strong>: Quick reveal (if flagged correctly)</li>
                        </ul>
                    </div>

                    <div>
                        <h3 style="color: #eee; margin-bottom: 10px;">📊 Scoring</h3>
                        <ul style="color: #aaa; list-style: none; padding: 0;">
                            <li>⭐ Base points based on difficulty</li>
                            <li>⚡ Bonus for faster completion</li>
                            <li>🎯 Penalty for using hints</li>
                            <li>🏆 Only winning games get scores</li>
                        </ul>
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 15px; background: rgba(0,173,181,0.1); border-radius: 10px;">
                    <h4 style="color: #00ADB5; margin-bottom: 10px;">Number Meanings:</h4>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <span style="padding: 5px 10px; background: #393E46; border-radius: 5px;">1 = 💣 Nearby</span>
                        <span style="padding: 5px 10px; background: #393E46; border-radius: 5px;">2 = 💣💣 Nearby</span>
                        <span style="padding: 5px 10px; background: #393E46; border-radius: 5px;">... and so on</span>
                        <span style="padding: 5px 10px; background: #393E46; border-radius: 5px;">Empty = Safe area</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Конфигурация игры
        const gameConfig = {
            isLoggedIn: <?php echo $isLoggedIn ? 'true' : 'false'; ?>,
            userId: <?php echo $userId ?? 'null'; ?>,
            apiBaseUrl: 'api/'
        };
    </script>

    <script src="minesweeper.js"></script>

    <?php include '../includes/footer.php'; ?>
    </body>
    </html>

<?php
$content = ob_get_clean();
echo $content;
?>