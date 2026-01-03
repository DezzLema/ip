<?php
require_once 'includes/config.php';
require_once 'includes/db_connection.php';

$page = 'login';
$page_title = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
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
            $_SESSION['user_role'] = $user['role']; // Добавляем роль в сессию

            // Редирект в админку если админ
            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        }
    }
}

ob_start();
?>
    <!-- HTML форма остается такой же -->
    <main class="main contact-main">
        <div class="container">
            <div class="contact-content">
                <h1 class="contact-title">Login</h1>

                <?php if ($error): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form class="contact-form" method="POST" action="login.php">
                    <div class="form-group">
                        <label for="username" class="form-label">Username or Email</label>
                        <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input"
                                placeholder="Enter your username or email"
                                required
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Enter your password"
                                required
                        >
                    </div>

                    <button type="submit" class="send-btn">
                        Login
                        <span class="btn-icon">→</span>
                    </button>

                    <p style="text-align: center; margin-top: 20px;">
                        Don't have an account? <a href="register.php">Register here</a>
                    </p>

                    <p style="text-align: center; margin-top: 10px;">
                        <small>Demo: username: <strong>demo</strong>, password: <strong>password</strong></small>
                    </p>
                </form>
            </div>
        </div>
    </main>
<?php
$content = ob_get_clean();
include 'includes/header.php';
echo $content;
include 'includes/footer.php';
?>