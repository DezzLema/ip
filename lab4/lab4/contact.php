<?php
require_once 'includes/config.php';
$page = 'contact';
$page_title = 'Contact Me';

// Обработка формы (упрощенный пример)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');

    // Здесь можно добавить отправку на email или сохранение в БД
    $success_message = "Thank you, $name! Your message has been sent.";

    // Для демо просто покажем сообщение
    $_SESSION['contact_message'] = $success_message;
    header('Location: contact.php?success=1');
    exit;
}

ob_start();
?>

    <main class="main contact-main">
        <div class="container">
            <div class="contact-content">
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <div class="success-message">
                        <?php echo $_SESSION['contact_message'] ?? 'Thank you! Your message has been sent.'; ?>
                        <?php unset($_SESSION['contact_message']); ?>
                    </div>
                <?php endif; ?>

                <h1 class="contact-title">Got a project in mind?</h1>
                <p class="contact-subtitle">Let's work together! Fill out the form below and I'll get back to you as soon as possible.</p>

                <form class="contact-form" method="POST" action="contact.php">
                    <div class="form-group">
                        <label for="name" class="form-label">Your Name</label>
                        <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-input"
                                placeholder="Enter your name"
                                required
                                value="<?php echo $_POST['name'] ?? ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Your Email</label>
                        <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="Enter your email address"
                                required
                                value="<?php echo $_POST['email'] ?? ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Your Message</label>
                        <textarea
                                id="message"
                                name="message"
                                class="form-textarea"
                                placeholder="Tell me about your project..."
                                rows="6"
                                required
                        ><?php echo $_POST['message'] ?? ''; ?></textarea>
                    </div>

                    <button type="submit" class="send-btn">
                        Send Message
                        <span class="btn-icon">→</span>
                    </button>
                </form>

                <div class="contact-info">
                    <h2>Other Ways to Reach Me</h2>
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">✉️</div>
                            <div class="contact-text">
                                <h3>Email</h3>
                                <p>hello@creativeui.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">📱</div>
                            <div class="contact-text">
                                <h3>Phone</h3>
                                <p>+1 (555) 123-4567</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">📍</div>
                            <div class="contact-text">
                                <h3>Location</h3>
                                <p>San Francisco, CA</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php
$custom_scripts = '
<script>
    // Можно оставить клиентскую валидацию
    document.querySelector(".contact-form").addEventListener("submit", function(e) {
        // Клиентская валидация (опционально)
        if (!this.checkValidity()) {
            e.preventDefault();
            alert("Please fill in all required fields correctly.");
        }
    });
</script>';
$content = ob_get_clean();
include 'includes/header.php';
echo $content;
include 'includes/footer.php';
?>