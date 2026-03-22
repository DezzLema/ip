<?php
require_once 'includes/config.php';
require_once 'includes/db_connection.php';

$db = Database::getInstance();

// проверка авторизации
if (!isset($_SESSION['user_id'])) {
    // Если пользователь не авторизован, перенаправляем на страницу логина
    $_SESSION['redirect_url'] = 'works.php';
    header('Location: login.php?message=Please login to view the gallery');
    exit;
}

// получаем работы из бд
$works = $db->fetchAll("
    SELECT * FROM works 
    WHERE is_published = 1 
    ORDER BY created_at DESC
");

$page = 'works';
$page_title = 'My Works';
$total_slides = count($works);
ob_start();
?>

    <div class="pure-css-gallery" id="gallery-container">
        <?php if (empty($works)): ?>
            <div style="text-align: center; padding: 100px 20px;">
                <div style="font-size: 80px; margin-bottom: 20px; color: #00ADB5; opacity: 0.5;">🎨</div>
                <h2 style="color: #eee; margin-bottom: 15px;">No works yet</h2>
                <p style="color: #aaa; max-width: 500px; margin: 0 auto;">
                    Our portfolio is currently being updated. Check back soon!
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($works as $index => $work): ?>
                <input type="radio" name="gallery"
                       id="slide-<?php echo $index + 1; ?>"
                       class="gallery-radio"
                        <?php echo $index === 0 ? 'checked' : ''; ?>>
            <?php endforeach; ?>

            <div class="slides-container">
                <?php foreach ($works as $index => $work): ?>
                    <div class="gallery-slide slide-<?php echo $index + 1; ?>">
                        <div class="puzzle-animation">
                            <?php for ($i = 1; $i <= 9; $i++): ?>
                                <div class="puzzle-piece p<?php echo $i; ?>"
                                     style="background-image: url('<?php echo htmlspecialchars($work['image_path']); ?>')">
                                </div>
                            <?php endfor; ?>
                        </div>
                        <div class="work-info">
                            <h3><?php echo htmlspecialchars($work['title']); ?></h3>
                            <p><?php echo htmlspecialchars($work['description']); ?></p>
                            <?php if (!empty($work['category'])): ?>
                                <span class="work-category"><?php echo htmlspecialchars($work['category']); ?></span>
                            <?php endif; ?>
                            <?php if (isAdmin()): ?>
                                <div style="margin-top: 10px; font-size: 12px; color: #aaa;">
                                    Work ID: <?php echo $work['id']; ?> |
                                    Created: <?php echo date('d.m.Y', strtotime($work['created_at'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="gallery-controls">
                <div class="nav-buttons <?php echo $total_slides <= 1 ? 'single-slide' : ''; ?>">
                    <?php if ($total_slides > 1): ?>
                        <button class="nav-btn prev" onclick="prevSlide()">‹</button>
                    <?php endif; ?>

                    <div class="nav-dots">
                        <?php foreach ($works as $index => $work): ?>
                            <button class="nav-dot dot-<?php echo $index + 1; ?>
                               <?php echo $index === 0 ? ' active' : ''; ?>"
                                    onclick="goToSlide(<?php echo $index + 1; ?>)">
                                ●
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_slides > 1): ?>
                        <button class="nav-btn next" onclick="nextSlide()">›</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php

$js_data = "
<script>
// Глобальные переменные для JavaScript
const totalSlides = " . $total_slides . ";
</script>
";

$hide_divider = true;
$content = ob_get_clean();
include 'includes/header.php';
echo $content;
echo $js_data;
echo '<script src="scripts/works.js"></script>';
include 'includes/footer.php';
?>