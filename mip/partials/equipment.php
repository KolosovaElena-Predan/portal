<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Доступ запрещён');
}

// Обработка AJAX-запросов (ДОЛЖНО БЫТЬ ПЕРВЫМ!)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            // Добавление нового продукта
            $name = trim($_POST['name'] ?? '');
            $short_description = trim($_POST['short_description'] ?? '');
            $full_description = $_POST['full_description'] ?? '';
            $base_price = (float)($_POST['base_price'] ?? 0);
            $status = $_POST['status'] ?? 'active';

            if (empty($name)) {
                throw new Exception('Название обязательно');
            }

            $stmt = $pdo->prepare("INSERT INTO products (name, short_description, full_description, base_price, status)
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $short_description, $full_description, $base_price, $status]);
            $product_id = $pdo->lastInsertId();

            // Загрузка изображений
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = '../../img/products/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === 0) {
                        $filename = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                        $filepath = $upload_dir . $filename;

                        if (move_uploaded_file($tmp_name, $filepath)) {
                            $image_url = 'img/products/' . $filename;
                            $is_main = ($key === 0) ? 1 : 0;

                            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, ?)");
                            $stmt->execute([$product_id, $image_url, $is_main]);
                        }
                    }
                }
            }

            echo json_encode(['success' => true, 'product_id' => $product_id]);

        } elseif ($action === 'update') {
            // Обновление продукта
            $product_id = (int)$_POST['id'];

            $stmt = $pdo->prepare("UPDATE products SET name=?, short_description=?, full_description=?,
                                   base_price=?, status=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['short_description']),
                $_POST['full_description'],
                (float)$_POST['base_price'],
                $_POST['status'],
                $product_id
            ]);

            echo json_encode(['success' => true]);

        } elseif ($action === 'delete') {
            // Удаление продукта
            $product_id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);

            echo json_encode(['success' => true]);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit; // ВАЖНО: завершить скрипт после JSON-ответа
}

// Получаем все продукты (только для GET-запросов)
try {
    $stmt = $pdo->query("SELECT p.*,
                         COUNT(DISTINCT pi.id) as images_count,
                         COUNT(DISTINCT pc.id) as configs_count
                         FROM products p
                         LEFT JOIN product_images pi ON p.id = pi.product_id
                         LEFT JOIN product_configurations pc ON p.id = pc.product_id
                         GROUP BY p.id
                         ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
}
?>

<h2 class="admin-title">Продукция</h2>

<!-- Кнопка добавления -->
<div class="admin-header-actions">
    <button class="btn btn-primary" onclick="openAddModal()">+ Добавить продукт</button>
</div>

<!-- Таблица продуктов -->
<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Изображения</th>
            <th>Комплектации</th>
            <th>Цена от</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $product): ?>
        <tr data-id="<?= $product['id'] ?>">
            <td><?= $product['id'] ?></td>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td><?= $product['images_count'] ?> шт.</td>
            <td><?= $product['configs_count'] ?> шт.</td>
            <td><?= number_format($product['base_price'], 2, ',', ' ') ?> ₽</td>
            <td>
                <span class="status-badge status-<?= $product['status'] ?>">
                    <?= $product['status'] ?>
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-edit" onclick="editProduct(<?= $product['id'] ?>)">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-delete" onclick="deleteProduct(<?= $product['id'] ?>)">
                    <i class="fa fa-trash"></i>
                </button>
                <a href="../product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-view" target="_blank">
                    <i class="fa fa-eye"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Модальное окно добавления/редактирования -->
<div id="productModal" class="modal-overlay" style="display: none;">
    <div class="modal-content-large">
        <h3 id="modalTitle">Добавить продукт</h3>
        <form id="productForm" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="productId">

            <!-- Основная информация -->
            <div class="form-section">
                <h4>Основная информация</h4>
                <div class="form-row">
                    <input type="text" name="name" id="productName" class="input-field" placeholder="Название *" required>
                    <input type="number" name="base_price" id="productPrice" class="input-field" placeholder="Базовая цена *" step="0.01" required>
                </div>
                <input type="text" name="short_description" id="productShortDesc" class="input-field" placeholder="Краткое описание">
                <textarea name="full_description" id="productFullDesc" class="input-field tinymce" placeholder="Полное описание (HTML)" rows="5"></textarea>
                <select name="status" id="productStatus" class="input-field">
                    <option value="draft">Черновик</option>
                    <option value="active" selected>Активен</option>
                    <option value="inactive">Неактивен</option>
                </select>
            </div>

            <!-- Изображения -->
            <div class="form-section">
                <h4>Изображения</h4>
                <input type="file" name="images[]" class="input-field" multiple accept="image/*">
                <p class="form-hint">Первое изображение будет главным</p>
            </div>

            <!-- Комплектации -->
            <div class="form-section">
                <h4>Базовые комплектации</h4>
                <div id="configurationsList"></div>
                <button type="button" class="btn btn-secondary btn-small" onclick="addConfiguration()">+ Добавить комплектацию</button>
            </div>

            <!-- Модификации -->
            <div class="form-section">
                <h4>Дополнительные модификации</h4>
                <div id="modificationsList"></div>
                <button type="button" class="btn btn-secondary btn-small" onclick="addModification()">+ Добавить модификацию</button>
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn btn-cancel" onclick="closeModal()">Отмена</button>
                <button type="submit" class="btn btn-save">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<style>
