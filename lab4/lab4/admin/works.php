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
$page_title = 'Admin - Portfolio Works';

// Обработка действий
$action = $_GET['action'] ?? '';
$work_id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_work'])) {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_path = $_POST['image_path'] ?? '';
        $category = $_POST['category'] ?? '';

        $db->query(
            "INSERT INTO works (title, description, image_path, category) VALUES (?, ?, ?, ?)",
            [$title, $description, $image_path, $category]
        );
        $_SESSION['message'] = 'Work added successfully';
        header('Location: works.php');
        exit;
    }

    if (isset($_POST['update_work'])) {
        $work_id = $_POST['work_id'];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_path = $_POST['image_path'] ?? '';
        $category = $_POST['category'] ?? '';

        $db->query(
            "UPDATE works SET title = ?, description = ?, image_path = ?, category = ? WHERE id = ?",
            [$title, $description, $image_path, $category, $work_id]
        );
        $_SESSION['message'] = 'Work updated successfully';
        header('Location: works.php');
        exit;
    }

    if (isset($_POST['delete_work'])) {
        $work_id = $_POST['work_id'];
        $db->query("DELETE FROM works WHERE id = ?", [$work_id]);
        $_SESSION['message'] = 'Work deleted successfully';
        header('Location: works.php');
        exit;
    }

    if (isset($_POST['toggle_publish'])) {
        $work_id = $_POST['work_id'];
        $current = $db->fetch("SELECT is_published FROM works WHERE id = ?", [$work_id]);
        $new_status = $current['is_published'] ? 0 : 1;
        $db->query("UPDATE works SET is_published = ? WHERE id = ?", [$new_status, $work_id]);
        $_SESSION['message'] = 'Work status updated';
        header('Location: works.php');
        exit;
    }
}

