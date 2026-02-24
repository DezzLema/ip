<?php
require_once 'includes/config.php';
require_once 'includes/db_connection.php';

$page = 'register';
$page_title = 'Register';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');

    // СЕРВЕРНАЯ ВАЛИДАЦИЯ
    $errors = [];

    // Валидация username
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters';
    } elseif (strlen($username) > 50) {
        $errors['username'] = 'Username must not exceed 50 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers and underscores';
    }

    // Валидация email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email must not exceed 100 characters';
    }

    // Валидация пароля
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    } elseif (strlen($password) > 100) {
        $errors['password'] = 'Password must not exceed 100 characters';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors['password'] = 'Password must contain at least one lowercase letter';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one number';
    }

    // Валидация подтверждения пароля
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Please confirm your password';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    // Валидация полного имени
    if (!empty($full_name) && strlen($full_name) > 100) {
        $errors['full_name'] = 'Full name must not exceed 100 characters';
    }

    // Если нет ошибок валидации
    if (empty($errors)) {
        $db = Database::getInstance();

        // Проверка существующего пользователя
        $existing = $db->fetch(
                "SELECT id FROM users WHERE username = ? OR email = ?",
                [$username, $email]
        );

        if ($existing) {
            $error = 'Username or email already exists';
        } else {
            // Хеширование пароля
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Создание пользователя
            $db->query(
                    "INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)",
                    [$username, $email, $hashed_password, $full_name]
            );

            $success = 'Registration successful! You can now <a href="login.php">login</a>.';

            // Очищаем поля формы
            $username = $email = $full_name = '';
        }
    }
}

ob_start();
?>
    <main class="main contact-main">
        <div class="container">
            <div class="contact-content">
                <h1 class="contact-title">Register</h1>

                <?php if ($error): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-message" style="background-color: rgba(0,173,181,0.1); color: #00ADB5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form class="contact-form" method="POST" action="register.php" id="register-form" novalidate>
                    <div class="form-group">
                        <label for="username" class="form-label">Username *</label>
                        <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input <?php echo isset($errors['username']) ? 'error' : ''; ?>"
                                placeholder="Choose a username (3-50 characters)"
                                required
                                value="<?php echo htmlspecialchars($username ?? ''); ?>"
                        >
                        <?php if (isset($errors['username'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['username']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email *</label>
                        <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>"
                                placeholder="Enter your email"
                                required
                                value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        >
                        <?php if (isset($errors['email'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['email']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                class="form-input <?php echo isset($errors['full_name']) ? 'error' : ''; ?>"
                                placeholder="Enter your full name"
                                value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                        >
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['full_name']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password *</label>
                        <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>"
                                placeholder="At least 8 characters with uppercase, lowercase and number"
                                required
                        >
                        <?php if (isset($errors['password'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['password']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="password-requirements" style="color: #aaa; font-size: 13px; margin-top: 5px;">
                            Must contain: uppercase, lowercase, number, min 8 chars
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                                placeholder="Repeat your password"
                                required
                        >
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['confirm_password']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="send-btn">
                        Register
                        <span class="btn-icon">→</span>
                    </button>

                    <p style="text-align: center; margin-top: 20px;">
                        Already have an account? <a href="login.php">Login here</a>
                    </p>
                </form>
            </div>
        </div>
    </main>

<?php
// кл. валидация
$custom_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("register-form");
    const username = document.getElementById("username");
    const email = document.getElementById("email");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");
    const fullName = document.getElementById("full_name");
    
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
    
    // Валидация username
    username.addEventListener("input", function() {
        const value = username.value.trim();
        if (value.length < 3) {
            showError(username, "Username must be at least 3 characters");
        } else if (value.length > 50) {
            showError(username, "Username must not exceed 50 characters");
        } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
            showError(username, "Only letters, numbers and underscores allowed");
        } else {
            clearError(username);
        }
    });
    
    // Валидация email
    email.addEventListener("input", function() {
        const value = email.value.trim();
        const emailRegex = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
        
        if (!emailRegex.test(value)) {
            showError(email, "Please enter a valid email");
        } else if (value.length > 100) {
            showError(email, "Email must not exceed 100 characters");
        } else {
            clearError(email);
        }
    });
    
    // Валидация пароля
    password.addEventListener("input", function() {
        const value = password.value;
        
        if (value.length < 8) {
            showError(password, "Password must be at least 8 characters");
        } else if (value.length > 100) {
            showError(password, "Password must not exceed 100 characters");
        } else if (!/[A-Z]/.test(value)) {
            showError(password, "Must contain at least one uppercase letter");
        } else if (!/[a-z]/.test(value)) {
            showError(password, "Must contain at least one lowercase letter");
        } else if (!/[0-9]/.test(value)) {
            showError(password, "Must contain at least one number");
        } else {
            clearError(password);
        }
        
        // Проверка подтверждения пароля, если оно уже введено
        if (confirmPassword.value) {
            if (value !== confirmPassword.value) {
                showError(confirmPassword, "Passwords do not match");
            } else {
                clearError(confirmPassword);
            }
        }
    });
    
    // Валидация подтверждения пароля
    confirmPassword.addEventListener("input", function() {
        if (password.value !== confirmPassword.value) {
            showError(confirmPassword, "Passwords do not match");
        } else {
            clearError(confirmPassword);
        }
    });
    
    // Валидация полного имени
    fullName.addEventListener("input", function() {
        const value = fullName.value.trim();
        if (value.length > 100) {
            showError(fullName, "Full name must not exceed 100 characters");
        } else {
            clearError(fullName);
        }
    });
    
    // Финальная проверка перед отправкой
    form.addEventListener("submit", function(e) {
        let hasErrors = false;
        
        // Проверяем все поля
        const inputs = [username, email, password, confirmPassword];
        
        inputs.forEach(input => {
            // Триггерим событие input для запуска валидации
            const event = new Event("input", { bubbles: true });
            input.dispatchEvent(event);
            
            // Проверяем, есть ли ошибка
            if (input.classList.contains("error")) {
                hasErrors = true;
            }
        });
        
        // Дополнительные проверки
        if (!password.value) {
            showError(password, "Password is required");
            hasErrors = true;
        }
        
        if (!confirmPassword.value) {
            showError(confirmPassword, "Please confirm your password");
            hasErrors = true;
        }
        
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
    });
});
</script>';

// Стили для ошибок
$custom_css = '
<style>
    .form-input.error {
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
    
    .success-message {
        background-color: rgba(0, 173, 181, 0.1) !important;
        color: #00ADB5 !important;
        padding: 15px !important;
        border-radius: 8px !important;
        margin-bottom: 20px !important;
        border: 1px solid rgba(0, 173, 181, 0.3) !important;
    }
</style>';

$content = ob_get_clean();
include 'includes/header.php';
echo $custom_css;
echo $content;
echo $custom_scripts;
include 'includes/footer.php';
?>