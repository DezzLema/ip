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
        <div style="text-align: center; margin-bottom: 20px;">
            <p style="color: #00ADB5; font-size: 16px;">
                Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
                You're viewing exclusive content.
            </p>
        </div>

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

                <div style="text-align: center; margin-top: 20px; padding: 10px; background: rgba(0,173,181,0.1); border-radius: 8px;">
                    <p style="color: #00ADB5; font-size: 14px; margin: 0;">
                        Exclusive content for registered users
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php
// Добавляем кастомные стили
$custom_css = '
<style>
    /* Стили для информации о работе */
    .work-info {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.7);
        padding: 15px 20px;
        border-radius: 10px;
        backdrop-filter: blur(5px);
        z-index: 10;
    }
    
    .work-info h3 {
        color: #00ADB5;
        margin-bottom: 8px;
        font-size: 18px;
    }
    
    .work-info p {
        color: #eee;
        font-size: 14px;
        margin-bottom: 5px;
        line-height: 1.4;
    }
    
    .work-category {
        display: inline-block;
        background: rgba(0, 173, 181, 0.2);
        color: #00ADB5;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 12px;
        margin-top: 5px;
    }
    
    /* Кнопки навигации */
    .nav-btn {
        background: rgba(0, 173, 181, 0.2);
        color: #00ADB5;
        border: 2px solid rgba(0, 173, 181, 0.3);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .nav-btn:hover {
        background: #00ADB5;
        color: white;
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(0, 173, 181, 0.4);
    }
    
    .nav-btn:active {
        transform: scale(0.95);
    }
    
    .nav-dot {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0;
        color: transparent;
        padding: 0;
    }
    
    .nav-dot:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: scale(1.2);
    }
    
    .nav-dot.active {
        background: #00ADB5 !important;
        transform: scale(1.3);
        box-shadow: 0 0 0 3px rgba(0, 173, 181, 0.2);
        animation: pulse 2s infinite;
    }
    
    /* Для одного слайда центрируем точки */
    .nav-buttons.single-slide {
        justify-content: center !important;
    }
    
    /* Стиль для защищенного контента */
    .protected-content {
        background: linear-gradient(135deg, rgba(0,173,181,0.1) 0%, rgba(34,40,49,0.8) 100%);
        border: 2px solid #00ADB5;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
    }
</style>';

// тут слайдер на js
$custom_scripts = '
<script>
const totalSlides = ' . $total_slides . ';
let currentSlide = 1;

function updateSlide() {
    if (totalSlides === 0) return;
    
    // Обновляем радио-кнопку
    document.getElementById("slide-" + currentSlide).checked = true;
    
    // Обновляем активную точку
    document.querySelectorAll(".nav-dot").forEach((dot, index) => {
        if (index + 1 === currentSlide) {
            dot.classList.add("active");
        } else {
            dot.classList.remove("active");
        }
    });
    
    // Запускаем анимацию пазла
    restartPuzzleAnimation();
}

function restartPuzzleAnimation() {
    // Удаляем и добавляем класс анимации для перезапуска
    const currentSlideEl = document.querySelector(".slide-" + currentSlide);
    if (currentSlideEl) {
        const pieces = currentSlideEl.querySelectorAll(".puzzle-piece");
        pieces.forEach(piece => {
            piece.style.animation = "none";
            setTimeout(() => {
                piece.style.animation = "puzzleAssemble 0.8s ease-out forwards";
            }, 10);
        });
    }
}

function nextSlide() {
    if (totalSlides <= 1) return;
    
    if (currentSlide < totalSlides) {
        currentSlide++;
    } else {
        currentSlide = 1;
    }
    updateSlide();
}

function prevSlide() {
    if (totalSlides <= 1) return;
    
    if (currentSlide > 1) {
        currentSlide--;
    } else {
        currentSlide = totalSlides;
    }
    updateSlide();
}

function goToSlide(slideNumber) {
    if (totalSlides <= 1) return;
    
    currentSlide = slideNumber;
    updateSlide();
}

// Автопереключение слайдов каждые 5 секунд
let slideInterval;

function startAutoSlide() {
    if (totalSlides > 1) {
        slideInterval = setInterval(nextSlide, 5000);
    }
}

function stopAutoSlide() {
    clearInterval(slideInterval);
}

// Инициализация
document.addEventListener("DOMContentLoaded", function() {
    if (totalSlides > 0) {
        updateSlide();
        startAutoSlide();
        
        // Останавливаем автопереключение при наведении
        const gallery = document.querySelector(".pure-css-gallery");
        if (gallery) {
            gallery.addEventListener("mouseenter", stopAutoSlide);
            gallery.addEventListener("mouseleave", startAutoSlide);
        }
        
        // Добавляем обработчики клавиатуры
        document.addEventListener("keydown", function(e) {
            if (e.key === "ArrowLeft") {
                prevSlide();
            } else if (e.key === "ArrowRight") {
                nextSlide();
            }
        });
    }
});

// Функция для перезапуска анимации при клике на текущий слайд
document.querySelectorAll(".nav-dot").forEach(dot => {
    dot.addEventListener("click", function() {
        const slideNum = parseInt(this.className.match(/dot-(\\d+)/)[1]);
        if (slideNum === currentSlide) {
            restartPuzzleAnimation();
        }
    });
});
</script>';

$hide_divider = true;
$content = ob_get_clean();
include 'includes/header.php';
echo $custom_css;
echo $content;
echo $custom_scripts;
include 'includes/footer.php';
?>