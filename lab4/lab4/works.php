<?php
require_once 'includes/config.php';
$page = 'works';
$page_title = 'My Works';
ob_start();
?>

    <div class="pure-css-gallery">
        <input type="radio" name="gallery" id="slide-1" class="gallery-radio" checked>
        <input type="radio" name="gallery" id="slide-2" class="gallery-radio">

        <div class="slides-container">
            <div class="gallery-slide slide-1">
                <div class="puzzle-animation">
                    <div class="puzzle-piece p1"></div>
                    <div class="puzzle-piece p2"></div>
                    <div class="puzzle-piece p3"></div>
                    <div class="puzzle-piece p4"></div>
                    <div class="puzzle-piece p5"></div>
                    <div class="puzzle-piece p6"></div>
                    <div class="puzzle-piece p7"></div>
                    <div class="puzzle-piece p8"></div>
                    <div class="puzzle-piece p9"></div>
                </div>
            </div>

            <div class="gallery-slide slide-2">
                <div class="puzzle-animation">
                    <div class="puzzle-piece p1"></div>
                    <div class="puzzle-piece p2"></div>
                    <div class="puzzle-piece p3"></div>
                    <div class="puzzle-piece p4"></div>
                    <div class="puzzle-piece p5"></div>
                    <div class="puzzle-piece p6"></div>
                    <div class="puzzle-piece p7"></div>
                    <div class="puzzle-piece p8"></div>
                    <div class="puzzle-piece p9"></div>
                </div>
            </div>
        </div>

        <div class="gallery-controls">
            <div class="nav-buttons">
                <label for="slide-2" class="nav-btn prev">‹</label>

                <div class="nav-dots">
                    <label for="slide-1" class="nav-dot dot-1 active">●</label>
                    <label for="slide-2" class="nav-dot dot-2">●</label>
                </div>

                <label for="slide-1" class="nav-btn next">›</label>
            </div>
        </div>
    </div>

<?php
$hide_divider = true; // Скрываем разделитель перед футером
$content = ob_get_clean();
include 'includes/header.php';
echo $content;
include 'includes/footer.php';
?>