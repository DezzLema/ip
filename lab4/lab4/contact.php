<?php
require_once 'includes/config.php';
require_once 'includes/db_connection.php';

$page = 'contact';
$page_title = 'Contact';

$errors = [];
$success = false;
$form_data = [
        'name' => '',
        'email' => '',
        'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Сохраняем данные формы для повторного отображения
    $form_data = [
            'name' => $name,
            'email' => $email,
            'message' => $message
    ];

    // серверная валидация
    // валидация имени
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Name must not exceed 100 characters';
    } elseif (!preg_match('/^[a-zA-Z\s\-\.\']+$/', $name)) {
        $errors['name'] = 'Name can only contain letters, spaces, hyphens, dots and apostrophes';
    }

    // валидация email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email must not exceed 100 characters';
    }

    // Валидация сообщения
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Message must be at least 10 characters';
    } elseif (strlen($message) > 2000) {
        $errors['message'] = 'Message must not exceed 2000 characters';
    }

    // защита от спама
    $spam_words = ['http://', 'https://', 'www.', '.com', 'buy now', 'click here', 'viagra', 'casino'];
    $message_lower = strtolower($message);

    foreach ($spam_words as $word) {
        if (strpos($message_lower, $word) !== false) {
            $errors['message'] = 'Message contains suspicious content';
            break;
        }
    }

    // анти спам
    if (isset($_SESSION['last_submit_time'])) {
        $time_diff = time() - $_SESSION['last_submit_time'];
        if ($time_diff < 10) { // 10 секунд между отправками
            $errors['general'] = 'Please wait a moment before sending another message';
        }
    }

    // если нет ошибок, сохраняем в бд
    if (empty($errors)) {
        $db = Database::getInstance();

        // сохранение
        $db->query(
                "INSERT INTO messages (user_id, name, email, message, ip_address, user_agent) 
             VALUES (?, ?, ?, ?, ?, ?)",
                [
                        $_SESSION['user_id'] ?? null,
                        htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
                        $_SERVER['REMOTE_ADDR'],
                        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                ]
        );

        // сохраняем время отправки для анти-спама
        $_SESSION['last_submit_time'] = time();

        // чистим формочку
        $form_data = [
                'name' => '',
                'email' => '',
                'message' => ''
        ];

        $success = true;
        $_SESSION['contact_success'] = "Thank you, $name! Your message has been sent.";

        // редирект чтобы избежать повторной отправки при обновлении
        header('Location: contact.php?success=1');
        exit;
    }
} else {
    // Если это GET запрос, проверяем success параметр
    if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_SESSION['contact_success'])) {
        $success = true;
        $success_message = $_SESSION['contact_success'];
        unset($_SESSION['contact_success']);
    }
}

ob_start();
?>
    <main class="main contact-main">
        <div class="container">
            <div class="contact-content">
                <?php if ($success && isset($success_message)): ?>
                    <div class="success-message" style="background-color: rgba(0,173,181,0.1); color: #00ADB5; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(0,173,181,0.3);">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($errors['general'])): ?>
                    <div class="error-message" style="background-color: rgba(255,107,107,0.1); color: #ff6b6b; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(255,107,107,0.3);">
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>

                <h1 class="contact-title">Есть интересный проект?</h1>
                <p class="contact-subtitle">Давайте работать вместе! Заполните форму ниже, и я свяжусь с вами как можно скорее.</p>

                <form class="contact-form" method="POST" action="contact.php" id="contact-form" novalidate>
                    <div class="form-group">
                        <label for="name" class="form-label">Имя *</label>
                        <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-input <?php echo isset($errors['name']) ? 'error' : ''; ?>"
                                placeholder="Введите свое имя"
                                required
                                value="<?php echo htmlspecialchars($form_data['name']); ?>"
                        >
                        <?php if (isset($errors['name'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['name']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Почта *</label>
                        <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>"
                                placeholder="Введите email"
                                required
                                value="<?php echo htmlspecialchars($form_data['email']); ?>"
                        >
                        <?php if (isset($errors['email'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['email']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Сообщение *</label>
                        <textarea
                                id="message"
                                name="message"
                                class="form-textarea <?php echo isset($errors['message']) ? 'error' : ''; ?>"
                                placeholder=""
                                rows="6"
                                required
                        ><?php echo htmlspecialchars($form_data['message']); ?></textarea>
                        <?php if (isset($errors['message'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['message']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="char-counter" style="color: #aaa; font-size: 13px; margin-top: 5px; text-align: right;">
                            <span id="char-count"><?php echo strlen($form_data['message']); ?></span>/2000 символов
                        </div>
                    </div>

                    <div style="display: none;">
                        <input type="text" name="honeypot" id="honeypot" value="">
                    </div>

                    <button type="submit" class="send-btn">
                        Отправить сообщение
                        <span class="btn-icon">→</span>
                    </button>
                </form>

                <div class="contact-info">
                    <h2>Другие контакты</h2>
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-text">
                                <h3>Email</h3>
                                <p>hello@creativeui.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-text">
                                <h3>Телефон</h3>
                                <p>+7 (927) 834-9353</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-text">
                                <h3>Город</h3>
                                <p>Россия, Москва</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php
$content = ob_get_clean();

// Включаем шаблоны
include 'includes/header.php';
echo $content;
// Подключаем внешний JavaScript файл
echo '<script src="scripts/contact.js"></script>';
include 'includes/footer.php';
?>