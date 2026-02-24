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
            $error = 'Invalid username/email or password';
        }
    }
}

ob_start();
?>
    <main class="main contact-main">
        <div class="container">
            <div class="contact-content">
                <h1 class="contact-title">Login</h1>

                <?php if ($message): ?>
                    <div class="info-message" style="background-color: rgba(0,173,181,0.1); color: #00ADB5; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form class="contact-form" method="POST" action="login.php" id="login-form" novalidate>
                    <div class="form-group">
                        <label for="username" class="form-label">Username or Email</label>
                        <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input <?php echo isset($errors['username']) ? 'error' : ''; ?>"
                                placeholder="Enter your username or email"
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
                        <label for="password" class="form-label">Password</label>
                        <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input <?php echo isset($errors['password']) ? 'error' : ''; ?>"
                                placeholder="Enter your password"
                                required
                        >
                        <?php if (isset($errors['password'])): ?>
                            <div class="error-text" style="color: #ff6b6b; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars($errors['password']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="send-btn">
                        Login
                        <span class="btn-icon">→</span>
                    </button>

                    <p style="text-align: center; margin-top: 20px;">
                        Don`t have an account? <a href="register.php">Register here</a>
                    </p>

                    <p style="text-align: center; margin-top: 10px;">
                        <small>Demo: username: <strong>demo</strong>, password: <strong>password</strong></small>
                    </p>
                </form>
            </div>
        </div>
    </main>

<?php
// клиентская валидация
$custom_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("login-form");
    const username = document.getElementById("username");
    const password = document.getElementById("password");
    
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
    
    // Валидация username/email
    username.addEventListener("input", function() {
        const value = username.value.trim();
        if (!value) {
            showError(username, "Username or email is required");
        } else if (value.length > 100) {
            showError(username, "Must not exceed 100 characters");
        } else {
            clearError(username);
        }
    });
    
    // Валидация пароля
    password.addEventListener("input", function() {
        const value = password.value;
        if (!value) {
            showError(password, "Password is required");
        } else if (value.length > 100) {
            showError(password, "Password must not exceed 100 characters");
        } else {
            clearError(password);
        }
    });
    
    // Финальная проверка перед отправкой
    form.addEventListener("submit", function(e) {
        let hasErrors = false;
        
        if (!username.value.trim()) {
            showError(username, "Username or email is required");
            hasErrors = true;
        }
        
        if (!password.value) {
            showError(password, "Password is required");
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

$content = ob_get_clean();
include 'includes/header.php';
echo $content;
echo $custom_scripts;
include 'includes/footer.php';
?>