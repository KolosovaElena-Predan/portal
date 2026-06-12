<?php
$activePage = 'products';
$pageTitle = 'Редактирование товара | Админ-панель';
$summernote = true;
require_once 'includes/auth_check.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../authorization.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$success = '';
$productId = (int)($_GET['id'] ?? 0);

if ($productId <= 0) { 
    header('Location: products.php'); 
    exit; 
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) { 
    header('Location: products.php'); 
    exit; 
}

$uploadBaseDir = __DIR__ . '/../mip/img/products/';
if (!file_exists($uploadBaseDir)) {
    mkdir($uploadBaseDir, 0777, true);
}

// ============================================
// ФУНКЦИЯ ОТПРАВКИ УВЕДОМЛЕНИЙ (исправленная)
// ============================================
function checkAndNotifyWaitingList($pdo, $productId, $newStock, $oldStock) {
    // Отправляем уведомления только если товар был недоступен (0 или -1) и стал доступен (>0)
    if ($oldStock > 0 || $newStock <= 0) return 0;
    
    try {
        // Получаем информацию о товаре
        $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) return 0;
        
        // Получаем главное изображение товара
        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1");
        $stmt->execute([$productId]);
        $productImage = $stmt->fetchColumn();
        
        // Подключаем функции уведомлений
        require_once __DIR__ . '/../mip/includes/notifications.php';
        
        // Получаем пользователей из листа ожидания
        $stmt = $pdo->prepare("
            SELECT r.id, r.user_id, r.message, u.email, u.name 
            FROM request r
            JOIN user u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.type = 'wl' AND r.status = 'waiting' AND r.is_notified = 0
        ");
        $stmt->execute([$productId]);
        $waitingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($waitingRequests)) return 0;
        
        $siteUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/mip';
        $productUrl = $siteUrl . '/product.php?id=' . $productId;
        $notifiedCount = 0;
        
        foreach ($waitingRequests as $req) {
            $userData = json_decode($req['message'], true);
            $requestedQty = $userData['quantity'] ?? 1;
            
            // Уведомляем только если доступное количество >= запрошенному
            if ($newStock >= $requestedQty) {
                $title = "Товар '{$product['name']}' поступил в наличие!";
                $message = "Запрошенное вами количество ({$requestedQty} шт.) теперь доступно для заказа в каталоге.";
                $link = "/mip/product.php?id=" . $productId;
                
                // Добавляем уведомление в систему
                addNotification($pdo, $req['user_id'], 'stock_available', $title, $message, $link);
                
                // Отправляем email
                $htmlMessage = getProductAvailableEmailTemplate($product['name'], $productUrl, $requestedQty, $productImage);
                sendEmailNotification($req['email'], $req['name'], $title, $htmlMessage);
                
                // Отмечаем как уведомлённого
                $updateReq = $pdo->prepare("UPDATE request SET is_notified = 1 WHERE id = ?");
                $updateReq->execute([$req['id']]);
                
                $notifiedCount++;
            }
        }
        
        if ($notifiedCount > 0) {
            error_log("Отправлено уведомлений о поступлении товара ID {$productId}: {$notifiedCount}");
        }
        
        return $notifiedCount;
        
    } catch (Exception $e) {
        error_log("Ошибка при отправке уведомлений о поступлении товара (Product ID: $productId): " . $e->getMessage());
        return 0;
    }
}

// ============================================
// AJAX: Загрузка временных изображений
// ============================================
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

// ============================================
// AJAX: Удаление временного изображения
// ============================================
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

// ============================================
// AJAX: Удаление существующего изображения из БД
// ============================================
if (isset($_POST['ajax_remove_db_image'])) {
    header('Content-Type: application/json');
    $imageId = (int)($_POST['image_id'] ?? 0);
    if ($imageId > 0) {
        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE id = ? AND product_id = ?");
        $stmt->execute([$imageId, $productId]);
        $img = $stmt->fetch();
        if ($img) {
            $filePath = __DIR__ . '/../mip/' . $img['image_url'];
            if (file_exists($filePath)) unlink($filePath);
            $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$imageId]);
            echo json_encode(['success' => true]);
            exit;
        }
    }
    echo json_encode(['success' => false]);
    exit;
}

