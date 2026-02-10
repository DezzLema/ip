<?php
require_once 'includes/config.php';
$page = 'aboutme';
$page_title = 'About Me';
ob_start();
?>

    <main class="main about-main">
        <div class="container">
            <div class="about-content">
                <h1 class="about-title">About Me</h1>
                <div class="about-text">
                    <p>Hello! I'm a passionate Creative UI Designer with over 5 years of experience in creating beautiful, functional, and user-centered digital experiences. My journey in design started with a curiosity about how things work and evolved into a career dedicated to making technology more accessible and enjoyable for everyone.</p>

                    <p>I specialize in creating intuitive user interfaces that not only look stunning but also solve real problems. My design philosophy is rooted in the belief that good design should be invisible — it should feel so natural that users don't even notice it's there.</p>


                    <p>When I'm not designing, you can find me exploring new design trends, contributing to open-source projects, or mentoring aspiring designers. I believe in continuous learning and staying updated with the latest industry developments.</p>

                    <a href="contact.php" class="contact-btn">Get in Touch</a>
                    <a href="works.php" class="works-btn">View My Works</a>
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