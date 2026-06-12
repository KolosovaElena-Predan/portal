<?php
$activePage = 'products';
$pageTitle = 'Добавление товара | Админ-панель';
$summernote = true;
require_once 'includes/auth_check.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$success = '';
$productId = null;
$uploadBaseDir = __DIR__ . '/../mip/img/products/';
if (!file_exists($uploadBaseDir)) mkdir($uploadBaseDir, 0777, true);

// AJAX: Загрузка временных изображений
if (isset($_POST['ajax_upload'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => '', 'images' => []];
    if (!empty($_FILES['images']['name'][0])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;
        if (!isset($_SESSION['temp_product_images'])) $_SESSION['temp_product_images'] = [];
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileType = $_FILES['images']['type'][$key];
                $fileSize = $_FILES['images']['size'][$key];
                if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                    $extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $fileName = 'temp_' . uniqid() . '.' . $extension;
                    $filePath = $uploadBaseDir . $fileName;
                    $dbPath = 'img/products/' . $fileName;
                    if (move_uploaded_file($tmpName, $filePath)) {
                        $tempId = 'temp_' . uniqid();
                        $_SESSION['temp_product_images'][] = ['temp_id' => $tempId, 'file_name' => $fileName, 'image_url' => $dbPath];
                        $response['images'][] = ['temp_id' => $tempId, 'image_url' => $dbPath];
                    }
                }
            }
        }
        $response['success'] = true;
        $response['message'] = 'Загружено: ' . count($response['images']);
    }
    echo json_encode($response);
    exit;
}

// AJAX: Удаление временного изображения
if (isset($_POST['ajax_remove'])) {
    header('Content-Type: application/json');
    $tempId = $_POST['temp_id'] ?? '';
    if (isset($_SESSION['temp_product_images'])) {
        foreach ($_SESSION['temp_product_images'] as $key => $img) {
            if ($img['temp_id'] === $tempId) {
                $filePath = $uploadBaseDir . $img['file_name'];
                if (file_exists($filePath)) unlink($filePath);
                unset($_SESSION['temp_product_images'][$key]);
                $_SESSION['temp_product_images'] = array_values($_SESSION['temp_product_images']);
                echo json_encode(['success' => true]);
                exit;
            }
        }
    }
    echo json_encode(['success' => false]);
    exit;
}