// Получаем все работы
$works = $db->fetchAll("SELECT * FROM works ORDER BY created_at DESC");

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

            .form-input, .form-textarea {
                width: 100%;
                padding: 12px 15px;
                background: rgba(255,255,255,0.05);
                border: 1px solid rgba(255,255,255,0.1);
                border-radius: 6px;
                color: #eee;
                font-size: 16px;
            }

            .form-textarea {
                min-height: 100px;
                resize: vertical;
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
        </style>
    </head>
    <body>
    <?php include '../includes/header.php'; ?>

    <main class="main">
        <div class="admin-container">
            <div class="admin-header">
                <h1 style="color: #00ADB5;">Portfolio Works</h1>
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

            <!-- Таблица работ -->
            <div style="background: rgba(57,62,70,0.8); padding: 25px; border-radius: 12px; margin-bottom: 30px;">
                <h2 style="color: #00ADB5; margin-bottom: 20px;">All Works (<?php echo count($works); ?>)</h2>

                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($works as $work): ?>
                        <tr>
                            <td>#<?php echo $work['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($work['title']); ?></strong><br>
                                <small style="color: #888;"><?php echo substr(htmlspecialchars($work['description']), 0, 50); ?>...</small>
                            </td>
                            <td>
                                <span style="background: rgba(0,173,181,0.2); color: #00ADB5; padding: 3px 10px; border-radius: 12px; font-size: 13px;">
                                    <?php echo htmlspecialchars($work['category']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($work['image_path']): ?>
                                    <a href="../<?php echo $work['image_path']; ?>" target="_blank" style="color: #00ADB5; font-size: 13px;">View Image</a>
                                <?php else: ?>
                                    <span style="color: #888;">No image</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="works.php" style="display: inline;">
                                    <input type="hidden" name="work_id" value="<?php echo $work['id']; ?>">
                                    <button type="submit" name="toggle_publish" value="1"
                                            style="background: <?php echo $work['is_published'] ? 'rgba(40,167,69,0.2)' : 'rgba(255,107,107,0.2)'; ?>;
                                                color: <?php echo $work['is_published'] ? '#28a745' : '#ff6b6b'; ?>;
                                                border: 1px solid <?php echo $work['is_published'] ? '#28a745' : '#ff6b6b'; ?>;
                                                padding: 5px 15px; border-radius: 4px; cursor: pointer;">
                                        <?php echo $work['is_published'] ? 'Published' : 'Draft'; ?>
                                    </button>
                                </form>
                            </td>
                            <td style="color: #aaa; font-size: 14px;">
                                <?php echo date('d.m.Y', strtotime($work['created_at'])); ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <button onclick="editWork(<?php echo $work['id']; ?>)"
                                            style="background: rgba(0,173,181,0.2); color: #00ADB5; border: 1px solid #00ADB5; padding: 5px 15px; border-radius: 4px; cursor: pointer;">
                                        Edit
                                    </button>
                                    <form method="POST" action="works.php" style="display: inline;" onsubmit="return confirm('Delete this work?');">
                                        <input type="hidden" name="work_id" value="<?php echo $work['id']; ?>">
                                        <button type="submit" name="delete_work" value="1"
                                                style="background: rgba(255,107,107,0.2); color: #ff6b6b; border: 1px solid #ff6b6b; padding: 5px 15px; border-radius: 4px; cursor: pointer;">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Форма добавления/редактирования работы -->
            <div style="background: rgba(57,62,70,0.8); padding: 25px; border-radius: 12px;">
                <h2 style="color: #00ADB5; margin-bottom: 20px;" id="form-title">Add New Work</h2>
                <form method="POST" action="works.php" class="contact-form" id="work-form">
                    <input type="hidden" name="work_id" id="work_id" value="">

                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" id="title" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-textarea" rows="4"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Image Path</label>
                            <input type="text" name="image_path" id="image_path" class="form-input"
                                   placeholder="e.g., img/gal1.jpg">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" id="category" class="form-input"
                                   placeholder="e.g., Web Design">
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 20px;">
                        <button type="submit" name="add_work" id="add-btn" class="send-btn">
                            Add Work
                            <span class="btn-icon">+</span>
                        </button>
                        <button type="submit" name="update_work" id="update-btn" class="send-btn" style="display: none;">
                            Update Work
                            <span class="btn-icon">✓</span>
                        </button>
                        <button type="button" onclick="resetForm()" class="send-btn" style="background-color: #393E46;">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Данные работ для редактирования
        const worksData = <?php echo json_encode($works); ?>;

        function editWork(workId) {
            const work = worksData.find(w => w.id == workId);
            if (!work) return;

            document.getElementById('form-title').textContent = 'Edit Work';
            document.getElementById('work_id').value = work.id;
            document.getElementById('title').value = work.title;
            document.getElementById('description').value = work.description;
            document.getElementById('image_path').value = work.image_path || '';
            document.getElementById('category').value = work.category || '';

            document.getElementById('add-btn').style.display = 'none';
            document.getElementById('update-btn').style.display = 'inline-block';

            // Прокрутка к форме
            document.getElementById('work-form').scrollIntoView({ behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('form-title').textContent = 'Add New Work';
            document.getElementById('work-form').reset();
            document.getElementById('work_id').value = '';

            document.getElementById('add-btn').style.display = 'inline-block';
            document.getElementById('update-btn').style.display = 'none';
        }
    </script>

    <?php include '../includes/footer.php'; ?>
    <?php if (!empty($works)): ?>
        <script>
            // JavaScript для динамической навигации
            document.addEventListener('DOMContentLoaded', function() {
                const totalSlides = <?php echo count($works); ?>;

                if (totalSlides > 1) {
                    // Обновляем навигационные кнопки
                    const prevBtn = document.querySelector('.nav-btn.prev');
                    const nextBtn = document.querySelector('.nav-btn.next');

                    if (prevBtn && nextBtn) {
                        // Функция для получения текущего слайда
                        function getCurrentSlide() {
                            const radios = document.querySelectorAll('.gallery-radio');
                            for (let i = 0; i < radios.length; i++) {
                                if (radios[i].checked) {
                                    return i + 1;
                                }
                            }
                            return 1;
                        }

                        // Функция для обновления кнопок
                        function updateNavigation() {
                            const current = getCurrentSlide();

                            // Обновляем предыдущую кнопку
                            const prevSlide = current === 1 ? totalSlides : current - 1;
                            prevBtn.setAttribute('for', 'slide-' + prevSlide);
                            prevBtn.style.display = 'flex';

                            // Обновляем следующую кнопку
                            const nextSlide = current === totalSlides ? 1 : current + 1;
                            nextBtn.setAttribute('for', 'slide-' + nextSlide);
                            nextBtn.style.display = 'flex';
                        }

                        // Слушаем изменения радио-кнопок
                        document.querySelectorAll('.gallery-radio').forEach(radio => {
                            radio.addEventListener('change', updateNavigation);
                        });

                        // Инициализируем навигацию
                        updateNavigation();
                    }
                }
            });
        </script>
    <?php endif; ?>
    </body>
    </html>
<?php
$content = ob_get_clean();
echo $content;
?>