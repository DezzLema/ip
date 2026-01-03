<?php
require_once 'includes/config.php';
$page = 'register';
$page_title = 'Register';
ob_start();
?>
    <main class="main contact-main">
        <div class="container">
            <div class="contact-content">
                <h1 class="contact-title">Register</h1>
                <p class="contact-subtitle">Registration functionality can be added here.</p>

                <div style="text-align: center; padding: 40px; background-color: rgba(57, 62, 70, 0.6); border-radius: 15px;">
                    <p>Registration system can be implemented with database integration.</p>
                    <a href="login.php" class="contact-btn" style="margin-top: 20px;">Go to Login</a>
                </div>
            </div>
        </div>
    </main>
<?php
$content = ob_get_clean();
include 'includes/header.php';
echo $content;
include 'includes/footer.php';
?>