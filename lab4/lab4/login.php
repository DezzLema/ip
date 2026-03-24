<?php
require_once 'includes/config.php';
require_once 'includes/db_connection.php';

$page = 'login';
$page_title = 'Login';
$error = '';

// Проверяем, есть ли сообщение о необходимости логина
$message = $_GET['message'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $db = Database::getInstance();

        // Ищем пользователя
        $user = $db->fetch(
                "SELECT id, username, email, password, full_name, role FROM users WHERE username = ? OR email = ?",
                [$username, $username]
        );

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            // Проверяем, есть ли URL для редиректа
            if (isset($_SESSION['redirect_url'])) {
                $redirect_url = $_SESSION['redirect_url'];
                unset($_SESSION['redirect_url']);
                header('Location: ' . $redirect_url);
            } else {
                // Редирект в админку если админ
                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: index.php');
                }
            }
            exit;
        } else {
            $error = 'Неверный username/email или пароль';
        }
    }
}

ob_start();
?>
    <main class="main contact-main">
        <div class="container">
            <div class="contact-content">
                <h1 class="contact-title">Вход</h1>

                <?php if ($message): ?>
                    <div class="info-message" style="background-color: rgba(0,173,181,0.1); color: #00ADB5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message" style="background-color: rgba(255,107,107,0.1); color: #ff6b6b; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(255,107,107,0.3);">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form class="contact-form" method="POST" action="login.php" id="login-form" novalidate>
                    <div class="form-group">
                        <label for="username" class="form-label">Username или Email</label>
                        <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input <?php echo isset($errors['username']) ? 'error    ' : ''; ?>"
                                placeholder="username или email"
                                required
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        >
                        <?php if (isset($errors['username'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['username']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Пароль</label>
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
                    </div>

                    <button type="submit" class="send-btn">
                        Вход
                        <span class="btn-icon">→</span>
                    </button>

                    <p style="text-align: center; margin-top: 20px;">
                        Нету аккаунта? <a href="register.php">Регистрация</a>
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
echo '<script src="scripts/login.js"></script>';
include 'includes/footer.php';
?>