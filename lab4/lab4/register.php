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
                <h1 class="contact-title">Регистрация</h1>

                <?php if ($error): ?>
                    <div class="error-message" style="background-color: rgba(255,107,107,0.1); color: #ff6b6b; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(255,107,107,0.3);">
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
                                placeholder="Выберете username"
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
                                placeholder="email"
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
                        <label for="full_name" class="form-label">Имя</label>
                        <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                class="form-input <?php echo isset($errors['full_name']) ? 'error' : ''; ?>"
                                placeholder="Имя"
                                value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                        >
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['full_name']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Пароль *</label>
                        <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>"
                                placeholder="Пароль"
                                required
                        >
                        <?php if (isset($errors['password'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['password']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="password-requirements" style="color: #aaa; font-size: 13px; margin-top: 5px;">
                            Пароль должен содердать: заглавные буквы, строчные буквы, цифры, 8 символов
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Подтверждение пароля *</label>
                        <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                                placeholder="Повторите пароль"
                                required
                        >
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['confirm_password']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="send-btn">
                        Регистрация
                        <span class="btn-icon">→</span>
                    </button>

                    <p style="text-align: center; margin-top: 20px;">
                        Уже есть аккаунт? <a href="login.php">Вход</a>
                    </p>
                </form>
            </div>
        </div>
    </main>

<?php
$content = ob_get_clean();
include 'includes/header.php';
echo $content;
// Подключаем внешний JavaScript файл
echo '<script src="scripts/register.js"></script>';
include 'includes/footer.php';
?>