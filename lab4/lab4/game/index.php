<?php
// game/index.php

require_once '../includes/config.php';
require_once '../includes/db_connection.php';
require_once '../includes/game_functions.php';

$page = 'game';
$page_title = 'Minesweeper Game';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    // можно играть без регистрации, но статистика не сохранится
    $isLoggedIn = false;
    $userId = null;
} else {
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
            
            .main.game-page {
                display: block;
                padding: 20px 0;
            }

            .main.game-page .container {
                display: block;
                max-width: 900px;
                margin: 0 auto;
                padding: 20px;
                width: 100%;
            }

            
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

    <main class="main game-page"> 
        <div class="container">

            <h1 style="color: #00ADB5; text-align: center; margin-bottom: 30px;">
                 Minesweeper
            </h1>

            <div class="quick-links">
                <?php if ($isLoggedIn): ?>
                    <a href="my_stats.php" class="quick-link"> My Statistics</a>
                <?php endif; ?>
            </div>

           
            <div class="difficulty-selector" style="margin: 30px 0;">
                <h2 style="color: #eee; margin-bottom: 15px;">Select Difficulty:</h2>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <button class="difficulty-btn active" data-difficulty="beginner">
                        Легкий (9×9, 10 mines)
                    </button>
                    <button class="difficulty-btn" data-difficulty="intermediate">
                        Средний (16×16, 40 mines)
                    </button>
                    <button class="difficulty-btn" data-difficulty="expert">
                        Сложный (30×16, 99 mines)
                    </button>
                </div>
            </div>

            
            <div id="minesweeper-game">
                <div class="game-header">
                    <div class="counter mines-counter">💣 <span id="mines-count">10</span></div>
                    <button id="reset-btn" class="game-btn">😊</button>
                    <div class="counter timer">⏱️ <span id="game-timer">0</span>s</div>
                </div>

                <div id="game-board" class="game-board">
                    <!-- игровое поле js генерирует -->
                </div>

                <div class="controls" style="margin-top: 20px;">
                    <button id="hint-btn" class="game-btn"></button>
                    <button id="pause-btn" class="game-btn">Pause</button>
                    <button id="new-game-btn" class="game-btn">New Game</button>
                </div>
            </div>

            <!-- стата -->
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
        </div>
    </main>

    <script>
        // конфиг
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