// ============================================
// Обработка сохранения изменений
// ============================================
if (isset($_POST['save'])) {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);
    $base_price = (float)($_POST['base_price'] ?? 0);
    $newStock = (int)($_POST['stock'] ?? 0);
    $status = $_POST['status'] ?? 'draft';
    $short_description = trim($_POST['short_description'] ?? '');
    $full_description = trim($_POST['full_description'] ?? '');
    $is_new = isset($_POST['is_new']) && $_POST['is_new'] == '1' ? 1 : 0;
    $is_slider = isset($_POST['is_slider']) && $_POST['is_slider'] == '1' ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $mainSelected = $_POST['main_image_selected'] ?? '';

    // Сохраняем старый остаток для проверки уведомлений
    $oldStock = (int)$product['stock'];

    if (!$name) {
        $error = 'Название обязательно';
    } elseif ($base_price < 0) {
        $error = 'Цена не может быть отрицательной';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Обновляем товар
            $pdo->prepare("UPDATE products SET 
                category_id = ?, name = ?, short_description = ?, full_description = ?, 
                base_price = ?, stock = ?, is_new = ?, is_slider = ?, sort_order = ?, status = ?, updated_at = NOW()
                WHERE id = ?")
                ->execute([$category_id, $name, $short_description, $full_description, 
                          $base_price, $newStock, $is_new, $is_slider, $sort_order, $status, $productId]);

            // 2. Изображения
            $pdo->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = ?")->execute([$productId]);
            if (str_starts_with($mainSelected, 'db:')) {
                $dbImgId = (int)substr($mainSelected, 3);
                $pdo->prepare("UPDATE product_images SET is_main = 1 WHERE id = ? AND product_id = ?")->execute([$dbImgId, $productId]);
            }
            if (!empty($_SESSION['temp_product_images'])) {
                $selectedTempId = str_starts_with($mainSelected, 'temp:') ? substr($mainSelected, 5) : null;
                foreach ($_SESSION['temp_product_images'] as $idx => $tempImg) {
                    $isMain = ($tempImg['temp_id'] === $selectedTempId) ? 1 : 0;
                    $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_main, sort_order) VALUES (?, ?, ?, ?)")
                        ->execute([$productId, $tempImg['image_url'], $isMain, $idx]);
                }
                unset($_SESSION['temp_product_images']);
            }

            // 3. Удаление файлов
            if (!empty($_POST['delete_file_ids'])) {
                foreach ($_POST['delete_file_ids'] as $delId) {
                    $delId = (int)$delId;
                    if ($delId <= 0) continue;
                    $stmt = $pdo->prepare("SELECT file_url FROM product_files WHERE id = ? AND product_id = ?");
                    $stmt->execute([$delId, $productId]);
                    $f = $stmt->fetch();
                    if ($f) {
                        $fPath = __DIR__ . '/../mip/' . $f['file_url'];
                        if (file_exists($fPath)) unlink($fPath);
                        $pdo->prepare("DELETE FROM product_files WHERE id = ?")->execute([$delId]);
                    }
                }
            }

            // 3.1 Обновление/добавление файлов
            $fileIds = $_POST['file_id'] ?? [];
            $fileNames = $_POST['file_name'] ?? [];
            $fileGroups = $_POST['file_group'] ?? [];
            $fileSorts = $_POST['file_sort'] ?? [];
            $fileDir = __DIR__ . '/../mip/files/products/';
            if (!file_exists($fileDir)) mkdir($fileDir, 0777, true);
            
            $maxCount = max(count($fileIds), !empty($_FILES['product_files']['name']) ? count($_FILES['product_files']['name']) : 0);
            for ($i = 0; $i < $maxCount; $i++) {
                $fid = !empty($fileIds[$i]) ? (int)$fileIds[$i] : 0;
                $fName = trim($fileNames[$i] ?? '');
                $fGroup = trim($fileGroups[$i] ?? 'Документация');
                $fSort = (int)($fileSorts[$i] ?? 0);
                $fUploaded = !empty($_FILES['product_files']['name'][$i]) && $_FILES['product_files']['error'][$i] === UPLOAD_ERR_OK;
                
                if ($fid > 0) {
                    $params = [$fName, $fGroup, $fSort, $fid, $productId];
                    $sql = "UPDATE product_files SET file_name = ?, group_name = ?, sort_order = ? WHERE id = ? AND product_id = ?";
                    if ($fUploaded) {
                        $tmp = $_FILES['product_files']['tmp_name'][$i];
                        $newName = uniqid() . '_' . basename($_FILES['product_files']['name'][$i]);
                        if (move_uploaded_file($tmp, $fileDir . $newName)) {
                            $oldStmt = $pdo->prepare("SELECT file_url FROM product_files WHERE id = ?");
                            $oldStmt->execute([$fid]);
                            $oldUrl = $oldStmt->fetchColumn();
                            if ($oldUrl && file_exists(__DIR__ . '/../mip/' . $oldUrl)) unlink(__DIR__ . '/../mip/' . $oldUrl);
                            $sql = "UPDATE product_files SET file_name = ?, group_name = ?, file_url = ?, file_size = ?, sort_order = ? WHERE id = ? AND product_id = ?";
                            $params = [$fName, $fGroup, 'files/products/' . $newName, $_FILES['product_files']['size'][$i], $fSort, $fid, $productId];
                        }
                    }
                    $pdo->prepare($sql)->execute($params);
                } elseif ($fUploaded && $fName) {
                    $tmp = $_FILES['product_files']['tmp_name'][$i];
                    $newName = uniqid() . '_' . basename($_FILES['product_files']['name'][$i]);
                    if (move_uploaded_file($tmp, $fileDir . $newName)) {
                        $pdo->prepare("INSERT INTO product_files (product_id, group_name, file_name, file_url, file_size, sort_order) VALUES (?, ?, ?, ?, ?, ?)")
                            ->execute([$productId, $fGroup, $fName, 'files/products/' . $newName, $_FILES['product_files']['size'][$i], $fSort]);
                    }
                }
            }

            // 4. Удаление схем
            if (!empty($_POST['delete_scheme_ids'])) {
                foreach ($_POST['delete_scheme_ids'] as $delId) {
                    $delId = (int)$delId;
                    if ($delId <= 0) continue;
                    $stmt = $pdo->prepare("SELECT image_url FROM product_schemes WHERE id = ? AND product_id = ?");
                    $stmt->execute([$delId, $productId]);
                    $s = $stmt->fetch();
                    if ($s) {
                        $sPath = __DIR__ . '/../mip/' . $s['image_url'];
                        if (file_exists($sPath)) unlink($sPath);
                        $pdo->prepare("DELETE FROM product_schemes WHERE id = ?")->execute([$delId]);
                    }
                }
            }

            // 4.1 Обновление/добавление схем
            $schemeIds = $_POST['scheme_id'] ?? [];
            $schemeTitles = $_POST['scheme_title'] ?? [];
            $schemeDescs = $_POST['scheme_description'] ?? [];
            $schemeSorts = $_POST['scheme_sort'] ?? [];
            $schemeDir = __DIR__ . '/../mip/img/schemes/';
            if (!file_exists($schemeDir)) mkdir($schemeDir, 0777, true);
            
            $maxSchemeCount = max(count($schemeIds), !empty($_FILES['scheme_images']['name']) ? count($_FILES['scheme_images']['name']) : 0);
            for ($i = 0; $i < $maxSchemeCount; $i++) {
                $sid = !empty($schemeIds[$i]) ? (int)$schemeIds[$i] : 0;
                $sTitle = trim($schemeTitles[$i] ?? '');
                $sDesc = trim($schemeDescs[$i] ?? '');
                $sSort = (int)($schemeSorts[$i] ?? 0);
                $sUploaded = !empty($_FILES['scheme_images']['name'][$i]) && $_FILES['scheme_images']['error'][$i] === UPLOAD_ERR_OK;
                
                if ($sid > 0) {
                    if (!$sTitle && !$sUploaded) continue;
                    $params = [$sTitle, $sDesc, $sSort, $sid, $productId];
                    $sql = "UPDATE product_schemes SET title = ?, description = ?, sort_order = ? WHERE id = ? AND product_id = ?";
                    if ($sUploaded) {
                        $tmp = $_FILES['scheme_images']['tmp_name'][$i];
                        $newName = uniqid() . '_' . basename($_FILES['scheme_images']['name'][$i]);
                        if (move_uploaded_file($tmp, $schemeDir . $newName)) {
                            $oldStmt = $pdo->prepare("SELECT image_url FROM product_schemes WHERE id = ?");
                            $oldStmt->execute([$sid]);
                            $oldUrl = $oldStmt->fetchColumn();
                            if ($oldUrl && file_exists(__DIR__ . '/../mip/' . $oldUrl)) unlink(__DIR__ . '/../mip/' . $oldUrl);
                            $sql = "UPDATE product_schemes SET title = ?, description = ?, image_url = ?, sort_order = ? WHERE id = ? AND product_id = ?";
                            $params = [$sTitle, $sDesc, 'img/schemes/' . $newName, $sSort, $sid, $productId];
                        }
                    }
                    $pdo->prepare($sql)->execute($params);
                } elseif ($sUploaded && $sTitle) {
                    $tmp = $_FILES['scheme_images']['tmp_name'][$i];
                    $newName = uniqid() . '_' . basename($_FILES['scheme_images']['name'][$i]);
                    if (move_uploaded_file($tmp, $schemeDir . $newName)) {
                        $pdo->prepare("INSERT INTO product_schemes (product_id, title, description, image_url, sort_order) VALUES (?, ?, ?, ?, ?)")
                            ->execute([$productId, $sTitle, $sDesc, 'img/schemes/' . $newName, $sSort]);
                    }
                }
            }

            // 5. Удаление комплектаций
            if (!empty($_POST['delete_config_ids'])) {
                foreach ($_POST['delete_config_ids'] as $delId) {
                    $delId = (int)$delId;
                    if ($delId > 0) $pdo->prepare("DELETE FROM product_configurations WHERE id = ? AND product_id = ?")->execute([$delId, $productId]);
                }
            }
            
            // 5.1 Обновление/добавление комплектаций
            if (!empty($_POST['config_name'])) {
                foreach ($_POST['config_name'] as $k => $cName) {
                    if (trim($cName) === '') continue;
                    $cId = !empty($_POST['config_id'][$k]) ? (int)$_POST['config_id'][$k] : 0;
                    $cPrice = (float)($_POST['config_price'][$k] ?? 0);
                    $cCharsText = trim($_POST['config_chars'][$k] ?? '');
                    $cSort = (int)($_POST['config_sort'][$k] ?? 0);
                    $cMain = isset($_POST['config_main'][$k]) && $_POST['config_main'][$k] == '1' ? 1 : 0;
                    
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
                    $charsArray['stock'] = $cStock;
                    $charsArray['made_to_order'] = (bool)$cIsMadeToOrder;
                    if ($cLeadTime) $charsArray['lead_time'] = $cLeadTime;
                    
                    $charsJson = json_encode($charsArray, JSON_UNESCAPED_UNICODE);
                    
                    if ($cId > 0) {
                        $pdo->prepare("UPDATE product_configurations SET name = ?, price = ?, characteristics = ?, sort_order = ?, is_main = ? WHERE id = ? AND product_id = ?")
                            ->execute([$cName, $cPrice, $charsJson, $cSort, $cMain, $cId, $productId]);
                    } else {
                        $pdo->prepare("INSERT INTO product_configurations (product_id, name, price, characteristics, sort_order, is_main) VALUES (?, ?, ?, ?, ?, ?)")
                            ->execute([$productId, $cName, $cPrice, $charsJson, $cSort, $cMain]);
                    }
                }
            }

            // 6. Удаление модификаций
            if (!empty($_POST['delete_mod_ids'])) {
                foreach ($_POST['delete_mod_ids'] as $delId) {
                    $delId = (int)$delId;
                    if ($delId > 0) $pdo->prepare("DELETE FROM product_modifications WHERE id = ? AND product_id = ?")->execute([$delId, $productId]);
                }
            }
            
            // 6.1 Обновление/добавление модификаций
            if (!empty($_POST['mod_group_name'])) {
                foreach ($_POST['mod_group_name'] as $k => $mGroup) {
                    if (trim($mGroup) === '') continue;
                    $mId = !empty($_POST['mod_id'][$k]) ? (int)$_POST['mod_id'][$k] : 0;
                    $mSort = (int)($_POST['mod_sort'][$k] ?? 0);
                    $variants = [];
                    
                    if (!empty($_POST['mod_variants'][$k])) {
                        foreach ($_POST['mod_variants'][$k] as $v) {
                            if (!empty($v['name'])) {
                                $props = [];
                                if (!empty($v['properties'])) {
                                    foreach ($v['properties'] as $p) {
                                        if (!empty($p['name']) && isset($p['price'])) {
                                            $props[] = ['name' => trim($p['name']), 'price' => (float)$p['price']];
                                        }
                                    }
                                }
                                $variants[] = [
                                    'name' => trim($v['name']), 
                                    'price' => (float)($v['price'] ?? 0), 
                                    'description' => trim($v['description'] ?? ''), 
                                    'properties' => $props
                                ];
                            }
                        }
                    }
                    $variantsJson = json_encode($variants, JSON_UNESCAPED_UNICODE);
                    
                    if ($mId > 0) {
                        $pdo->prepare("UPDATE product_modifications SET group_name = ?, options = ?, sort_order = ? WHERE id = ? AND product_id = ?")
                            ->execute([$mGroup, $variantsJson, $mSort, $mId, $productId]);
                    } else {
                        $pdo->prepare("INSERT INTO product_modifications (product_id, group_name, options, sort_order) VALUES (?, ?, ?, ?)")
                            ->execute([$productId, $mGroup, $variantsJson, $mSort]);
                    }
                }
            }

            $pdo->commit();
            
            // 7. ОТПРАВКА УВЕДОМЛЕНИЙ (если остаток увеличился и стал положительным)
            $notifiedCount = 0;
            if ($newStock > 0 && $oldStock <= 0) {
                $notifiedCount = checkAndNotifyWaitingList($pdo, $productId, $newStock, $oldStock);
            }
            
            $success = 'Товар успешно обновлён!';
            if ($notifiedCount > 0) {
                $success .= " Отправлено уведомлений о поступлении: {$notifiedCount}.";
            }
            
            // Перезагружаем данные товара для отображения
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

// ============================================
// Загрузка данных для формы
// ============================================
$categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id");
$stmt->execute([$productId]);
$productImages = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM product_files WHERE product_id = ? ORDER BY sort_order, id");
$stmt->execute([$productId]);
$productFiles = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM product_schemes WHERE product_id = ? ORDER BY sort_order, id");
$stmt->execute([$productId]);
$productSchemes = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM product_configurations WHERE product_id = ? ORDER BY sort_order, id");
$stmt->execute([$productId]);
$productConfigs = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM product_modifications WHERE product_id = ? ORDER BY sort_order, id");
$stmt->execute([$productId]);
$productMods = $stmt->fetchAll();

$tempImages = $_SESSION['temp_product_images'] ?? [];

$mainSelectedValue = '';
foreach ($productImages as $img) { 
    if ($img['is_main']) { 
        $mainSelectedValue = 'db:' . $img['id']; 
        break; 
    } 
}
if (!$mainSelectedValue && $productImages) {
    $mainSelectedValue = 'db:' . $productImages[0]['id'];
} elseif (empty($productImages) && !empty($tempImages)) {
    $mainSelectedValue = 'temp:' . $tempImages[0]['temp_id'];
}

$formData = [
    'name' => $_POST['name'] ?? $product['name'],
    'category_id' => $_POST['category_id'] ?? $product['category_id'],
    'base_price' => $_POST['base_price'] ?? $product['base_price'],
    'stock' => $_POST['stock'] ?? $product['stock'],
    'short_description' => $_POST['short_description'] ?? $product['short_description'],
    'full_description' => $_POST['full_description'] ?? $product['full_description'],
    'is_new' => (int)($_POST['is_new'] ?? $product['is_new']),
    'is_slider' => (int)($_POST['is_slider'] ?? $product['is_slider']),
    'sort_order' => (int)($_POST['sort_order'] ?? $product['sort_order']),
    'status' => $_POST['status'] ?? $product['status'],
];

$pageScript = '$(".summernote").summernote({ height: 300, lang: "ru-RU" });';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Редактирование товара #<?= $productId ?></h1>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="../mip/product.php?id=<?= $productId ?>" target="_blank">Открыть</a></div><?php endif; ?>

            <form method="POST" class="card" id="productForm" enctype="multipart/form-data">
                <div id="deletedItemsContainer" style="display:none;"></div>
                <input type="hidden" name="main_image_selected" id="mainImageSelected" value="<?= htmlspecialchars($mainSelectedValue) ?>">
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-section mb-3">
                                <label>Название *</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($formData['name']) ?>" required>
                            </div>
                            <div class="form-section mb-3">
                                <label>Краткое описание</label>
                                <textarea name="short_description" class="form-control" rows="3"><?= htmlspecialchars($formData['short_description']) ?></textarea>
                            </div>
                            <div class="form-section mb-3">
                                <label>Полное описание</label>
                                <textarea name="full_description" class="form-control summernote" rows="10"><?= htmlspecialchars($formData['full_description']) ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-section mb-3">
                                <label>Категория</label>
                                <select name="category_id" class="form-control">
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $formData['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-section mb-3">
                                <label>Цена (₽)</label>
                                <input type="number" step="0.01" min="0" name="base_price" class="form-control" value="<?= $formData['base_price'] ?>">
                            </div>
                            <div class="form-section mb-3">
                                <label>Остаток на складе</label>
                                <input type="number" min="-1" name="stock" id="base_stock" class="form-control" value="<?= $formData['stock'] ?>">
                                <small class="text-muted">Укажите -1, если товар только под заказ</small>
                            </div>
                            <div class="form-section mb-3">
                                <label>Статус</label>
                                <select name="status" class="form-control">
                                    <option value="draft" <?= $formData['status'] === 'draft' ? 'selected' : '' ?>>Черновик</option>
                                    <option value="active" <?= $formData['status'] === 'active' ? 'selected' : '' ?>>Активен</option>
                                    <option value="inactive" <?= $formData['status'] === 'inactive' ? 'selected' : '' ?>>Неактивен</option>
                                </select>
                            </div>
                            <div class="form-section mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_new" id="is_new" value="1" <?= $formData['is_new'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_new">Новинка</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_slider" id="is_slider" value="1" <?= $formData['is_slider'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_slider">В слайдере</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Изображения -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Изображения (<span id="imageCount"><?= count($productImages) + count($tempImages) ?></span>)</h5>
                            <div class="row" id="imagesContainer">
                                <?php foreach ($productImages as $img): ?>
                                <div class="col-md-3 mb-3 image-card" data-image-id="<?= $img['id'] ?>">
                                    <div class="card">
                                        <img src="../mip/<?= htmlspecialchars($img['image_url']) ?>" class="card-img-top" style="height:150px;object-fit:cover;">
                                        <div class="card-body p-2">
                                            <div class="form-check mb-2">
                                                <input type="radio" name="main_image_selector" class="form-check-input main-radio" value="db:<?= $img['id'] ?>" id="main_db_<?= $img['id'] ?>" <?= $img['is_main'] ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="main_db_<?= $img['id'] ?>">📌 Главное</label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger btn-block remove-db-image" data-image-id="<?= $img['id'] ?>"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php foreach ($tempImages as $img): ?>
                                <div class="col-md-3 mb-3 image-card" data-temp-id="<?= htmlspecialchars($img['temp_id']) ?>">
                                    <div class="card">
                                        <img src="../mip/<?= htmlspecialchars($img['image_url']) ?>" class="card-img-top" style="height:150px;object-fit:cover;">
                                        <div class="card-body p-2">
                                            <div class="form-check mb-2">
                                                <input type="radio" name="main_image_selector" class="form-check-input main-radio" value="temp:<?= htmlspecialchars($img['temp_id']) ?>" id="main_temp_<?= htmlspecialchars($img['temp_id']) ?>" <?= empty($productImages) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="main_temp_<?= htmlspecialchars($img['temp_id']) ?>">📌 Главное</label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger btn-block remove-image" data-temp-id="<?= htmlspecialchars($img['temp_id']) ?>"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (empty($productImages) && empty($tempImages)): ?>
                                <div class="col-12"><p class="text-muted">Изображений нет</p></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group mt-2">
                                <label>Добавить изображения</label>
                                <input type="file" name="images[]" id="imageInput" class="form-control" multiple accept="image/*">
                                <div id="uploadProgress" class="mt-2" style="display:none;">
                                    <div class="spinner-border spinner-border-sm text-primary"></div> Загрузка...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Файлы -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Файлы</h5>
                            <div id="filesContainer">
                                <?php foreach ($productFiles as $f): ?>
                                <div class="file-entry mb-3 p-3 border rounded" data-file-id="<?= $f['id'] ?>">
                                    <input type="hidden" name="file_id[]" value="<?= $f['id'] ?>">
                                    <div class="row align-items-end">
                                        <div class="col-md-3"><label>Отображаемое имя *</label><input type="text" name="file_name[]" class="form-control" value="<?= htmlspecialchars($f['file_name']) ?>" required></div>
                                        <div class="col-md-3"><label>Группа</label><input type="text" name="file_group[]" class="form-control" value="<?= htmlspecialchars($f['group_name']) ?>"></div>
                                        <div class="col-md-4"><label>Файл</label><div class="input-group input-group-sm"><input type="text" class="form-control" value="<?= htmlspecialchars($f['file_url']) ?>" readonly style="background:#f8f9fa;"><div class="input-group-append"><a href="../mip/<?= htmlspecialchars($f['file_url']) ?>" target="_blank" class="btn btn-outline-secondary" title="Открыть">🔗</a></div></div><input type="file" name="product_files[]" class="form-control form-control-sm mt-1" accept=".pdf,.doc,.docx,.zip,.rar,.xls,.xlsx"><small class="text-muted">Загрузите новый, чтобы заменить</small></div>
                                        <div class="col-md-1"><label>Порядок</label><input type="number" name="file_sort[]" class="form-control" value="<?= $f['sort_order'] ?>" min="0"></div>
                                        <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-file-btn">✕</button></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (empty($productFiles)): ?>
                                <div class="file-entry mb-3 p-3 border rounded">
                                    <div class="row align-items-end">
                                        <div class="col-md-3"><label>Отображаемое имя *</label><input type="text" name="file_name[]" class="form-control" placeholder="Напр: Инструкция" required></div>
                                        <div class="col-md-3"><label>Группа</label><input type="text" name="file_group[]" class="form-control" value="Документация"></div>
                                        <div class="col-md-4"><label>Загрузить файл</label><input type="file" name="product_files[]" class="form-control" accept=".pdf,.doc,.docx,.zip,.rar,.xls,.xlsx" required></div>
                                        <div class="col-md-1"><label>Порядок</label><input type="number" name="file_sort[]" class="form-control" value="0" min="0"></div>
                                        <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-file-btn">✕</button></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" id="addFileBtn"><i class="fas fa-plus"></i> Добавить файл</button>
                        </div>
                    </div>

                    <!-- Схемы -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Схемы</h5>
                            <div id="schemesContainer">
                                <?php foreach ($productSchemes as $s): ?>
                                <div class="scheme-entry mb-3 p-3 border rounded" data-scheme-id="<?= $s['id'] ?>">
                                    <input type="hidden" name="scheme_id[]" value="<?= $s['id'] ?>">
                                    <div class="row">
                                        <div class="col-md-4"><label>Название</label><input type="text" name="scheme_title[]" class="form-control" value="<?= htmlspecialchars($s['title']) ?>"></div>
                                        <div class="col-md-4"><label>Изображение</label><?php if ($s['image_url']): ?><div><img src="../mip/<?= htmlspecialchars($s['image_url']) ?>" style="max-height:60px;" class="mb-1 border rounded"></div><?php endif; ?><input type="file" name="scheme_images[]" class="form-control" accept="image/*"><small class="text-muted">Загрузите новое, чтобы заменить</small></div>
                                        <div class="col-md-3"><label>Порядок</label><input type="number" name="scheme_sort[]" class="form-control" value="<?= $s['sort_order'] ?>" min="0"></div>
                                        <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-scheme-btn">✕</button></div>
                                    </div>
                                    <div class="mt-2"><label>Описание</label><textarea name="scheme_description[]" class="form-control" rows="2"><?= htmlspecialchars($s['description']) ?></textarea></div>
                                </div>
                                <?php endforeach; ?>
                                
                                <div class="scheme-entry mb-3 p-3 border rounded d-none" id="schemeTemplate">
                                    <div class="row">
                                        <div class="col-md-4"><label>Название</label><input type="text" name="scheme_title[]" class="form-control" placeholder="Напр: Схема подключения" required></div>
                                        <div class="col-md-4"><label>Изображение *</label><input type="file" name="scheme_images[]" class="form-control" accept="image/*" required></div>
                                        <div class="col-md-3"><label>Порядок</label><input type="number" name="scheme_sort[]" class="form-control" value="0" min="0"></div>
                                        <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-scheme-btn">✕</button></div>
                                    </div>
                                    <div class="mt-2"><label>Описание</label><textarea name="scheme_description[]" class="form-control" rows="2"></textarea></div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" id="addSchemeBtn"><i class="fas fa-plus"></i> Добавить схему</button>
                        </div>
                    </div>

                    <!-- Комплектации -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Комплектации</h5>
                            <div id="configsContainer">
                                <?php foreach ($productConfigs as $c): 
                                    $chars = json_decode($c['characteristics'], true) ?? []; 
                                    $charsText = ''; 
                                    foreach ($chars as $k => $v) { 
                                        if (!in_array($k, ['stock', 'made_to_order', 'lead_time'])) {
                                            $charsText .= "$k: $v\n"; 
                                        }
                                    } 
                                    $cStock = $chars['stock'] ?? 0;
                                    $cIsMadeToOrder = $chars['made_to_order'] ?? false;
                                    $cLeadTime = $chars['lead_time'] ?? '';
                                ?>
                                <div class="config-entry mb-3 p-3 border rounded" data-config-id="<?= $c['id'] ?>">
                                    <input type="hidden" name="config_id[]" value="<?= $c['id'] ?>">
                                    <div class="row">
                                        <div class="col-md-3"><label>Название</label><input type="text" name="config_name[]" class="form-control" value="<?= htmlspecialchars($c['name']) ?>"></div>
                                        <div class="col-md-2"><label>Цена (₽)</label><input type="number" step="0.01" name="config_price[]" class="form-control" value="<?= $c['price'] ?>"></div>
                                        <div class="col-md-2"><label>Остаток</label><input type="number" min="-1" name="config_stock[]" class="form-control config-stock" value="<?= $cStock ?>" <?= $cIsMadeToOrder ? 'disabled' : '' ?>></div>
                                        <div class="col-md-3"><label>Характеристики</label><textarea name="config_chars[]" class="form-control" rows="2"><?= trim(htmlspecialchars($charsText)) ?></textarea></div>
                                        <div class="col-md-1"><label>Порядок</label><input type="number" name="config_sort[]" class="form-control" value="<?= $c['sort_order'] ?>" min="0"></div>
                                        <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-config-btn">✕</button></div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-4"><div class="form-check"><input class="form-check-input config-made-to-order" type="checkbox" name="config_made_to_order[]" value="1" <?= $cIsMadeToOrder ? 'checked' : '' ?>><label class="form-check-label"><i class="fas fa-industry"></i> Только под заказ</label></div></div>
                                        <div class="col-md-4"><label>Срок изготовления</label><input type="text" name="config_lead_time[]" class="form-control" value="<?= htmlspecialchars($cLeadTime) ?>" placeholder="Напр: 14-21 дней"></div>
                                        <div class="col-md-2"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="config_main[]" value="1" <?= $c['is_main'] ? 'checked' : '' ?>><label class="form-check-label">Основная</label></div></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (empty($productConfigs)): ?>
                                <div class="config-entry mb-3 p-3 border rounded" data-config-id="0">
                                    <div class="row">
                                        <div class="col-md-3"><label>Название</label><input type="text" name="config_name[]" class="form-control" placeholder="Базовая"></div>
                                        <div class="col-md-2"><label>Цена (₽)</label><input type="number" step="0.01" name="config_price[]" class="form-control" value="0"></div>
                                        <div class="col-md-2"><label>Остаток</label><input type="number" min="-1" name="config_stock[]" class="form-control config-stock" value="0"></div>
                                        <div class="col-md-3"><label>Характеристики</label><textarea name="config_chars[]" class="form-control" rows="2" placeholder="Мощность: 1000 Вт&#10;Напряжение: 220В"></textarea></div>
                                        <div class="col-md-1"><label>Порядок</label><input type="number" name="config_sort[]" class="form-control" value="0" min="0"></div>
                                        <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm remove-config-btn">✕</button></div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-4"><div class="form-check"><input class="form-check-input config-made-to-order" type="checkbox" name="config_made_to_order[]" value="1"><label class="form-check-label"><i class="fas fa-industry"></i> Только под заказ</label></div></div>
                                        <div class="col-md-4"><label>Срок изготовления</label><input type="text" name="config_lead_time[]" class="form-control" placeholder="Напр: 14-21 дней"></div>
                                        <div class="col-md-2"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="config_main[]" value="1"><label class="form-check-label">Основная</label></div></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" id="addConfigBtn"><i class="fas fa-plus"></i> Добавить комплектацию</button>
                        </div>
                    </div>

                    <!-- Модификации -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Модификации</h5>
                            <div id="modsContainer">
                                <?php foreach ($productMods as $idx => $mod): 
                                    $variants = json_decode($mod['options'], true) ?? []; 
                                ?>
                                <div class="mod-entry mb-3 p-3 border rounded" data-gidx="<?= $idx ?>">
                                    <input type="hidden" name="mod_id[]" value="<?= $mod['id'] ?>">
                                    <div class="row">
                                        <div class="col-md-4"><label>Название группы</label><input type="text" name="mod_group_name[]" class="form-control" value="<?= htmlspecialchars($mod['group_name']) ?>"></div>
                                        <div class="col-md-2"><label>Порядок</label><input type="number" name="mod_sort[]" class="form-control" value="<?= $mod['sort_order'] ?>" min="0"></div>
                                        <div class="col-md-6 text-right"><button type="button" class="btn btn-success btn-sm add-variant-btn" data-gidx="<?= $idx ?>"><i class="fas fa-plus"></i> Вариант</button></div>
                                    </div>
                                    <div class="variants-container mt-3">
                                        <?php foreach ($variants as $vIdx => $v): ?>
                                        <div class="variant-item card mb-2">
                                            <div class="card-body">
                                                <div class="row align-items-end">
                                                    <div class="col-md-3"><label>Название *</label><input type="text" name="mod_variants[<?= $idx ?>][<?= $vIdx ?>][name]" class="form-control form-control-sm" value="<?= htmlspecialchars($v['name'] ?? '') ?>" required></div>
                                                    <div class="col-md-2"><label>Цена</label><input type="number" step="0.01" name="mod_variants[<?= $idx ?>][<?= $vIdx ?>][price]" class="form-control form-control-sm" value="<?= $v['price'] ?? 0 ?>"></div>
                                                    <div class="col-md-4"><label>Описание</label><input type="text" name="mod_variants[<?= $idx ?>][<?= $vIdx ?>][description]" class="form-control form-control-sm" value="<?= htmlspecialchars($v['description'] ?? '') ?>"></div>
                                                    <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-variant-btn">✕</button></div>
                                                    <div class="col-md-1"><button type="button" class="btn btn-sm btn-secondary add-prop-btn">+ Св-во</button></div>
                                                </div>
                                                <div class="properties-list mt-2">
                                                    <?php if (!empty($v['properties'])): foreach ($v['properties'] as $pIdx => $p): ?>
                                                    <div class="prop-item input-group input-group-sm mb-1">
                                                        <input type="text" name="mod_variants[<?= $idx ?>][<?= $vIdx ?>][properties][<?= $pIdx ?>][name]" class="form-control" value="<?= htmlspecialchars($p['name'] ?? '') ?>" placeholder="Свойство">
                                                        <input type="number" step="0.01" name="mod_variants[<?= $idx ?>][<?= $vIdx ?>][properties][<?= $pIdx ?>][price]" class="form-control" style="width:90px" value="<?= $p['price'] ?? 0 ?>" placeholder="Наценка">
                                                        <div class="input-group-append"><button type="button" class="btn btn-outline-danger remove-prop-btn">✕</button></div>
                                                    </div>
                                                    <?php endforeach; endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm mt-2 add-variant-btn" data-gidx="<?= $idx ?>"><i class="fas fa-plus"></i> Добавить вариант</button>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (empty($productMods)): ?>
                                <div class="mod-entry mb-3 p-3 border rounded" data-gidx="0">
                                    <div class="row">
                                        <div class="col-md-4"><label>Название группы</label><input type="text" name="mod_group_name[]" class="form-control" placeholder="Напр: Количество входов"></div>
                                        <div class="col-md-2"><label>Порядок</label><input type="number" name="mod_sort[]" class="form-control" value="0" min="0"></div>
                                        <div class="col-md-6 text-right"><button type="button" class="btn btn-success btn-sm add-variant-btn" data-gidx="0"><i class="fas fa-plus"></i> Вариант</button></div>
                                    </div>
                                    <div class="variants-container mt-3"></div>
                                    <button type="button" class="btn btn-secondary btn-sm mt-2 add-variant-btn" data-gidx="0"><i class="fas fa-plus"></i> Добавить вариант</button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" id="addModGroupBtn"><i class="fas fa-plus"></i> Добавить группу</button>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" name="save" value="default" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить изменения</button>
                    <button type="submit" name="save" value="open_preview" formnovalidate class="btn btn-success"><i class="fas fa-external-link-alt"></i> Сохранить и открыть</button>
                    <a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Отмена</a>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function markForDeletion(type, id) {
        const container = document.getElementById('deletedItemsContainer');
        if (!container || !id) return;
        const existing = container.querySelector(`input[name="delete_${type}_ids[]"][value="${id}"]`);
        if (!existing) {
            container.insertAdjacentHTML('beforeend', `<input type="hidden" name="delete_${type}_ids[]" value="${id}">`);
        }
    }

    document.getElementById('addFileBtn')?.addEventListener('click', () => cloneField('#filesContainer', '.file-entry'));
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
        const stockInput = f.querySelector('.config-stock');
        if(stockInput) stockInput.disabled = false;
        c.appendChild(f);
    });

    document.getElementById('addSchemeBtn')?.addEventListener('click', function() {
        const container = document.getElementById('schemesContainer');
        const template = document.getElementById('schemeTemplate');
        const clone = template.cloneNode(true);
        clone.classList.remove('d-none');
        clone.removeAttribute('id');
        clone.querySelectorAll('input,textarea').forEach(el => {
            if (el.type === 'file') el.value = '';
            else if (el.type !== 'hidden') el.value = '';
        });
        container.appendChild(clone);
    });

    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.remove-file-btn')) {
            const entry = e.target.closest('.file-entry');
            const fileId = entry?.dataset?.fileId;
            if (fileId) markForDeletion('file', fileId);
            entry?.remove();
        }
        if (e.target.closest('.remove-scheme-btn')) {
            const entry = e.target.closest('.scheme-entry');
            const schemeId = entry?.dataset?.schemeId;
            if (schemeId) markForDeletion('scheme', schemeId);
            entry?.remove();
        }
        if (e.target.closest('.remove-config-btn')) {
            const entry = e.target.closest('.config-entry');
            const configId = entry?.dataset?.configId;
            if (configId) markForDeletion('config', configId);
            entry?.remove();
        }
        if (e.target.closest('.remove-variant-btn')) e.target.closest('.variant-item')?.remove();
        if (e.target.closest('.remove-prop-btn')) e.target.closest('.prop-item')?.remove();
    });

    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.add-variant-btn')) addVariant(e.target.closest('.add-variant-btn'));
        if (e.target.closest('.add-prop-btn')) addProperty(e.target.closest('.add-prop-btn'));
    });
    
    document.getElementById('addModGroupBtn')?.addEventListener('click', function() {
        const c = document.getElementById('modsContainer'), idx = c.querySelectorAll('.mod-entry').length;
        const f = document.querySelector('.mod-entry').cloneNode(true);
        f.querySelectorAll('input,textarea').forEach(i => { if(i.type !== 'hidden') i.value = ''; });
        f.querySelector('input[name="mod_id[]"]')?.remove();
        f.dataset.gidx = idx;
        f.querySelectorAll('.add-variant-btn').forEach(btn => btn.dataset.gidx = idx);
        c.appendChild(f);
    });

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
    const c = document.querySelector(containerSel), first = c.querySelector(itemSel);
    if(!first) return;
    const f = first.cloneNode(true);
    f.querySelectorAll('input,textarea').forEach(i => {
        if(i.type==='file') i.value = '';
        else if(i.type!=='hidden') i.value = i.type==='number' ? '0' : '';
    });
    c.appendChild(f);
}

