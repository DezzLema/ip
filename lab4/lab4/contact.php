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

    // СЕРВЕРНАЯ ВАЛИДАЦИЯ
    // Валидация имени
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Name must not exceed 100 characters';
    } elseif (!preg_match('/^[a-zA-Z\s\-\.\']+$/', $name)) {
        $errors['name'] = 'Name can only contain letters, spaces, hyphens, dots and apostrophes';
    }

    // Валидация email
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

    // Защита от спама
    $spam_words = ['http://', 'https://', 'www.', '.com', 'buy now', 'click here', 'viagra', 'casino'];
    $message_lower = strtolower($message);

    foreach ($spam_words as $word) {
        if (strpos($message_lower, $word) !== false) {
            $errors['message'] = 'Message contains suspicious content';
            break;
        }
    }

    // Проверка на быструю отправку (анти-спам)
    if (isset($_SESSION['last_submit_time'])) {
        $time_diff = time() - $_SESSION['last_submit_time'];
        if ($time_diff < 10) { // 10 секунд между отправками
            $errors['general'] = 'Please wait a moment before sending another message';
        }
    }

    // Если нет ошибок, сохраняем в БД
    if (empty($errors)) {
        $db = Database::getInstance();

        // Сохраняем в БД
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

        // Сохраняем время отправки для анти-спама
        $_SESSION['last_submit_time'] = time();

        // Очищаем данные формы
        $form_data = [
                'name' => '',
                'email' => '',
                'message' => ''
        ];

        $success = true;
        $_SESSION['contact_success'] = "Thank you, $name! Your message has been sent.";

        // Редирект, чтобы избежать повторной отправки при обновлении
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

ob_start(); // НАЧИНАЕМ БУФЕРИЗАЦИЮ ВЫВОДА
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

                <h1 class="contact-title">Got a project in mind?</h1>
                <p class="contact-subtitle">Let's work together! Fill out the form below and I'll get back to you as soon as possible.</p>

                <form class="contact-form" method="POST" action="contact.php" id="contact-form" novalidate>
                    <div class="form-group">
                        <label for="name" class="form-label">Your Name *</label>
                        <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-input <?php echo isset($errors['name']) ? 'error' : ''; ?>"
                                placeholder="Enter your name"
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
                        <label for="email" class="form-label">Your Email *</label>
                        <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>"
                                placeholder="Enter your email address"
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
                        <label for="message" class="form-label">Your Message *</label>
                        <textarea
                                id="message"
                                name="message"
                                class="form-textarea <?php echo isset($errors['message']) ? 'error' : ''; ?>"
                                placeholder="Tell me about your project..."
                                rows="6"
                                required
                        ><?php echo htmlspecialchars($form_data['message']); ?></textarea>
                        <?php if (isset($errors['message'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['message']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="char-counter" style="color: #aaa; font-size: 13px; margin-top: 5px; text-align: right;">
                            <span id="char-count"><?php echo strlen($form_data['message']); ?></span>/2000 characters
                        </div>
                    </div>

                    <!-- Скрытое поле для защиты от ботов (honeypot) -->
                    <div style="display: none;">
                        <input type="text" name="honeypot" id="honeypot" value="">
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
// КЛИЕНТСКАЯ ВАЛИДАЦИЯ (JavaScript)
$custom_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("contact-form");
    const nameInput = document.getElementById("name");
    const emailInput = document.getElementById("email");
    const messageInput = document.getElementById("message");
    const charCount = document.getElementById("char-count");
    const honeypot = document.getElementById("honeypot");
    
    // Счетчик символов для сообщения
    function updateCharCount() {
        const length = messageInput.value.length;
        charCount.textContent = length;
        
        if (length > 1900) {
            charCount.style.color = "#ff6b6b";
        } else if (length > 1800) {
            charCount.style.color = "#ffc107";
        } else {
            charCount.style.color = "#aaa";
        }
    }
    
    // Инициализация счетчика
    updateCharCount();
    messageInput.addEventListener("input", updateCharCount);
    
    // Функция для показа ошибки
    function showError(input, message) {
        const formGroup = input.closest(".form-group");
        let errorDiv = formGroup.querySelector(".error-text");
        
        if (!errorDiv) {
            errorDiv = document.createElement("div");
            errorDiv.className = "error-text";
            errorDiv.style.color = "#ff6b6b";
            errorDiv.style.fontSize = "14px";
            errorDiv.style.marginTop = "5px";
            formGroup.appendChild(errorDiv);
        }
        
        errorDiv.textContent = message;
        input.classList.add("error");
    }
    
    // Функция для очистки ошибки
    function clearError(input) {
        const formGroup = input.closest(".form-group");
        const errorDiv = formGroup.querySelector(".error-text");
        
        if (errorDiv) {
            errorDiv.remove();
        }
        
        input.classList.remove("error");
    }
    
    // Валидация имени
    nameInput.addEventListener("input", function() {
        const value = nameInput.value.trim();
        
        if (!value) {
            showError(nameInput, "Name is required");
        } else if (value.length < 2) {
            showError(nameInput, "Name must be at least 2 characters");
        } else if (value.length > 100) {
            showError(nameInput, "Name must not exceed 100 characters");
        } else if (!/^[a-zA-Z\s\\-\\.\\\']+$/.test(value)) {
            showError(nameInput, "Only letters, spaces, hyphens, dots and apostrophes allowed");
        } else {
            clearError(nameInput);
        }
    });
    
    // Валидация email
    emailInput.addEventListener("input", function() {
        const value = emailInput.value.trim();
        const emailRegex = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
        
        if (!value) {
            showError(emailInput, "Email is required");
        } else if (!emailRegex.test(value)) {
            showError(emailInput, "Please enter a valid email");
        } else if (value.length > 100) {
            showError(emailInput, "Email must not exceed 100 characters");
        } else {
            clearError(emailInput);
        }
    });
    
    // Валидация сообщения
    messageInput.addEventListener("input", function() {
        const value = messageInput.value.trim();
        
        if (!value) {
            showError(messageInput, "Message is required");
        } else if (value.length < 10) {
            showError(messageInput, "Message must be at least 10 characters");
        } else if (value.length > 2000) {
            showError(messageInput, "Message must not exceed 2000 characters");
        } else {
            // Проверка на спам
            const spamWords = ["http://", "https://", "www.", ".com", "buy now", "click here", "viagra", "casino"];
            const lowerValue = value.toLowerCase();
            
            for (let word of spamWords) {
                if (lowerValue.includes(word)) {
                    showError(messageInput, "Message contains suspicious content");
                    return;
                }
            }
            
            clearError(messageInput);
        }
    });
    
    // Проверка honeypot поля
    honeypot.addEventListener("input", function() {
        if (honeypot.value) {
            // Бот заполнил скрытое поле - блокируем отправку
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                alert("Submission blocked - suspicious activity detected");
                return false;
            }, { once: true });
        }
    });
    
    // Финальная проверка перед отправкой
    form.addEventListener("submit", function(e) {
        let hasErrors = false;
        
        // Проверяем honeypot
        if (honeypot.value) {
            e.preventDefault();
            alert("Submission blocked - suspicious activity detected");
            return false;
        }
        
        // Проверяем все поля
        const inputs = [nameInput, emailInput, messageInput];
        
        inputs.forEach(input => {
            // Триггерим событие input для запуска валидации
            const event = new Event("input", { bubbles: true });
            input.dispatchEvent(event);
            
            // Проверяем, есть ли ошибка
            if (input.classList.contains("error")) {
                hasErrors = true;
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            
            // Прокрутка к первой ошибке
            const firstError = form.querySelector(".error");
            if (firstError) {
                firstError.scrollIntoView({ 
                    behavior: "smooth", 
                    block: "center" 
                });
                firstError.focus();
            }
            
            return false;
        }
        
        // Показываем индикатор загрузки
        const submitBtn = form.querySelector(".send-btn");
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = \'Sending... <span class="btn-icon">⏳</span>\';
        submitBtn.disabled = true;
        
        // Автоматическое восстановление кнопки через 10 секунд (на случай ошибки)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 10000);
    });
});
</script>';

// Стили для ошибок
$custom_css = '
<style>
    .form-input.error,
    .form-textarea.error {
        border-color: #ff6b6b !important;
        background-color: rgba(255, 107, 107, 0.05) !important;
    }
    
    .error-text {
        color: #ff6b6b;
        font-size: 14px;
        margin-top: 5px;
        padding: 5px 10px;
        background: rgba(255, 107, 107, 0.1);
        border-radius: 4px;
        border-left: 3px solid #ff6b6b;
    }
    
    .error-message {
        background-color: rgba(255, 107, 107, 0.1);
        color: #ff6b6b;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid rgba(255, 107, 107, 0.3);
    }
    
    .success-message {
        background-color: rgba(0, 173, 181, 0.1);
        color: #00ADB5;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid rgba(0, 173, 181, 0.3);
    }
    
    .send-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>';

$content = ob_get_clean(); // ЗАКАНЧИВАЕМ БУФЕРИЗАЦИЮ

// Включаем шаблоны
include 'includes/header.php';
echo $custom_css;
echo $content;
echo $custom_scripts;
include 'includes/footer.php';
?>