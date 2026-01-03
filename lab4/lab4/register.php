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

    // Валидация
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
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

                <form class="contact-form" method="POST" action="register.php">
                    <div class="form-group">
                        <label for="username" class="form-label">Username *</label>
                        <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input"
                                placeholder="Choose a username"
                                required
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email *</label>
                        <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="Enter your email"
                                required
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                class="form-input"
                                placeholder="Enter your full name"
                                value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password *</label>
                        <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="At least 6 characters"
                                required
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input"
                                placeholder="Repeat your password"
                                required
                        >
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
$content = ob_get_clean();
include 'includes/header.php';
echo $content;
include 'includes/footer.php';
?>