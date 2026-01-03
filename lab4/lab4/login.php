<?php
require_once 'includes/config.php';
$page = 'login';
$page_title = 'Login';

// Упрощенная аутентификация (для демо)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Демо-авторизация (в реальном проекте проверять из БД)
    if ($username === 'demo' && $password === 'demo') {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}

ob_start();
?>
    <main class="main contact-main">
        <div class="container">
            <div class="contact-content">
                <h1 class="contact-title">Login</h1>

                <?php if (isset($error)): ?>
                    <div class="error-message" style="background-color: rgba(255,0,0,0.1); color: #ff6b6b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form class="contact-form" method="POST" action="login.php">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input"
                                placeholder="Enter your username"
                                required
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
                        <small>Demo credentials: username: <strong>demo</strong>, password: <strong>demo</strong></small>
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