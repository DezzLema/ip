<?php if (!isset($hide_divider) || $hide_divider !== true): ?>
    <div class="divider">
        <img src="<?php echo IMG_PATH; ?>Line 2.png" alt="Divider line" class="line">
    </div>
<?php endif; ?>

<footer class="footer">
    <div class="container">
        <nav class="footer-nav">
            <ul class="footer_list">
                <li><a href="index.php">Home</a></li>
                <li><a href="aboutme.php">About me</a></li>
                <li><a href="works.php">My works</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
        <div class="social">
            <a href="#" aria-label="Facebook" class="social-link">
                <img src="<?php echo IMG_PATH; ?>facebook.png" alt="Facebook" class="social-icon">
            </a>
            <a href="#" aria-label="Instagram" class="social-link">
                <img src="<?php echo IMG_PATH; ?>instagram.png" alt="Instagram" class="social-icon">
            </a>
            <a href="#" aria-label="Twitter" class="social-link">
                <img src="<?php echo IMG_PATH; ?>twitter.png" alt="Twitter" class="social-icon">
            </a>
            <a href="#" aria-label="YouTube" class="social-link">
                <img src="<?php echo IMG_PATH; ?>youtube.png" alt="YouTube" class="social-icon">
            </a>
        </div>
        <p class="copyright">&copy; <?php echo COPYRIGHT_YEAR; ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
    </div>
</footer>

<?php if (isset($custom_scripts)): ?>
    <?php echo $custom_scripts; ?>
<?php endif; ?>
</body>
</html>