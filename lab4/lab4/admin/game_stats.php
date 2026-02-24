<?php

require_once '../includes/config.php';
require_once '../includes/db_connection.php';
require_once '../includes/game_functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin');
    exit;
}

if (!isAdmin()) {
    die('<div style="text-align: center; padding: 50px; font-family: Arial;">
        <h1 style="color: #ff6b6b;">Access Denied</h1>
        <p>Admin privileges required to access this page.</p>
        <a href="../index.php" style="color: #00ADB5;">Return to Home</a>
    </div>');
}

$db = Database::getInstance();
$page = 'admin';
$page_title = 'Admin - Game Statistics';

// Получаем глобальную статистику
$globalStats = getGlobalGameStats();
$topPlayers = getTopPlayers(10);
$recentGames = $db->fetchAll("
    SELECT gs.*, u.username 
    FROM game_sessions gs 
    LEFT JOIN users u ON gs.user_id = u.id 
    ORDER BY gs.created_at DESC 
    LIMIT 20
");

// Статистика за сегодня
$todayStats = $db->fetch("
    SELECT 
        COUNT(*) as games_today,
        SUM(CASE WHEN game_state = 'won' THEN 1 ELSE 0 END) as wins_today,
        AVG(total_time) as avg_time_today,
        COUNT(DISTINCT user_id) as active_players_today
    FROM game_sessions 
    WHERE DATE(created_at) = CURDATE()
");

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
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            .admin-container {
                max-width: 1400px;
                margin: 0 auto;
                padding: 20px;
            }

            .admin-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
                padding: 25px;
                background: rgba(34,40,49,0.9);
                border-radius: 12px;
                border: 2px solid #00ADB5;
            }

            .admin-header h1 {
                color: #00ADB5;
                margin: 0;
            }

            .admin-actions {
                display: flex;
                gap: 15px;
            }

            .admin-btn {
                padding: 10px 20px;
                background: #393E46;
                color: #eee;
                text-decoration: none;
                border-radius: 6px;
                transition: all 0.3s;
            }

            .admin-btn:hover {
                background: #454b55;
            }

            .stats-overview {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .stat-card {
                background: rgba(57, 62, 70, 0.6);
                padding: 25px;
                border-radius: 12px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                position: relative;
                overflow: hidden;
            }

            .stat-icon {
                position: absolute;
                top: 20px;
                right: 20px;
                font-size: 40px;
                opacity: 0.2;
            }

            .stat-number {
                font-size: 36px;
                font-weight: bold;
                margin-bottom: 10px;
                color: #00ADB5;
            }

            .stat-label {
                color: #aaa;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .dashboard-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
                gap: 30px;
                margin-bottom: 40px;
            }

            .dashboard-card {
                background: rgba(57, 62, 70, 0.6);
                padding: 25px;
                border-radius: 12px;
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .card-title {
                color: #00ADB5;
                font-size: 18px;
                font-weight: 600;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }

            th, td {
                padding: 12px 15px;
                text-align: left;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            th {
                background: rgba(0, 173, 181, 0.2);
                color: #00ADB5;
                font-weight: 600;
            }

            tr:hover {
                background: rgba(255, 255, 255, 0.05);
            }

            .badge {
                padding: 3px 10px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
            }

            .badge-success {
                background: rgba(40, 167, 69, 0.2);
                color: #28a745;
            }

            .badge-danger {
                background: rgba(255, 107, 107, 0.2);
                color: #ff6b6b;
            }

            .chart-container {
                height: 300px;
                margin-top: 20px;
            }

            .filters {
                display: flex;
                gap: 15px;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }

            .filter-select {
                padding: 8px 15px;
                background: rgba(57, 62, 70, 0.8);
                color: #eee;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 6px;
                min-width: 150px;
            }

            .export-btn {
                padding: 8px 20px;
                background: #28a745;
                color: white;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .pagination {
                display: flex;
                justify-content: center;
                gap: 10px;
                margin-top: 20px;
            }

            .page-btn {
                padding: 8px 15px;
                background: rgba(57, 62, 70, 0.6);
                color: #eee;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 6px;
                cursor: pointer;
            }

            .page-btn.active {
                background: #00ADB5;
                color: white;
            }

            @media (max-width: 768px) {
                .dashboard-grid {
                    grid-template-columns: 1fr;
                }

                .admin-header {
                    flex-direction: column;
                    gap: 20px;
                    text-align: center;
                }
            }
        </style>
    </head>
    <body>
    <?php include '../includes/header.php'; ?>

    <main class="main">
        <div class="admin-container">
            <div class="admin-header">
                <div>
                    <h1>🎮 Game Statistics Admin</h1>
                    <p style="color: #aaa; margin-top: 10px;">Monitor game activity and player statistics</p>
                </div>

                <div class="admin-actions">
                    <a href="index.php" class="admin-btn">← Dashboard</a>
                    <a href="users.php" class="admin-btn">👥 Users</a>
                    <a href="../game/leaderboard.php" target="_blank" class="admin-btn">🏆 Leaderboard</a>
                </div>
            </div>

            <div class="stats-overview">
                <div class="stat-card">
                    <div class="stat-icon">🎮</div>
                    <div class="stat-number"><?php echo $todayStats['games_today'] ?? 0; ?></div>
                    <div class="stat-label">Games Today</div>
                    <div style="margin-top: 10px; color: #aaa; font-size: 14px;">
                        <?php echo $todayStats['wins_today'] ?? 0; ?> wins
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $todayStats['active_players_today'] ?? 0; ?></div>
                    <div class="stat-label">Active Players Today</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">⏱️</div>
                    <div class="stat-number">
                        <?php
                        $avgTime = $todayStats['avg_time_today'] ?? 0;
                        echo $avgTime ? round($avgTime) . 's' : 'N/A';
                        ?>
                    </div>
                    <div class="stat-label">Avg. Game Time</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-number">
                        <?php
                        $totalGames = $db->fetch("SELECT COUNT(*) as count FROM game_sessions")['count'];
                        echo number_format($totalGames);
                        ?>
                    </div>
                    <div class="stat-label">Total Games</div>
                </div>
            </div>


            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">🏆 Top Players (All Time)</div>
                    </div>

                    <?php if (empty($topPlayers)): ?>
                        <div style="text-align: center; padding: 30px; color: #888;">
                            <div style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;">👤</div>
                            <p>No player data available</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Player</th>
                                <th>Total Score</th>
                                <th>Games</th>
                                <th>Win Rate</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($topPlayers as $index => $player): ?>
                                <tr>
                                    <td style="font-weight: bold; color:
                                        <?php echo $index < 3 ? ['#ffd700', '#c0c0c0', '#cd7f32'][$index] : '#00ADB5'; ?>">
                                        #<?php echo $index + 1; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($player['username']); ?></strong><br>
                                        <small style="color: #aaa;"><?php echo htmlspecialchars($player['full_name'] ?? ''); ?></small>
                                    </td>
                                    <td style="font-weight: bold;"><?php echo number_format($player['total_score']); ?></td>
                                    <td><?php echo $player['total_games']; ?></td>
                                    <td>
                                        <?php
                                        $winRate = $player['total_games'] > 0
                                            ? round(($player['games_won'] / $player['total_games']) * 100, 1)
                                            : 0;
                                        $color = $winRate >= 50 ? '#28a745' : ($winRate >= 30 ? '#ffc107' : '#ff6b6b');
                                        ?>
                                        <span style="color: <?php echo $color; ?>;">
                                            <?php echo $winRate; ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">🕐 Recent Games</div>
                        <a href="#" class="admin-btn" style="font-size: 12px;">View All</a>
                    </div>

                    <?php if (empty($recentGames)): ?>
                        <div style="text-align: center; padding: 30px; color: #888;">
                            <div style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;">🕹️</div>
                            <p>No recent games</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                            <tr>
                                <th>Player</th>
                                <th>Difficulty</th>
                                <th>Time</th>
                                <th>Result</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recentGames as $game): ?>
                                <tr>
                                    <td>
                                        <?php if ($game['username']): ?>
                                            <?php echo htmlspecialchars($game['username']); ?>
                                        <?php else: ?>
                                            <span style="color: #888;">Guest</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color:
                                            <?php echo $game['difficulty'] == 'beginner' ? '#28a745' :
                                            ($game['difficulty'] == 'intermediate' ? '#ffc107' : '#ff6b6b'); ?>">
                                            <?php echo ucfirst($game['difficulty']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo floor($game['total_time'] / 60) . ':' . sprintf('%02d', $game['total_time'] % 60); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $game['game_state'] == 'won' ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo ucfirst($game['game_state']); ?>
                                        </span>
                                    </td>
                                    <td style="color: #aaa; font-size: 13px;">
                                        <?php echo date('H:i', strtotime($game['created_at'])); ?><br>
                                        <small><?php echo date('M d', strtotime($game['created_at'])); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card" style="grid-column: span 2;">
                    <div class="card-header">
                        <div class="card-title">📈 Activity Last 30 Days</div>
                    </div>

                    <div class="chart-container">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="dashboard-card" style="margin-top: 20px;">
                <div class="card-header">
                    <div class="card-title">🔍 Advanced Statistics</div>
                </div>

                <div class="filters">
                    <select class="filter-select">
                        <option>All Difficulties</option>
                        <option>Beginner</option>
                        <option>Intermediate</option>
                        <option>Expert</option>
                    </select>

                    <select class="filter-select">
                        <option>Last 7 Days</option>
                        <option>Last 30 Days</option>
                        <option>Last 90 Days</option>
                        <option>All Time</option>
                    </select>

                    <select class="filter-select">
                        <option>All Results</option>
                        <option>Wins Only</option>
                        <option>Losses Only</option>
                    </select>

                    <button class="export-btn">
                        📥 Export Data
                    </button>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Games</th>
                            <th>Games Won</th>
                            <th>Win Rate</th>
                            <th>Avg. Time</th>
                            <th>Total Score</th>
                            <th>Unique Players</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($globalStats as $stat): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($stat['date'])); ?></td>
                                <td><?php echo $stat['total_games']; ?></td>
                                <td><?php echo $stat['games_won']; ?></td>
                                <td>
                                    <?php
                                    $winRate = $stat['total_games'] > 0
                                        ? round(($stat['games_won'] / $stat['total_games']) * 100, 1)
                                        : 0;
                                    echo $winRate . '%';
                                    ?>
                                </td>
                                <td><?php echo round($stat['avg_time'], 1); ?>s</td>
                                <td><?php echo number_format($stat['total_score']); ?></td>
                                <td><?php echo 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">...</button>
                    <button class="page-btn">10</button>
                </div>
            </div>
        </div>
    </main>

    <script>

        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('activityChart').getContext('2d');


            const activityData = <?php echo json_encode($globalStats); ?>;


            const labels = activityData.map(stat => {
                const date = new Date(stat.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }).reverse();

            const gamesData = activityData.map(stat => stat.total_games).reverse();
            const winsData = activityData.map(stat => stat.games_won).reverse();

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total Games',
                            data: gamesData,
                            borderColor: '#00ADB5',
                            backgroundColor: 'rgba(0, 173, 181, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Games Won',
                            data: winsData,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#eee'
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#aaa'
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#aaa'
                            },
                            beginAtZero: true
                        }
                    }
                }
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