// Обработка сохранения товара
if (isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);
    $base_price = (float)($_POST['base_price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $status = $_POST['status'] ?? 'draft';
    $short_description = trim($_POST['short_description'] ?? '');
    $full_description = trim($_POST['full_description'] ?? '');
    $is_new = isset($_POST['is_new']) && $_POST['is_new'] == '1' ? 1 : 0;
    $is_slider = isset($_POST['is_slider']) && $_POST['is_slider'] == '1' ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $mainImageTempId = $_POST['main_image_temp_id'] ?? null;
    
    // НОВЫЕ ПОЛЯ для базового товара
    $is_made_to_order = isset($_POST['is_made_to_order']) && $_POST['is_made_to_order'] == '1' ? 1 : 0;
    $lead_time = trim($_POST['lead_time'] ?? '14-21 дней');
    
    // Если товар под заказ, принудительно ставим stock = -1
    if ($is_made_to_order) $stock = -1;

    if (!$name) {
        $error = 'Название обязательно';
    } elseif ($base_price < 0) {
        $error = 'Цена не может быть отрицательной';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Сохраняем товар (с новыми полями)
            $stmt = $pdo->prepare("INSERT INTO products (category_id, name, short_description, full_description, base_price, stock, is_made_to_order, lead_time, is_new, is_slider, sort_order, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$category_id, $name, $short_description, $full_description, $base_price, $stock, $is_made_to_order, $lead_time, $is_new, $is_slider, $sort_order, $status]);
            $productId = $pdo->lastInsertId();

            // 2. Сохраняем изображения
            if (!empty($_SESSION['temp_product_images'])) {
                foreach ($_SESSION['temp_product_images'] as $idx => $tempImg) {
                    $isMain = ($tempImg['temp_id'] === $mainImageTempId) ? 1 : 0;
                    $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_main, sort_order) VALUES (?, ?, ?, ?)")->execute([$productId, $tempImg['image_url'], $isMain, $idx]);
                }
                unset($_SESSION['temp_product_images']);
            }

            // 3. Сохраняем файлы
            $fileDir = __DIR__ . '/../mip/files/products/';
            if (!file_exists($fileDir)) mkdir($fileDir, 0777, true);
            if (!empty($_FILES['product_files']['name'][0])) {
                foreach ($_FILES['product_files']['tmp_name'] as $k => $tmp) {
                    if ($_FILES['product_files']['error'][$k] === UPLOAD_ERR_OK) {
                        $fName = uniqid() . '_' . basename($_FILES['product_files']['name'][$k]);
                        if (move_uploaded_file($tmp, $fileDir . $fName)) {
                            $pdo->prepare("INSERT INTO product_files (product_id, group_name, file_name, file_url, file_size, sort_order) VALUES (?, ?, ?, ?, ?, ?)")->execute([
                                $productId, trim($_POST['file_group'][$k] ?? 'Документация'), $_FILES['product_files']['name'][$k], 'files/products/' . $fName, $_FILES['product_files']['size'][$k], (int)($_POST['file_sort'][$k] ?? 0)
                            ]);
                        }
                    }
                }
            }

            // 4. Сохраняем схемы
            $schemeDir = __DIR__ . '/../mip/img/schemes/';
            if (!file_exists($schemeDir)) mkdir($schemeDir, 0777, true);
            if (!empty($_FILES['scheme_images']['name'][0])) {
                foreach ($_FILES['scheme_images']['tmp_name'] as $k => $tmp) {
                    if ($_FILES['scheme_images']['error'][$k] === UPLOAD_ERR_OK) {
                        $sName = uniqid() . '_' . basename($_FILES['scheme_images']['name'][$k]);
                        if (move_uploaded_file($tmp, $schemeDir . $sName)) {
                            $pdo->prepare("INSERT INTO product_schemes (product_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?)")->execute([
                                $productId, trim($_POST['scheme_title'][$k] ?? ''), trim($_POST['scheme_description'][$k] ?? ''), 'img/schemes/' . $sName, (int)($_POST['scheme_sort'][$k] ?? 0)
                            ]);
                        }
                    }
                }
            }

            // 5. Сохраняем комплектации (С НОВЫМИ ПОЛЯМИ В JSON)
            if (!empty($_POST['config_name'])) {
                foreach ($_POST['config_name'] as $k => $cName) {
                    if (trim($cName) !== '') {
                        $cPrice = (float)($_POST['config_price'][$k] ?? 0);
                        $cCharsText = trim($_POST['config_chars'][$k] ?? '');
                        $cSort = (int)($_POST['config_sort'][$k] ?? 0);
                        $cMain = isset($_POST['config_main'][$k]) && $_POST['config_main'][$k] == '1' ? 1 : 0;
                        
                        // НОВЫЕ ПОЛЯ для комплектации
                        $cStock = (int)($_POST['config_stock'][$k] ?? 0);
                        $cIsMadeToOrder = isset($_POST['config_made_to_order'][$k]) && $_POST['config_made_to_order'][$k] == '1' ? 1 : 0;
                        $cLeadTime = trim($_POST['config_lead_time'][$k] ?? '');
                        
                        if ($cIsMadeToOrder) $cStock = -1;
                        
                        $charsArray = [];
                        if ($cCharsText) {
                            foreach (explode("\n", $cCharsText) as $line) {
                                $parts = explode(':', trim($line), 2);
                                if (count($parts) === 2) $charsArray[trim($parts[0])] = trim($parts[1]);
                            }
                        }
                        // Добавляем новые поля в JSON
                        $charsArray['stock'] = $cStock;
                        $charsArray['made_to_order'] = (bool)$cIsMadeToOrder;
                        if ($cLeadTime) $charsArray['lead_time'] = $cLeadTime;
                        
                        $charsJson = json_encode($charsArray, JSON_UNESCAPED_UNICODE);
                        $pdo->prepare("INSERT INTO product_configurations (product_id, name, price, characteristics, sort_order, is_main) VALUES (?, ?, ?, ?, ?, ?)")->execute([
                            $productId, $cName, $cPrice, $charsJson, $cSort, $cMain
                        ]);
                    }
                }
            }

            // 6. Сохраняем модификации
            if (!empty($_POST['mod_group_name'])) {
                foreach ($_POST['mod_group_name'] as $k => $mGroup) {
                    if (trim($mGroup) !== '') {
                        $mSort = (int)($_POST['mod_sort'][$k] ?? 0);
                        $variants = [];
                        if (!empty($_POST['mod_variants'][$k]) && is_array($_POST['mod_variants'][$k])) {
                            foreach ($_POST['mod_variants'][$k] as $v) {
                                if (!empty($v['name'])) {
                                    $props = [];
                                    if (!empty($v['properties']) && is_array($v['properties'])) {
                                        foreach ($v['properties'] as $p) {
                                            if (!empty($p['name']) && isset($p['price'])) $props[] = ['name' => $p['name'], 'price' => (float)$p['price']];
                                        }
                                    }
                                    $variants[] = ['name' => $v['name'], 'price' => (float)($v['price'] ?? 0), 'description' => $v['description'] ?? '', 'properties' => $props];
                                }
                            }
                        }
                        $pdo->prepare("INSERT INTO product_modifications (product_id, group_name, options, sort_order) VALUES (?, ?, ?, ?)")->execute([
                            $productId, $mGroup, json_encode($variants, JSON_UNESCAPED_UNICODE), $mSort
                        ]);
                    }
                }
            }

            $pdo->commit();
            $success = 'Товар добавлен! ID: ' . $productId;
            
            if (isset($_POST['save']) && $_POST['save'] === 'open_preview' && $productId) {
                header('Location: ../mip/product.php?id=' . $productId);
                exit;
            }
            $_POST = [];
            $_SESSION['temp_product_images'] = [];
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

$categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll();
$tempImages = $_SESSION['temp_product_images'] ?? [];
$mainImageTempId = $_POST['main_image_temp_id'] ?? ($tempImages[0]['temp_id'] ?? null);

$formData = [
    'name' => $_POST['name'] ?? '', 'category_id' => $_POST['category_id'] ?? 1,
    'base_price' => $_POST['base_price'] ?? 0, 'stock' => $_POST['stock'] ?? 0,
    'short_description' => $_POST['short_description'] ?? '', 'full_description' => $_POST['full_description'] ?? '',
    'is_new' => $_POST['is_new'] ?? 0, 'is_slider' => $_POST['is_slider'] ?? 0,
    'sort_order' => $_POST['sort_order'] ?? 0, 'status' => $_POST['status'] ?? 'draft',
    'is_made_to_order' => $_POST['is_made_to_order'] ?? 0, 'lead_time' => $_POST['lead_time'] ?? '14-21 дней',
];

$pageScript = '$(".summernote").summernote({ height: 300, lang: "ru-RU" });';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
<div class="content-header"><div class="container-fluid"><h1 class="m-0">Добавление нового товара</h1></div></div>
<section class="content"><div class="container-fluid">
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<?php if ($success && $productId): ?><div class="alert alert-success"><?= e($success) ?> <a href="../mip/product.php?id=<?= $productId ?>" target="_blank">Открыть</a></div><?php endif; ?>

<form method="POST" class="card" id="productForm" enctype="multipart/form-data">
<input type="hidden" name="main_image_temp_id" id="mainImageTempId" value="<?= e($mainImageTempId ?? '') ?>">
<div class="card-body">
    <div class="row">
        <div class="col-md-8">
            <div class="form-section"><label>Название *</label><input type="text" name="name" class="form-control" value="<?= e($formData['name']) ?>" required></div>
            <div class="form-section"><label>Краткое описание</label><textarea name="short_description" class="form-control" rows="3"><?= e($formData['short_description']) ?></textarea></div>
            <div class="form-section"><label>Полное описание</label><textarea name="full_description" class="form-control summernote" rows="10"><?= e($formData['full_description']) ?></textarea></div>
        </div>
        <div class="col-md-4">
            <div class="form-section"><label>Категория</label><select name="category_id" class="form-control"><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id'] == $formData['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
            <div class="form-section"><label>Цена (₽)</label><input type="number" step="0.01" min="0" name="base_price" class="form-control" value="<?= $formData['base_price'] ?>"></div>
            
            <div class="form-section">
                <label>Остаток на складе</label>
                <input type="number" min="-1" name="stock" id="base_stock" class="form-control" value="<?= $formData['stock'] ?>" <?= $formData['is_made_to_order'] ? 'disabled' : '' ?>>
                <small class="text-muted">-1 = только под заказ</small>
            </div>
            
            <div class="form-section">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_made_to_order" id="base_made_to_order" value="1" <?= $formData['is_made_to_order'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="base_made_to_order"><i class="fas fa-industry"></i> Только под заказ (без листа ожидания)</label>
                </div>
                <label>Срок изготовления</label>
                <input type="text" name="lead_time" class="form-control" value="<?= e($formData['lead_time']) ?>" placeholder="Напр: 14-21 дней">
            </div>
            
            <div class="form-section"><label>Статус</label><select name="status" class="form-control"><option value="draft" <?= $formData['status'] === 'draft' ? 'selected' : '' ?>>Черновик</option><option value="active" <?= $formData['status'] === 'active' ? 'selected' : '' ?>>Активен</option><option value="inactive" <?= $formData['status'] === 'inactive' ? 'selected' : '' ?>>Неактивен</option></select></div>
            <div class="form-section"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_new" id="is_new" value="1" <?= $formData['is_new'] ? 'checked' : '' ?>><label class="form-check-label" for="is_new">Новинка</label></div><div class="form-check"><input class="form-check-input" type="checkbox" name="is_slider" id="is_slider" value="1" <?= $formData['is_slider'] ? 'checked' : '' ?>><label class="form-check-label" for="is_slider">В слайдере</label></div></div>
        </div>
    </div>

    <!-- Изображения -->
    <div class="row mt-4"><div class="col-12"><h5>Изображения (<span id="imageCount"><?= count($tempImages) ?></span>)</h5><div class="row" id="imagesContainer"><?php if (!empty($tempImages)): ?><?php foreach ($tempImages as $img): ?><div class="col-md-3 mb-3 image-card" data-temp-id="<?= e($img['temp_id']) ?>"><div class="card"><img src="../mip/<?= e($img['image_url']) ?>" class="card-img-top" style="height:150px;object-fit:cover;"><div class="card-body p-2"><div class="form-check mb-2"><input type="radio" name="main_image_radio" class="form-check-input main-radio" value="<?= e($img['temp_id']) ?>" id="main_<?= e($img['temp_id']) ?>_<?= uniqid() ?>" <?= ($img['temp_id'] === $mainImageTempId) ? 'checked' : '' ?>><label class="form-check-label" for="main_<?= e($img['temp_id']) ?>_<?= uniqid() ?>"> Главное</label></div><button type="button" class="btn btn-sm btn-danger btn-block remove-image" data-temp-id="<?= e($img['temp_id']) ?>"><i class="fas fa-trash"></i></button></div></div></div><?php endforeach; ?><?php else: ?><div class="col-12"><p class="text-muted">Изображений нет</p></div><?php endif; ?></div><div class="form-group mt-3"><label>Добавить изображения</label><input type="file" name="images[]" id="imageInput" class="form-control" multiple accept="image/*"><div id="uploadProgress" class="mt-2" style="display:none;"><div class="spinner-border spinner-border-sm text-primary"></div> Загрузка...</div></div></div></div>

    <!-- Файлы -->
    <div class="row mt-4"><div class="col-12"><h5>Файлы</h5><div id="filesContainer"><div class="file-entry mb-3 p-3 border rounded"><div class="row align-items-end"><div class="col-md-4"><label>Группа</label><input type="text" name="file_group[]" class="form-control" value="Документация"></div><div class="col-md-5"><label>Файл</label><input type="file" name="product_files[]" class="form-control" accept=".pdf,.doc,.docx,.zip"></div><div class="col-md-2"><label>Порядок</label><input type="number" name="file_sort[]" class="form-control" value="0" min="0"></div><div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-file-btn">✕</button></div></div></div></div><button type="button" class="btn btn-secondary btn-sm" id="addFileBtn"><i class="fas fa-plus"></i> Добавить файл</button></div></div>

    <!-- Схемы -->
    <div class="row mt-4"><div class="col-12"><h5>Схемы</h5><div id="schemesContainer"><div class="scheme-entry mb-3 p-3 border rounded"><div class="row"><div class="col-md-4"><label>Название</label><input type="text" name="scheme_title[]" class="form-control" placeholder="Схема подключения"></div><div class="col-md-4"><label>Изображение</label><input type="file" name="scheme_images[]" class="form-control" accept="image/*"></div><div class="col-md-3"><label>Порядок</label><input type="number" name="scheme_sort[]" class="form-control" value="0" min="0"></div><div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-scheme-btn">✕</button></div></div><div class="mt-2"><label>Описание</label><textarea name="scheme_description[]" class="form-control" rows="2" placeholder="Краткое пояснение"></textarea></div></div></div><button type="button" class="btn btn-secondary btn-sm" id="addSchemeBtn"><i class="fas fa-plus"></i> Добавить схему</button></div></div>

    <!-- Комплектации -->
    <div class="row mt-4"><div class="col-12"><h5>Комплектации</h5><div id="configsContainer"><?php if (!empty($_POST['config_name'])): ?><?php foreach ($_POST['config_name'] as $k => $cName): ?><div class="config-entry mb-3 p-3 border rounded"><div class="row"><div class="col-md-3"><label>Название</label><input type="text" name="config_name[]" class="form-control" value="<?= e($cName) ?>"></div><div class="col-md-2"><label>Цена (₽)</label><input type="number" step="0.01" name="config_price[]" class="form-control" value="<?= (float)($_POST['config_price'][$k] ?? 0) ?>"></div><div class="col-md-2"><label>Остаток</label><input type="number" min="-1" name="config_stock[]" class="form-control config-stock" value="<?= (int)($_POST['config_stock'][$k] ?? 0) ?>" <?= (isset($_POST['config_made_to_order'][$k]) && $_POST['config_made_to_order'][$k]) ? 'disabled' : '' ?>></div><div class="col-md-3"><label>Характеристики</label><textarea name="config_chars[]" class="form-control" rows="2"><?= e($_POST['config_chars'][$k] ?? '') ?></textarea></div><div class="col-md-1"><label>Порядок</label><input type="number" name="config_sort[]" class="form-control" value="<?= (int)($_POST['config_sort'][$k] ?? 0) ?>" min="0"></div><div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-config-btn">✕</button></div></div><div class="row mt-2"><div class="col-md-4"><div class="form-check"><input class="form-check-input config-made-to-order" type="checkbox" name="config_made_to_order[]" value="1" <?= isset($_POST['config_made_to_order'][$k]) && $_POST['config_made_to_order'][$k] ? 'checked' : '' ?>><label class="form-check-label"><i class="fas fa-industry"></i> Только под заказ</label></div></div><div class="col-md-4"><label>Срок изготовления</label><input type="text" name="config_lead_time[]" class="form-control" value="<?= e($_POST['config_lead_time'][$k] ?? '') ?>" placeholder="Напр: 30 дней"></div><div class="col-md-2"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="config_main[]" value="1" <?= isset($_POST['config_main'][$k]) && $_POST['config_main'][$k] ? 'checked' : '' ?>><label class="form-check-label">Основная</label></div></div></div></div><?php endforeach; ?><?php else: ?><div class="config-entry mb-3 p-3 border rounded"><div class="row"><div class="col-md-3"><label>Название</label><input type="text" name="config_name[]" class="form-control" placeholder="Базовая"></div><div class="col-md-2"><label>Цена (₽)</label><input type="number" step="0.01" name="config_price[]" class="form-control" value="0"></div><div class="col-md-2"><label>Остаток</label><input type="number" min="-1" name="config_stock[]" class="form-control config-stock" value="0"></div><div class="col-md-3"><label>Характеристики</label><textarea name="config_chars[]" class="form-control" rows="2" placeholder="Мощность: 1000 Вт&#10;Напряжение: 220В"></textarea></div><div class="col-md-1"><label>Порядок</label><input type="number" name="config_sort[]" class="form-control" value="0" min="0"></div><div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-config-btn">✕</button></div></div><div class="row mt-2"><div class="col-md-4"><div class="form-check"><input class="form-check-input config-made-to-order" type="checkbox" name="config_made_to_order[]" value="1"><label class="form-check-label"><i class="fas fa-industry"></i> Только под заказ</label></div></div><div class="col-md-4"><label>Срок изготовления</label><input type="text" name="config_lead_time[]" class="form-control" placeholder="Напр: 30 дней"></div><div class="col-md-2"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="config_main[]" value="1"><label class="form-check-label">Основная</label></div></div></div></div><?php endif; ?></div><button type="button" class="btn btn-secondary btn-sm" id="addConfigBtn"><i class="fas fa-plus"></i> Добавить комплектацию</button></div></div>

    <!-- Модификации -->
    <div class="row mt-4"><div class="col-12"><h5>Модификации</h5><div id="modsContainer"><div class="mod-entry mb-3 p-3 border rounded" data-gidx="0"><div class="row"><div class="col-md-4"><label>Название группы</label><input type="text" name="mod_group_name[]" class="form-control" placeholder="Количество входов"></div><div class="col-md-2"><label>Порядок</label><input type="number" name="mod_sort[]" class="form-control" value="0" min="0"></div><div class="col-md-6 text-right"><button type="button" class="btn btn-success btn-sm add-variant-btn" data-gidx="0"><i class="fas fa-plus"></i> Вариант</button></div></div><div class="variants-container mt-3"></div><button type="button" class="btn btn-secondary btn-sm mt-2 add-variant-btn" data-gidx="0"><i class="fas fa-plus"></i> Добавить вариант</button></div></div><button type="button" class="btn btn-secondary btn-sm" id="addModGroupBtn"><i class="fas fa-plus"></i> Добавить группу</button><small class="text-muted d-block mt-1">Модификации всегда оформляются под заказ (без листа ожидания)</small></div></div>
</div>
<div class="card-footer"><button type="submit" name="save" value="default" class="btn btn-primary"><i class="fas fa-plus"></i> Добавить товар</button><button type="submit" name="save" value="open_preview" formnovalidate class="btn btn-success"><i class="fas fa-external-link-alt"></i> Сохранить и открыть</button><a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Отмена</a></div>
</form>
</div></section></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addFileBtn')?.addEventListener('click', () => cloneField('#filesContainer', '.file-entry'));
    document.getElementById('addSchemeBtn')?.addEventListener('click', () => cloneField('#schemesContainer', '.scheme-entry'));
    
    // Клонирование комплектаций с новыми полями
    document.getElementById('addConfigBtn')?.addEventListener('click', () => {
        const c = document.querySelector('#configsContainer');
        const first = c.querySelector('.config-entry');
        if(!first) return;
        const f = first.cloneNode(true);
        f.querySelectorAll('input,textarea').forEach(i => {
            if(i.type==='file') i.value = '';
            else if(i.type==='checkbox') i.checked = false;
            else if(i.type!=='hidden') i.value = i.type==='number' ? '0' : '';
        });
        // Разблокируем поле остатка
        const stockInput = f.querySelector('.config-stock');
        if(stockInput) stockInput.disabled = false;
        c.appendChild(f);
    });

    document.body.addEventListener('click', function(e) {
        if(e.target.closest('.remove-file-btn')) e.target.closest('.file-entry')?.remove();
        if(e.target.closest('.remove-scheme-btn')) e.target.closest('.scheme-entry')?.remove();
        if(e.target.closest('.remove-config-btn')) e.target.closest('.config-entry')?.remove();
        if(e.target.closest('.remove-variant-btn')) e.target.closest('.variant-item')?.remove();
        if(e.target.closest('.remove-prop-btn')) e.target.closest('.prop-item')?.remove();
    });

    document.body.addEventListener('click', function(e) {
        if(e.target.closest('.add-variant-btn')) addVariant(e.target.closest('.add-variant-btn'));
        if(e.target.closest('.add-prop-btn')) addProperty(e.target.closest('.add-prop-btn'));
    });
    
    document.getElementById('addModGroupBtn')?.addEventListener('click', function() {
        const c = document.getElementById('modsContainer'), idx = c.querySelectorAll('.mod-entry').length;
        const f = document.querySelector('.mod-entry').cloneNode(true);
        f.querySelectorAll('input').forEach(i => i.value = i.type==='number' ? '0' : '');
        f.querySelectorAll('.variants-container,.properties-list').forEach(d => d.innerHTML = '');
        f.dataset.gidx = idx;
        f.querySelectorAll('.add-variant-btn').forEach(btn => btn.dataset.gidx = idx);
        c.appendChild(f);
    });
    
    // Обработка чекбокса "Только под заказ" для базового товара
    document.getElementById('base_made_to_order')?.addEventListener('change', function() {
        const stockInput = document.getElementById('base_stock');
        if (this.checked) {
            stockInput.value = -1;
            stockInput.disabled = true;
        } else {
            stockInput.value = 0;
            stockInput.disabled = false;
        }
    });
    
    // Обработка чекбокса "Только под заказ" для комплектаций (делегирование)
    document.body.addEventListener('change', function(e) {
        if (e.target.classList.contains('config-made-to-order')) {
            const entry = e.target.closest('.config-entry');
            const stockInput = entry.querySelector('.config-stock');
            if (e.target.checked) {
                stockInput.value = -1;
                stockInput.disabled = true;
            } else {
                stockInput.value = 0;
                stockInput.disabled = false;
            }
        }
    });
    
    initImageHandlers();
});

function cloneField(containerSel, itemSel) {
    const c = document.querySelector(containerSel), f = c.querySelector(itemSel).cloneNode(true);
    f.querySelectorAll('input,textarea').forEach(i => {
        if(i.type==='checkbox' || i.type==='radio') { i.checked = false; }
        else { i.value = i.type==='number' ? '0' : ''; }
        if(i.id) i.id = i.id + '_' + Date.now() + '_' + Math.random().toString(36).substr(2,5);
    });
    f.querySelectorAll('label').forEach(l => { if(l.htmlFor) l.htmlFor = l.htmlFor + '_' + Date.now() + '_' + Math.random().toString(36).substr(2,5); });
    c.appendChild(f);
}

function addVariant(btn) {
    const entry = btn.closest('.mod-entry'), container = entry.querySelector('.variants-container');
    const gidx = entry.dataset.gidx || 0, vidx = container.querySelectorAll('.variant-item').length;
    const html = `<div class="variant-item card mb-2"><div class="card-body"><div class="row align-items-end"><div class="col-md-3"><label>Название *</label><input type="text" name="mod_variants[${gidx}][${vidx}][name]" class="form-control form-control-sm" required></div><div class="col-md-2"><label>Цена</label><input type="number" step="0.01" name="mod_variants[${gidx}][${vidx}][price]" class="form-control form-control-sm" value="0"></div><div class="col-md-4"><label>Описание</label><input type="text" name="mod_variants[${gidx}][${vidx}][description]" class="form-control form-control-sm"></div><div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-variant-btn">✕</button></div><div class="col-md-1"><button type="button" class="btn btn-sm btn-secondary add-prop-btn">+ Свойство</button></div></div><div class="properties-list mt-2"></div></div></div>`;
    container.insertAdjacentHTML('beforeend', html);
}
function addProperty(btn) {
    const variant = btn.closest('.variant-item'), list = variant.querySelector('.properties-list');
    const gidx = variant.closest('.mod-entry').dataset.gidx || 0;
    const vidx = Array.from(variant.parentNode.children).indexOf(variant);
    const pidx = list.querySelectorAll('.prop-item').length;
    const html = `<div class="prop-item input-group input-group-sm mb-1"><input type="text" name="mod_variants[${gidx}][${vidx}][properties][${pidx}][name]" class="form-control" placeholder="Свойство"><input type="number" step="0.01" name="mod_variants[${gidx}][${vidx}][properties][${pidx}][price]" class="form-control" style="width:90px" placeholder="Наценка"><div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-prop-btn">✕</button></div></div>`;
    list.insertAdjacentHTML('beforeend', html);
}

function initImageHandlers() {
    const imageInput = document.getElementById('imageInput');
    const imagesContainer = document.getElementById('imagesContainer');
    const imageCount = document.getElementById('imageCount');
    const uploadProgress = document.getElementById('uploadProgress');
    const mainImageTempIdInput = document.getElementById('mainImageTempId');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const fd = new FormData(); fd.append('ajax_upload', '1');
                for (let f of this.files) fd.append('images[]', f);
                uploadProgress.style.display = 'block'; this.disabled = true;
                fetch('product_add.php', {method:'POST', body:fd})
                .then(r => r.json()).then(data => {
                    uploadProgress.style.display = 'none'; this.disabled = false; this.value = '';
                    if (data.success && data.images.length) {
                        const m = imagesContainer.querySelector('.text-muted'); if (m) m.remove();
                        data.images.forEach((img, i) => {
                            const isFirst = document.querySelectorAll('.image-card').length === 0;
                            const radioId = 'main_' + img.temp_id + '_' + Date.now() + '_' + i;
                            const html = `<div class="col-md-3 mb-3 image-card" data-temp-id="${img.temp_id}"><div class="card"><img src="../mip/${img.image_url}" class="card-img-top" style="height:150px;object-fit:cover;"><div class="card-body p-2"><div class="form-check mb-2"><input type="radio" name="main_image_radio" class="form-check-input main-radio" value="${img.temp_id}" id="${radioId}" ${isFirst ? 'checked' : ''}><label class="form-check-label" for="${radioId}"> Главное</label></div><button type="button" class="btn btn-sm btn-danger btn-block remove-image" data-temp-id="${img.temp_id}"><i class="fas fa-trash"></i></button></div></div></div>`;
                            imagesContainer.insertAdjacentHTML('beforeend', html);
                        });
                        imageCount.textContent = document.querySelectorAll('.image-card').length;
                        if (document.querySelectorAll('.image-card').length === 1) {
                            const firstRadio = document.querySelector('.main-radio');
                            if (firstRadio) { mainImageTempIdInput.value = firstRadio.value; firstRadio.checked = true; }
                        }
                    }
                }).catch(err => { uploadProgress.style.display='none'; this.disabled=false; alert('❌ Ошибка: '+err); });
            }
        });
    }
    imagesContainer?.addEventListener('click', function(e) {
        if (e.target.closest('.remove-image')) {
            const btn = e.target.closest('.remove-image'), tid = btn.dataset.tempId;
            fetch('product_add.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_remove=1&temp_id='+encodeURIComponent(tid)})
            .then(r => r.json()).then(data => {
                if (data.success) {
                    const card = btn.closest('.image-card');
                    const wasChecked = card.querySelector('.main-radio')?.checked;
                    card.remove();
                    imageCount.textContent = document.querySelectorAll('.image-card').length;
                    if (wasChecked && document.querySelector('.main-radio')) {
                        const first = document.querySelector('.main-radio');
                        first.checked = true;
                        mainImageTempIdInput.value = first.value;
                    } else if (!document.querySelector('.image-card')) {
                        mainImageTempIdInput.value = '';
                        imagesContainer.innerHTML = '<div class="col-12"><p class="text-muted">Изображений нет</p></div>';
                    }
                }
            });
        }
    });
    imagesContainer?.addEventListener('change', function(e) {
        if (e.target.classList.contains('main-radio')) {
            mainImageTempIdInput.value = e.target.value;
        }
    });
}
</script>
<?php require_once 'includes/footer.php'; ?>