<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>normalize.css">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>style.css">
    <?php if (isset($page) && $page === 'works'): ?>
        <link rel="stylesheet" href="<?php echo CSS_PATH; ?>works.css">
    <?php endif; ?>
    <?php if (isset($page) && $page === 'index'): ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php endif; ?>
</head>
<body>
<?php
$current_page = isset($page) ? $page : basename($_SERVER['PHP_SELF'], '.php');

// Определяем, находимся ли мы в админ-панели
$is_admin_section = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
?>

<header class="header">
    <div class="container">
        <nav class="header_nav">
            <ul class="header_list">
                <li>
                    <a href="<?php echo $is_admin_section ? '../index.php' : 'index.php'; ?>"
                            <?php echo ($current_page == 'index') ? 'class="active"' : ''; ?>>
                        Home
                    </a>
                </li>
                <li>
                    <a href="<?php echo $is_admin_section ? '../aboutme.php' : 'aboutme.php'; ?>"
                            <?php echo ($current_page == 'aboutme') ? 'class="active"' : ''; ?>>
                        About Me
                    </a>
                </li>
                <li>
                    <a href="<?php echo $is_admin_section ? '../works.php' : 'works.php'; ?>"
                            <?php echo ($current_page == 'works') ? 'class="active"' : ''; ?>>
                        My works
                    </a>
                </li>
                <li>
                    <a href="<?php echo $is_admin_section ? '../contact.php' : 'contact.php'; ?>"
                            <?php echo ($current_page == 'contact') ? 'class="active"' : ''; ?>>
                        Contact
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id']): ?>
                    <?php if (isAdmin()): ?>
                        <li>
                            <a href="<?php echo $is_admin_section ? 'index.php' : 'admin/index.php'; ?>"
                               style="color: #00ADB5; font-weight: bold;">
                                Admin Panel
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo $is_admin_section ? 'logout.php' : 'logout.php'; ?>" class="logout-btn">
                            Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?php echo $is_admin_section ? '../login.php' : 'login.php'; ?>"
                                <?php echo ($current_page == 'login') ? 'class="active"' : ''; ?>>
                            Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <img src="<?php echo IMG_PATH; ?>Line 2.png" alt="Divider line" class="line">
    </div>
</header>