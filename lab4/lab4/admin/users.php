<?php
require_once '../includes/config.php';
require_once '../includes/db_connection.php';

// Проверка авторизации и прав админа
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=admin');
    exit;
}

if (!isAdmin()) {
    die('<div style="text-align: center; padding: 50px; font-family: Arial;">
        <h1 style="color: #ff6b6b;">Access Denied</h1>
        <p>Admin privileges required to access this page.</p>
        <a href="../index.php" style="color: #00ADB5;">Return to Home</a>
    </div>');
}

$db = Database::getInstance();
$page = 'admin';
$page_title = 'Admin - User Management';

// Обработка действий
$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        // Нельзя удалить самого себя
        if ($user_id != $_SESSION['user_id']) {
            $db->query("DELETE FROM users WHERE id = ?", [$user_id]);
            $_SESSION['message'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'You cannot delete yourself';
        }
        header('Location: users.php');
        exit;
    }

    if (isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'];
        $new_role = $_POST['role'];
        // Нельзя изменить свою роль
        if ($user_id != $_SESSION['user_id']) {
            $db->query("UPDATE users SET role = ? WHERE id = ?", [$new_role, $user_id]);
            $_SESSION['message'] = 'User role updated successfully';
        } else {
            $_SESSION['error'] = 'You cannot change your own role';
        }
        header('Location: users.php');
        exit;
    }

    if (isset($_POST['toggle_active'])) {
        $user_id = $_POST['user_id'];
        $current = $db->fetch("SELECT is_active FROM users WHERE id = ?", [$user_id]);
        $new_status = $current['is_active'] ? 0 : 1;
        $db->query("UPDATE users SET is_active = ? WHERE id = ?", [$new_status, $user_id]);
        $_SESSION['message'] = 'User status updated';
        header('Location: users.php');
        exit;
    }
}

// Получаем всех пользователей
$users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");

ob_start();
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
        <link rel="stylesheet" href="../styles/normalize.css">
        <link rel="stylesheet" href="../styles/style.css">
        <style>
            .admin-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }

            .admin-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
                padding: 20px;
                background: rgba(57,62,70,0.8);
                border-radius: 12px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
            }

            th, td {
                padding: 12px 15px;
                text-align: left;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }

            th {
                background: rgba(0,173,181,0.2);
                color: #00ADB5;
                font-weight: 600;
            }

            tr:hover {
                background: rgba(255,255,255,0.05);
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-label {
                display: block;
                margin-bottom: 8px;
                color: #eee;
                font-weight: 500;
            }

            .form-input {
                width: 100%;
                padding: 12px 15px;
                background: rgba(255,255,255,0.05);
                border: 1px solid rgba(255,255,255,0.1);
                border-radius: 6px;
                color: #eee;
                font-size: 16px;
            }

            select.form-input {
                cursor: pointer;
            }

            .send-btn {
                background: #00ADB5;
                color: white;
                border: none;
                padding: 12px 25px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 16px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                transition: all 0.3s ease;
            }

            .send-btn:hover {
                background: #0099a8;
            }

            .btn-icon {
                font-size: 18px;
            }

            .success-message {
                background-color: rgba(0,173,181,0.1);
                color: #00ADB5;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
                border: 1px solid rgba(0,173,181,0.3);
            }

            .error-message {
                background-color: rgba(255,107,107,0.1);
                color: #ff6b6b;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
                border: 1px solid rgba(255,107,107,0.3);
            }

            .contact-btn {
                display: inline-block;
                padding: 10px 20px;
                background: #393E46;
                color: #eee;
                text-decoration: none;
                border-radius: 6px;
                margin-left: 10px;
            }

            .contact-btn:hover {
                background: #454b55;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin-top: 30px;
            }

            .stat-box {
                background: rgba(0,0,0,0.2);
                padding: 20px;
                border-radius: 8px;
                text-align: center;
            }

            .stat-number {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .stat-label {
                color: #aaa;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
    <?php include '../includes/header.php'; ?>

    <main class="main">
        <div class="admin-container">
            <div class="admin-header">
                <h1 style="color: #00ADB5;">User Management</h1>
                <div>
                    <a href="index.php" class="contact-btn">← Dashboard</a>
                    <a href="logout.php" class="contact-btn" style="background-color: #ff6b6b;">Logout</a>
                </div>
            </div>

            <!-- Сообщения -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="success-message">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Таблица пользователей -->
            <div style="background: rgba(57,62,70,0.8); padding: 25px; border-radius: 12px; margin-bottom: 30px;">
                <h2 style="color: #00ADB5; margin-bottom: 20px;">All Users (<?php echo count($users); ?>)</h2>

                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span style="background: #00ADB5; color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: 5px;">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                            <td>
                                <form method="POST" action="users.php" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" onchange="if(this.value != '<?php echo $user['role']; ?>') { this.form.submit(); }"
                                            style="background: <?php echo $user['role'] == 'admin' ? 'rgba(0,173,181,0.2)' : 'rgba(57,62,70,0.5)'; ?>;
                                                color: <?php echo $user['role'] == 'admin' ? '#00ADB5' : '#ccc'; ?>;
                                                border: 1px solid <?php echo $user['role'] == 'admin' ? '#00ADB5' : '#555'; ?>;
                                                padding: 5px 10px; border-radius: 4px;"
                                        <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                        <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <input type="hidden" name="update_role" value="1">
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="users.php" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="toggle_active" value="1"
                                            style="background: <?php echo $user['is_active'] ? 'rgba(40,167,69,0.2)' : 'rgba(255,107,107,0.2)'; ?>;
                                                color: <?php echo $user['is_active'] ? '#28a745' : '#ff6b6b'; ?>;
                                                border: 1px solid <?php echo $user['is_active'] ? '#28a745' : '#ff6b6b'; ?>;
                                                padding: 5px 15px; border-radius: 4px; cursor: pointer;"
                                        <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </button>
                                </form>
                            </td>
                            <td style="color: #aaa; font-size: 14px;">
                                <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" action="users.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" value="1"
                                                style="background: rgba(255,107,107,0.2); color: #ff6b6b; border: 1px solid #ff6b6b; padding: 5px 15px; border-radius: 4px; cursor: pointer;">
                                            Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #888; font-style: italic;">Current user</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Статистика -->
            <div class="stats-grid">
                <div class="stat-box" style="border-top: 3px solid #00ADB5;">
                    <div class="stat-number"><?php echo count($users); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-box" style="border-top: 3px solid #28a745;">
                    <div class="stat-number">
                        <?php echo count(array_filter($users, fn($u) => $u['role'] == 'admin')); ?>
                    </div>
                    <div class="stat-label">Admins</div>
                </div>
                <div class="stat-box" style="border-top: 3px solid #ffc107;">
                    <div class="stat-number">
                        <?php echo count(array_filter($users, fn($u) => $u['is_active'])); ?>
                    </div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-box" style="border-top: 3px solid #ff6b6b;">
                    <div class="stat-number">
                        <?php echo count(array_filter($users, fn($u) => !$u['is_active'])); ?>
                    </div>
                    <div class="stat-label">Inactive Users</div>
                </div>
            </div>

            <!-- Форма добавления пользователя -->
            <div style="background: rgba(57,62,70,0.8); padding: 25px; border-radius: 12px; margin-top: 30px;">
                <h2 style="color: #00ADB5; margin-bottom: 20px;">Add New User</h2>
                <form method="POST" action="../register.php" class="contact-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-input">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="send-btn" style="margin-top: 20px;">
                        Add User
                        <span class="btn-icon">+</span>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    </body>
    </html>
<?php
$content = ob_get_clean();
echo $content;
?>