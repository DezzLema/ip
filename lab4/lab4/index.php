<?php
require_once 'includes/config.php';
$page = 'index';
$page_title = 'Creative UI Designer';
ob_start();
?>

    <main class="main">
        <div class="container">
            <div class="main-content">
                <h1 class="ui_des">CREATIVE UI</h1>
                <h1 class="designer">DESIGNER</h1>
                <div class="buttons">
                    <button class="hire" onclick="window.location.href='contact.php'">Hire me</button>
                    <button class="cv" onclick="downloadCV()">Download CV</button>
                </div>
            </div>
            <div class="image-container">
                <img src="<?php echo IMG_PATH; ?>Group 2345.png" alt="Creative designer illustration" class="guy">
            </div>
        </div>
    </main>

<?php
$custom_scripts = '
<script>
    function downloadCV() {
        alert("CV download will start shortly.");
    }
</script>';
$content = ob_get_clean();
include 'includes/header.php';
echo $content;
include 'includes/footer.php';
?>