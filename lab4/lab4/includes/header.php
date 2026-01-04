<?php
// includes/header.php

$current_page = isset($page) ? $page : basename($_SERVER['PHP_SELF'], '.php');

// Определяем базовый путь в зависимости от текущей директории
$is_in_game_folder = strpos($_SERVER['PHP_SELF'], '/game/') !== false;
$is_in_admin_folder = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;

// Устанавливаем правильный префикс для путей
if ($is_in_game_folder) {
    $path_prefix = '../';
} elseif ($is_in_admin_folder) {
    $path_prefix = '../';
} else {
    $path_prefix = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>styles/normalize.css">
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>styles/style.css">
    <?php if (isset($page) && $page === 'works'): ?>
        <link rel="stylesheet" href="<?php echo $path_prefix; ?>styles/works.css">
    <?php endif; ?>
    <?php if (isset($page) && $page === 'index'): ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php endif; ?>
</head>
<body>
<header class="header">
    <div class="container">
        <nav class="header_nav">
            <ul class="header_list">
                <li>
                    <a href="<?php echo $path_prefix; ?>index.php"
                            <?php echo ($current_page == 'index') ? 'class="active"' : ''; ?>>
                        Home
                    </a>
                </li>
                <li>
                    <a href="<?php echo $path_prefix; ?>aboutme.php"
                            <?php echo ($current_page == 'aboutme') ? 'class="active"' : ''; ?>>
                        About Me
                    </a>
                </li>
                <li>
                    <a href="<?php echo $path_prefix; ?>works.php"
                            <?php echo ($current_page == 'works') ? 'class="active"' : ''; ?>>
                        My works
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <span style="color: #ff6b6b; font-size: 12px; margin-left: 5px;">🔒</span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Пункт меню для игры -->
                <li>
                    <a href="<?php echo $path_prefix; ?>game/index.php"
                            <?php echo ($current_page == 'game') ? 'class="active"' : ''; ?>>
                        Minesweeper 🎮
                    </a>
                </li>

                <li>
                    <a href="<?php echo $path_prefix; ?>contact.php"
                            <?php echo ($current_page == 'contact') ? 'class="active"' : ''; ?>>
                        Contact
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id']): ?>
                    <?php if (isAdmin()): ?>
                        <li>
                            <a href="<?php echo $path_prefix; ?>admin/index.php"
                               style="color: #00ADB5; font-weight: bold;">
                                Admin Panel
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo $path_prefix; ?>logout.php" class="logout-btn">
                            Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?php echo $path_prefix; ?>login.php"
                                <?php echo ($current_page == 'login') ? 'class="active"' : ''; ?>>
                            Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <img src="<?php echo $path_prefix; ?>img/Line 2.png" alt="Divider line" class="line">
    </div>
</header>