.admin-header-actions {
    margin-bottom: 20px;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.admin-table th,
.admin-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.admin-table th {
    background: #f7f7fd;
    font-weight: 600;
    color: #1a1982;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}

.status-active { background: #d4f7d4; color: #008240; }
.status-inactive { background: #f8d7da; color: #721c24; }
.status-draft { background: #fff3cd; color: #856404; }

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    padding: 20px;
}

.modal-content-large {
    background: white;
    border-radius: 12px;
    padding: 30px;
    width: 100%;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
}

.form-section {
    margin-bottom: 25px;
    padding-bottom: 25px;
    border-bottom: 1px solid #e0e0e0;
}

.form-section h4 {
    font-size: 18px;
    color: #1a1982;
    margin-bottom: 15px;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.form-row .input-field {
    flex: 1;
}

.input-field {
    width: 100%;
    height: 50px;
    padding: 0 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    font-family: "Inter-Regular", sans-serif;
    outline: none;
    margin-bottom: 15px;
}

.input-field:focus {
    border-color: #1a1982;
    box-shadow: 0 0 0 3px rgba(26, 25, 130, 0.1);
}

textarea.input-field {
    height: auto;
    min-height: 120px;
    padding: 15px;
    resize: vertical;
}

.form-hint {
    font-size: 14px;
    color: #777;
    margin-top: -10px;
    margin-bottom: 15px;
}

.config-item,
.mod-item {
    background: #f7f7fd;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.modal-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-family: "Inter-Medium", sans-serif;
    transition: all 0.3s;
}

.btn-primary {
    background: #1a1982;
    color: white;
}

.btn-primary:hover {
    background: #14136b;
}

.btn-secondary {
    background: #e7e8f3;
    color: #1a1982;
}

.btn-small {
    padding: 8px 16px;
    font-size: 13px;
}

.btn-edit {
    background: #17a2b8;
    color: white;
}

.btn-delete {
    background: #d9534f;
    color: white;
}

.btn-view {
    background: #28a745;
    color: white;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.btn-cancel {
    background: #f8f9fa;
    border: 1px solid #ccc;
    color: #333;
}

.btn-save {
    background: #1a1982;
    color: white;
}
</style>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
<script>
// Инициализация TinyMCE
tinymce.init({
    selector: '.tinymce',
    height: 300,
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
});

let configIndex = 0;
let modIndex = 0;

// Открытие модального окна добавления
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Добавить продукт';
    document.getElementById('formAction').value = 'add';
    document.getElementById('productId').value = '';
    document.getElementById('productForm').reset();
    document.getElementById('configurationsList').innerHTML = '';
    document.getElementById('modificationsList').innerHTML = '';
    if (tinymce.get('productFullDesc')) {
        tinymce.get('productFullDesc').setContent('');
    }
    configIndex = 0;
    modIndex = 0;
    document.getElementById('productModal').style.display = 'flex';
}

// Закрытие модального окна
function closeModal() {
    document.getElementById('productModal').style.display = 'none';
}

// Отправка формы
document.getElementById('productForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    console.log('Форма отправляется...');

    const formData = new FormData(this);

    try {
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        console.log('Ответ сервера:', result);

        if (result.success) {
            alert('Продукт сохранён!');
            closeModal();
            loadSection('equipment');
        } else {
            alert('Ошибка: ' + (result.error || ''));
        }
    } catch (error) {
        console.error('Ошибка сети:', error);
        alert('Ошибка сети: ' + error.message);
    }
});

// Закрытие по клику вне окна
document.getElementById('productModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Динамическое добавление комплектаций
function addConfiguration() {
    const container = document.getElementById('configurationsList');
    const index = configIndex++;
    container.insertAdjacentHTML('beforeend', `
        <div class="config-item" data-index="${index}">
            <input type="text" name="configs[${index}][name]" placeholder="Название комплектации" class="input-field" required>
            <input type="number" name="configs[${index}][price]" placeholder="Цена" step="0.01" class="input-field" required>
            <textarea name="configs[${index}][characteristics]" placeholder='{"Характеристика": "Значение"}' class="input-field"></textarea>
            <button type="button" class="btn btn-delete btn-small" onclick="this.parentElement.remove()">Удалить</button>
        </div>
    `);
}

// Динамическое добавление модификаций
function addModification() {
    const container = document.getElementById('modificationsList');
    const index = modIndex++;
    container.insertAdjacentHTML('beforeend', `
        <div class="mod-item" data-index="${index}">
            <input type="text" name="mods[${index}][group_name]" placeholder="Группа (например: Цвет)" class="input-field" required>
            <textarea name="mods[${index}][options]" placeholder='[{"name": "Красный", "price": 100}, {"name": "Синий", "price": 150}]' class="input-field" required></textarea>
            <button type="button" class="btn btn-delete btn-small" onclick="this.parentElement.remove()">Удалить</button>
        </div>
    `);
}
</script>