function addVariant(btn) {
    const entry = btn.closest('.mod-entry'), container = entry.querySelector('.variants-container');
    const gidx = entry.dataset.gidx || 0, vidx = container.querySelectorAll('.variant-item').length;
    const html = `<div class="variant-item card mb-2"><div class="card-body"><div class="row align-items-end"><div class="col-md-3"><label>Название *</label><input type="text" name="mod_variants[${gidx}][${vidx}][name]" class="form-control form-control-sm" required></div><div class="col-md-2"><label>Цена</label><input type="number" step="0.01" name="mod_variants[${gidx}][${vidx}][price]" class="form-control form-control-sm" value="0"></div><div class="col-md-4"><label>Описание</label><input type="text" name="mod_variants[${gidx}][${vidx}][description]" class="form-control form-control-sm"></div><div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-variant-btn">✕</button></div><div class="col-md-1"><button type="button" class="btn btn-sm btn-secondary add-prop-btn">+ Св-во</button></div></div><div class="properties-list mt-2"></div></div></div>`;
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
    const mainImageSelected = document.getElementById('mainImageSelected');
    
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const fd = new FormData(); 
                fd.append('ajax_upload', '1');
                for (let f of this.files) fd.append('images[]', f);
                uploadProgress.style.display = 'block'; 
                this.disabled = true;
                
                fetch('product_edit.php?id=<?= $productId ?>', {method:'POST', body:fd})
                .then(r => r.json()).then(data => {
                    uploadProgress.style.display = 'none'; 
                    this.disabled = false; 
                    this.value = '';
                    if (data.success && data.images.length) {
                        const m = imagesContainer.querySelector('.text-muted'); 
                        if (m) m.remove();
                        data.images.forEach((img, i) => {
                            const isFirst = document.querySelectorAll('.image-card').length === 0;
                            const radioId = 'main_temp_' + img.temp_id + '_' + Date.now() + '_' + i;
                            const html = `<div class="col-md-3 mb-3 image-card" data-temp-id="${img.temp_id}"><div class="card"><img src="../mip/${img.image_url}" class="card-img-top" style="height:150px;object-fit:cover;"><div class="card-body p-2"><div class="form-check mb-2"><input type="radio" name="main_image_selector" class="form-check-input main-radio" value="temp:${img.temp_id}" id="${radioId}" ${isFirst ? 'checked' : ''}><label class="form-check-label" for="${radioId}">📌 Главное</label></div><button type="button" class="btn btn-sm btn-danger btn-block remove-image" data-temp-id="${img.temp_id}"><i class="fas fa-trash"></i></button></div></div></div>`;
                            imagesContainer.insertAdjacentHTML('beforeend', html);
                            if (isFirst) mainImageSelected.value = 'temp:' + img.temp_id;
                        });
                        imageCount.textContent = document.querySelectorAll('.image-card').length;
                    }
                }).catch(err => { 
                    uploadProgress.style.display='none'; 
                    this.disabled=false; 
                    alert('❌ Ошибка: '+err); 
                });
            }
        });
    }
    
    imagesContainer?.addEventListener('click', function(e) {
        const btn = e.target.closest('button'); 
        if (!btn) return;
        
        if (btn.classList.contains('remove-image')) {
            const tid = btn.dataset.tempId;
            fetch('product_edit.php?id=<?= $productId ?>', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_remove=1&temp_id='+encodeURIComponent(tid)})
            .then(r => r.json()).then(data => {
                if (data.success) {
                    const card = btn.closest('.image-card');
                    const wasChecked = card.querySelector('.main-radio')?.checked;
                    card.remove();
                    imageCount.textContent = document.querySelectorAll('.image-card').length;
                    if (wasChecked && document.querySelector('.main-radio')) {
                        const first = document.querySelector('.main-radio');
                        first.checked = true;
                        mainImageSelected.value = first.value;
                    } else if (!document.querySelector('.image-card')) {
                        mainImageSelected.value = '';
                        imagesContainer.innerHTML = '<div class="col-12"><p class="text-muted">Изображений нет</p></div>';
                    }
                }
            });
        } else if (btn.classList.contains('remove-db-image')) {
            const imgId = btn.dataset.imageId;
            if (confirm('Удалить это изображение с сервера?')) {
                fetch('product_edit.php?id=<?= $productId ?>', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_remove_db_image=1&image_id='+encodeURIComponent(imgId)})
                .then(r => r.json()).then(data => {
                    if (data.success) {
                        const card = btn.closest('.image-card');
                        const wasChecked = card.querySelector('.main-radio')?.checked;
                        card.remove();
                        imageCount.textContent = document.querySelectorAll('.image-card').length;
                        if (wasChecked && document.querySelector('.main-radio')) {
                            const first = document.querySelector('.main-radio');
                            first.checked = true;
                            mainImageSelected.value = first.value;
                        } else if (!document.querySelector('.image-card')) {
                            mainImageSelected.value = '';
                            imagesContainer.innerHTML = '<div class="col-12"><p class="text-muted">Изображений нет</p></div>';
                        }
                    } else {
                        alert('❌ Не удалось удалить');
                    }
                });
            }
        }
    });
    
    imagesContainer?.addEventListener('change', function(e) {
        if (e.target.classList.contains('main-radio')) {
            mainImageSelected.value = e.target.value;
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>