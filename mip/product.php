<?php
session_start();
require_once 'config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

try {
    // 1. Получаем продукт
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("<div style='text-align:center;margin-top:150px;font-family:sans-serif;'>
                <h2 style='color:#d9534f;'>Продукт не найден</h2>
                <a href='catalog.php' style='color:#1a1982;'>Вернуться в каталог</a>
             </div>");
    }

    // 2. Изображения
    try {
        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$product_id]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY id ASC");
        $stmt->execute([$product_id]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Комплектации
    try {
        $stmt = $pdo->prepare("SELECT * FROM product_configurations WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$product_id]);
        $configurations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $stmt = $pdo->prepare("SELECT * FROM product_configurations WHERE product_id = ? ORDER BY id ASC");
        $stmt->execute([$product_id]);
        $configurations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Модификации
    try {
        $stmt = $pdo->prepare("SELECT * FROM product_modifications WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$product_id]);
        $modifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $stmt = $pdo->prepare("SELECT * FROM product_modifications WHERE product_id = ? ORDER BY id ASC");
        $stmt->execute([$product_id]);
        $modifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. Схемы
    try {
        $stmt = $pdo->prepare("SELECT * FROM product_schemes WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$product_id]);
        $schemes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $stmt = $pdo->prepare("SELECT * FROM product_schemes WHERE product_id = ? ORDER BY id ASC");
        $stmt->execute([$product_id]);
        $schemes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. Файлы
    try {
        $stmt = $pdo->prepare("SELECT * FROM product_files WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$product_id]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $stmt = $pdo->prepare("SELECT * FROM product_files WHERE product_id = ? ORDER BY id ASC");
        $stmt->execute([$product_id]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    error_log("Product Error: " . $e->getMessage());
    die("<div style='text-align:center;margin-top:150px;font-family:sans-serif;'>
            <h2 style='color:#d9534f;'>Ошибка загрузки данных</h2>
            <a href='catalog.php' style='color:#1a1982;'>Вернуться в каталог</a>
         </div>");
}

function getImageUrl($url) {
    if (empty($url)) return 'img/placeholder.png';
    $url = ltrim($url, './');
    return file_exists($url) ? $url : 'img/placeholder.png';
}

// Флаг "Позиция на заказ"
$isMadeToOrder = ($product['stock'] == -1);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/style_header_footer.css" />
<link rel="stylesheet" href="css/style_main.css" />
<link rel="stylesheet" href="css/style_product.css" />
<link rel="stylesheet" href="css/header_mip.css" />
<link rel="stylesheet" href="css/modals.css">
<title><?= htmlspecialchars($product['name']) ?></title>
<style>
/* Стили для модального окна "Товар добавлен" */
.cart-success-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    justify-content: center;
    align-items: center;
}
.cart-success-modal.show {
    display: flex;
}
.cart-success-content {
    background: #ffffff;
    border-radius: 20px;
    max-width: 450px;
    width: 90%;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    animation: modalFadeIn 0.25s ease;
    overflow: hidden;
}
.cart-success-header {
    padding: 25px 25px 0 25px;
    text-align: center;
}
.cart-success-header h3 {
    font-family: "Inter-Bold", sans-serif;
    font-size: 22px;
    color: #00a896;
    margin: 0 0 10px;
}
.cart-success-icon {
    width: 60px;
    height: 60px;
    background: #00a896;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
}
.cart-success-icon i {
    font-size: 30px;
    color: white;
}
.cart-success-body {
    padding: 20px 25px;
    text-align: center;
}
.cart-success-body p {
    font-size: 16px;
    color: #333;
    margin: 5px 0;
}
.cart-success-footer {
    display: flex;
    gap: 12px;
    padding: 0 25px 25px 25px;
}
.btn-cart-success {
    flex: 1;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 500;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    text-decoration: none;
    display: inline-block;
}
.btn-continue {
    background: #f0f0f0;
    color: #333;
}
.btn-continue:hover {
    background: #e0e0e0;
}
.btn-to-cart {
    background: #00a896;
    color: white;
}
.btn-to-cart:hover {
    background: #008a7a;
}
@keyframes modalFadeIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}

/* ===== СТИЛИ ДЛЯ "ПОЗИЦИЯ НА ЗАКАЗ" ===== */
.order-to-order-badge {
    display: inline-block;
    background: #e6f7f4;
    color: #00a896;
    font-weight: 600;
    padding: 6px 20px;
    border-radius: 20px;
    font-size: 15px;
    border: 1px solid #b2dfdb;
    letter-spacing: 0.3px;
}

.order-to-order-total {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 24px;
    font-weight: 700;
    color: #00a896;
}

.order-to-order-total i {
    font-size: 28px;
    color: #00a896;
}

.order-to-order-btn {
    background: #00a896;
    color: #ffffff;
    border: none;
    padding: 16px 40px;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 15px rgba(0, 168, 150, 0.3);
}

.order-to-order-btn:hover {
    background: #008a7a;
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(0, 168, 150, 0.4);
}

.order-to-order-btn i {
    font-size: 20px;
}

.order-total-wrapper {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
}

.order-total-label {
    font-size: 16px;
    color: #4a6a65;
    font-weight: 500;
}
</style>
</head>
<body>
<div class="screen">
<div class="div">
<?php 
$context = 'mip';
require_once '../header.php'; 
?>

<div class="product-page-wrapper">
    
    <!-- Заголовок -->
    <div class="prod-header">
        <h1 class="prod-title"><?= htmlspecialchars($product['name']) ?></h1>
        <?php if ($isMadeToOrder): ?>
            <div style="margin-top: 12px;">
                <span class="order-to-order-badge">
                    <i class="fas fa-clock" style="margin-right: 8px;"></i> Позиция на заказ
                </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Галерея -->
    <?php if (!empty($images)): ?>
    <div class="prod-gallery">
        <div class="prod-gallery-main">
            <img id="mainImage" src="<?= htmlspecialchars(getImageUrl($images[0]['image_url'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <?php if (count($images) > 1): ?>
        <div class="prod-thumbs">
            <?php foreach ($images as $img): ?>
            <img src="<?= htmlspecialchars(getImageUrl($img['image_url'])) ?>" 
                 class="<?= ($img === $images[0]) ? 'active' : '' ?>" 
                 onclick="changeMainImage('<?= htmlspecialchars(getImageUrl($img['image_url'])) ?>', this)">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Описание -->
    <?php if (!empty($product['full_description'])): ?>
    <section class="prod-section">
        <h2>Описание</h2>
        <div class="desc-content">
            <?= $product['full_description'] ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Конфигуратор заказа -->
    <section class="prod-section2" id="orderBlock">
        <h2>Конфигуратор заказа</h2>
        
        <!-- Комплектации -->
        <?php if (!empty($configurations)): ?>
        <h3>Выберите комплектацию</h3>
        <table class="config-table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Характеристики</th>
                    <th>Цена</th>
                    <th>Выбор</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($configurations as $cfg): 
                    $chars = !empty($cfg['characteristics']) ? json_decode($cfg['characteristics'], true) : [];
                    if (!is_array($chars)) $chars = [];
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($cfg['name']) ?></strong></td>
                    <td>
                        <?php if (!empty($chars)): ?>
                            <?php foreach ($chars as $k => $v): ?>
                                <?php if (!in_array($k, ['stock', 'made_to_order', 'lead_time'])): ?>
                                    <div><small><?= htmlspecialchars($k) ?>:</small> <?= htmlspecialchars($v) ?></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span style="color:#999;">—</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= number_format($cfg['price'], 0, ',', ' ') ?> ₽</strong></td>
                    <td>
                        <input type="radio" name="configuration" value="<?= $cfg['id'] ?>" 
                               data-price="<?= $cfg['price'] ?>"
                               data-name="<?= htmlspecialchars($cfg['name']) ?>"
                               onchange="updateTotal()" style="transform: scale(1.5);">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Модификации -->
        <?php if (!empty($modifications)): ?>
        <div class="modifications-table-wrapper">
            <h3>Дополнительные опции</h3>
            <table class="modifications-table">
                <thead>
                    <tr>
                        <th style="width: 15%">Модификация</th>
                        <th style="width: 20%">Вариант</th>
                        <th style="width: 25%">Описание</th>
                        <th style="width: 15%">Цена</th>
                        <th style="width: 15%">Доп. свойство</th>
                        <th style="width: 10%">Цена доп.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modifications as $mod):
                    $options = json_decode($mod['options'], true) ?? [];
                    if (!is_array($options)) $options = [];
                    ?>
                    <tr data-mod-id="<?= $mod['id'] ?>">
                        <td class="mod-name"><?= htmlspecialchars($mod['group_name']) ?></td>
                        
                        <td class="mod-select-cell">
                            <select class="mod-variant-select" onchange="updatePropertiesList(this)">
                                <option value="0" data-props='[]' data-desc="" data-price="0" data-name="">— Не выбрано —</option>
                                <?php foreach ($options as $opt):
                                $propsJson = htmlspecialchars(json_encode($opt['properties'] ?? []), ENT_QUOTES, 'UTF-8');
                                $desc = htmlspecialchars($opt['description'] ?? 'Нет описания', ENT_QUOTES, 'UTF-8');
                                $price = number_format($opt['price'] ?? 0, 2, ',', ' ');
                                ?>
                                <option value="<?= $opt['price'] ?>" 
                                        data-props='<?= $propsJson ?>' 
                                        data-desc='<?= $desc ?>'
                                        data-price='<?= $price ?>'
                                        data-name='<?= htmlspecialchars($opt['name']) ?>'>
                                    <?= htmlspecialchars($opt['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        
                        <td class="mod-desc-cell">
                            <span class="mod-desc-text">—</span>
                        </td>
                        
                        <td class="mod-price-cell">
                            <span class="mod-price-value">0 ₽</span>
                        </td>
                        
                        <td class="mod-property-cell">
                            <select class="mod-property-select" disabled onchange="updatePropertyPrice(this)">
                                <option value="0" data-price="0">— Выберите вариант —</option>
                            </select>
                        </td>
                        
                        <td class="mod-prop-price-cell">
                            <span class="mod-prop-price-value">0 ₽</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Итого и кнопка -->
        <div class="order-summary-wrapper">
            <div class="order-selected-list">
                <h4>Ваша конфигурация:</h4>
                <div id="selectedConfigList" class="selected-items">
                    <p class="empty-selection">Пока ничего не выбрано</p>
                </div>
            </div>
            
            <div class="order-total-wrapper">
                <?php if ($isMadeToOrder): ?>
                    <div class="order-total order-to-order-total">
                        <i class="fas fa-clock"></i>
                        <span>Позиция на заказ</span>
                    </div>
                    <button class="order-to-order-btn" onclick="showOrderInfo()">
                        <i class="fas fa-info-circle"></i> Уточнить сроки
                    </button>
                <?php else: ?>
                    <div class="order-total">
                        Итого: <span id="totalPrice"><?= number_format($product['base_price'], 2, ',', ' ') ?> ₽</span>
                    </div>
                    <button class="btn-order1" onclick="addToCart(<?= $product_id ?>, this)">В корзину</button>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Схемы -->
    <?php if (!empty($schemes)): ?>
    <section class="schemes-section">
        <h2>Схемы применения</h2>
        <div class="schemes-grid">
            <?php foreach ($schemes as $idx => $scheme): ?>
            <div class="scheme-card" onclick="openSchemeLightbox(<?= $idx ?>)">
                <?php if ($scheme['image_url']): ?>
                <img src="<?= htmlspecialchars($scheme['image_url']) ?>" 
                     alt="<?= htmlspecialchars($scheme['title'] ?? 'Схема') ?>"
                     loading="lazy">
                <div class="scheme-zoom-hint">
                    <i class="fas fa-search-plus"></i>
                </div>
                <?php endif; ?>
                <?php if ($scheme['title']): ?><h3><?= htmlspecialchars($scheme['title']) ?></h3><?php endif; ?>
                <?php if ($scheme['description']): ?><p><?= nl2br(htmlspecialchars($scheme['description'])) ?></p><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Лайтбокс для схем -->
    <div id="schemeLightbox" class="scheme-lightbox" onclick="closeSchemeLightbox(event)">
        <span class="scheme-lightbox-close" onclick="closeSchemeLightbox(event)">&times;</span>
        <div class="scheme-lightbox-content">
            <img id="lightboxImg" class="scheme-lightbox-img" src="" alt="">
            <div id="lightboxTitle" class="scheme-lightbox-title"></div>
        </div>
        <div class="scheme-lightbox-nav scheme-lightbox-prev" onclick="navigateScheme(-1)">&#10094;</div>
        <div class="scheme-lightbox-nav scheme-lightbox-next" onclick="navigateScheme(1)">&#10095;</div>
    </div>
    <?php endif; ?>

    <!-- Файлы -->
    <?php if (!empty($files)): ?>
    <section class="prod-section">
        <h2>Файлы</h2>
        <?php 
        $groups = [];
        foreach ($files as $f) $groups[$f['group_name']][] = $f;
        foreach ($groups as $gName => $gFiles): 
        ?>
        <div class="files-group">
            <h3><?= htmlspecialchars($gName) ?></h3>
            <ul class="files-list">
                <?php foreach ($gFiles as $f): ?>
                <li>
                    <a href="<?= htmlspecialchars($f['file_url']) ?>" download>
                        <i class="fas fa-file-pdf"></i>
                        <span><?= htmlspecialchars($f['file_name']) ?></span>
                        <?php if ($f['file_size']): ?>
                            <span class="file-size">(<?= round($f['file_size']/1024, 1) ?> Кб)</span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

</div>

<!-- Модальное окно "Товар добавлен в корзину" -->
<div id="cartSuccessModal" class="cart-success-modal">
    <div class="cart-success-content">
        <div class="cart-success-header">
            <div class="cart-success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h3>Товар добавлен в корзину!</h3>
        </div>
        <div class="cart-success-body">
            <p>Товар успешно добавлен в вашу корзину.</p>
        </div>
        <div class="cart-success-footer">
            <button class="btn-cart-success btn-continue" onclick="closeCartSuccessModal()">
                Продолжить покупки
            </button>
            <a href="cart.php" class="btn-cart-success btn-to-cart">
                Перейти в корзину
            </a>
        </div>
    </div>
</div>

<!-- Модальное окно для "Позиция на заказ" -->
<div id="orderInfoModal" class="cart-success-modal" style="display:none;">
    <div class="cart-success-content">
        <div class="cart-success-header">
            <div class="cart-success-icon" style="background: #00a896;">
                <i class="fas fa-clock"></i>
            </div>
            <h3 style="color: #00a896;">Позиция на заказ</h3>
        </div>
        <div class="cart-success-body">
            <p style="font-size: 16px; color: #333; line-height: 1.6;">
                Данный товар изготавливается на заказ.
            </p>
            <p style="font-size: 15px; color: #4a6a65; margin-top: 10px;">
                Для получения информации о сроках изготовления и стоимости, 
                пожалуйста, свяжитесь с нашими менеджерами.
            </p>
            <div style="margin-top: 20px; padding: 15px; background: #f0f8f6; border-radius: 10px;">
                <p style="font-size: 14px; color: #4a6a65; margin: 0;">
                    <i class="fas fa-phone" style="color: #00a896; margin-right: 8px;"></i>
                    <?php 
                        // Получаем телефон из настроек
                        $phone = '+7 (3022) XX-XX-XX';
                        try {
                            $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'contact_phone'");
                            $stmt->execute();
                            $phoneSetting = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($phoneSetting) $phone = $phoneSetting['value'];
                        } catch (Exception $e) {}
                        echo htmlspecialchars($phone);
                    ?>
                </p>
                <p style="font-size: 14px; color: #4a6a65; margin: 5px 0 0;">
                    <i class="fas fa-envelope" style="color: #00a896; margin-right: 8px;"></i>
                    <?php 
                        $email = 'info@example.com';
                        try {
                            $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'contact_email'");
                            $stmt->execute();
                            $emailSetting = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($emailSetting) $email = $emailSetting['value'];
                        } catch (Exception $e) {}
                        echo htmlspecialchars($email);
                    ?>
                </p>
            </div>
        </div>
        <div class="cart-success-footer">
            <button class="btn-cart-success btn-continue" onclick="closeOrderInfoModal()" style="flex:1;">
                Закрыть
            </button>
        </div>
    </div>
</div>

<?php
if (!isset($context)) {
    $context = 'lab';
}
require_once '../footer.php';
?>
</div>
</div>

<script>

function changeMainImage(src, thumb) {
    const mainImage = document.getElementById('mainImage');
    if (mainImage) {
        mainImage.src = src;
    }
    document.querySelectorAll('.prod-thumbs img').forEach(t => {
        t.classList.remove('active');
        if (t.getAttribute('src') === src) {
            t.classList.add('active');
        }
    });
}

<?php if (!$isMadeToOrder): // JS для цены только если не "Позиция на заказ" ?>
let basePrice = <?= (float)$product['base_price'] ?>;

document.querySelectorAll('input[name="configuration"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.config-table tr').forEach(row => {
            row.style.background = '';
        });
        this.closest('tr').style.background = '#f0f4ff';
        updateSelectedList();
        updateTotal();
    });
});

document.querySelectorAll('.mod-variant-select').forEach(select => {
    select.addEventListener('change', function() {
        updatePropertiesList(this);
        updateSelectedList();
        updateTotal();
    });
});

document.querySelectorAll('.mod-property-select').forEach(select => {
    select.addEventListener('change', function() {
        updateSelectedList();
        updateTotal();
    });
});

function updatePropertiesList(selectElement) {
    const row = selectElement.closest('tr');
    const propertySelect = row.querySelector('.mod-property-select');
    const priceCell = row.querySelector('.mod-price-value');
    if (!propertySelect) return;
    
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const propertiesJson = selectedOption.getAttribute('data-props');
    const properties = JSON.parse(propertiesJson || '[]');
    const variantPrice = parseFloat(selectElement.value) || 0;
    
    if (priceCell) {
        priceCell.textContent = variantPrice.toFixed(2).replace('.', ',') + ' ₽';
    }
    
    propertySelect.innerHTML = '<option value="0" data-price="0">— Сначала выберите вариант —</option>';
    
    if (properties.length === 0) {
        propertySelect.innerHTML = '<option value="0" data-price="0">— Нет доп. опций —</option>';
        propertySelect.disabled = true;
        const propPriceCell = row.querySelector('.mod-prop-price-value');
        if (propPriceCell) propPriceCell.textContent = '0 ₽';
    } else {
        propertySelect.innerHTML = '<option value="0" data-price="0">— Без доп. свойства —</option>';
        properties.forEach(prop => {
            const opt = document.createElement('option');
            opt.value = prop.price || 0;
            opt.setAttribute('data-price', prop.price || 0);
            opt.text = prop.name;
            propertySelect.appendChild(opt);
        });
        propertySelect.disabled = false;
        propertySelect.value = 0;
        const propPriceCell = row.querySelector('.mod-prop-price-value');
        if (propPriceCell) propPriceCell.textContent = '0 ₽';
    }
    
    updateTotal();
}

function updatePropertyPrice(selectElement) {
    const row = selectElement.closest('tr');
    const propPriceCell = row.querySelector('.mod-prop-price-value');
    const price = parseFloat(selectElement.value) || 0;
    
    if (propPriceCell) {
        propPriceCell.textContent = price.toFixed(2).replace('.', ',') + ' ₽';
    }
    
    updateTotal();
}

function updateSelectedList() {
    const listContainer = document.getElementById('selectedConfigList');
    if (!listContainer) return;
    
    let html = '';
    let hasSelection = false;
    
    const configChecked = document.querySelector('input[name="configuration"]:checked');
    if (configChecked) {
        const configName = configChecked.getAttribute('data-name') || 'Комплектация';
        const configPrice = parseFloat(configChecked.dataset.price) || 0;
        
        html += `<div class="selected-item">
            <span class="selected-item-name">${configName}</span>
            <span class="selected-item-price">${configPrice.toLocaleString('ru-RU')} ₽</span>
        </div>`;
        hasSelection = true;
    }
    
    document.querySelectorAll('.modifications-table tbody tr').forEach(row => {
        const modName = row.querySelector('.mod-name')?.textContent || '';
        const variantSelect = row.querySelector('.mod-variant-select');
        const propertySelect = row.querySelector('.mod-property-select');
        
        if (variantSelect && variantSelect.value > 0) {
            const variantOption = variantSelect.options[variantSelect.selectedIndex];
            const variantName = variantOption.getAttribute('data-name') || variantOption.text.split('(')[0].trim();
            const variantPrice = parseFloat(variantSelect.value) || 0;
            
            html += `<div class="selected-item">
                <span class="selected-item-name">${modName}: ${variantName}</span>
                <span class="selected-item-price">${variantPrice.toLocaleString('ru-RU')} ₽</span>
            </div>`;
            hasSelection = true;
            
            if (propertySelect && !propertySelect.disabled && propertySelect.value > 0) {
                const propOption = propertySelect.options[propertySelect.selectedIndex];
                const propName = propOption.text.split('(')[0].trim();
                const propPrice = parseFloat(propertySelect.value) || 0;
                
                html += `<div class="selected-item" style="padding-left:20px;font-size:13px;">
                    <span class="selected-item-char">${propName}</span>
                    <span class="selected-item-price">${propPrice.toLocaleString('ru-RU')} ₽</span>
                </div>`;
            }
        }
    });
    
    if (!hasSelection) {
        html = '<p class="empty-selection">Пока ничего не выбрано</p>';
    }
    listContainer.innerHTML = html;
}

function updateTotal() {
    let total = basePrice;
    
    const configChecked = document.querySelector('input[name="configuration"]:checked');
    if (configChecked) {
        total = parseFloat(configChecked.dataset.price) || basePrice;
    }
    
    document.querySelectorAll('.mod-variant-select').forEach(select => {
        total += parseFloat(select.value) || 0;
    });
    
    document.querySelectorAll('.mod-property-select').forEach(select => {
        if (!select.disabled && select.value > 0) {
            total += parseFloat(select.value) || 0;
        }
    });
    
    const totalElement = document.getElementById('totalPrice');
    if (totalElement) {
        totalElement.textContent = total.toFixed(2).replace('.', ',') + ' ₽';
    }
}
<?php endif; ?>

/* Модальное окно успешного добавления */
function showCartSuccessModal() {
    const modal = document.getElementById('cartSuccessModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeCartSuccessModal() {
    const modal = document.getElementById('cartSuccessModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

/* Модальное окно "Позиция на заказ" */
function showOrderInfo() {
    const modal = document.getElementById('orderInfoModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeOrderInfoModal() {
    const modal = document.getElementById('orderInfoModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

/* Добавление в корзину с проверкой авторизации */
function addToCart(productId, btnElement) {
    const btn = btnElement || document.querySelector('.btn-order1');
    
    // Проверка комплектации
    const configRadios = document.querySelectorAll('input[name="configuration"]');
    if (configRadios.length > 0) {
        const configChecked = document.querySelector('input[name="configuration"]:checked');
        if (!configChecked) {
            alert('⚠️ Пожалуйста, выберите базовую комплектацию!');
            document.getElementById('orderBlock').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            return;
        }
    }
    
    // Сначала проверяем авторизацию и роль
    fetch('check_auth_status.php')
        .then(res => res.json())
        .then(authData => {
            if (!authData.is_logged_in) {
                showAuthModal();
                return;
            }
            if (authData.role !== 'client') {
                showRoleErrorModal(authData.role);
                return;
            }
            
            // Авторизован и роль client — продолжаем
            proceedAddToCart(productId, btn);
        })
        .catch(err => {
            console.error('Ошибка проверки авторизации:', err);
            alert('Ошибка проверки авторизации');
        });
}

// Оригинальная логика добавления в корзину
function proceedAddToCart(productId, btn) {
    let totalPrice = basePrice;
    
    const orderData = {
        product_id: productId,
        base_price: basePrice,
        configuration: null,
        configuration_name: '',
        modifications: [],
        total_price: 0
    };
    
    // Комплектация
    const configChecked = document.querySelector('input[name="configuration"]:checked');
    if (configChecked) {
        const configPrice = parseFloat(configChecked.dataset.price) || 0;
        const configName = configChecked.getAttribute('data-name') || '';
        
        orderData.configuration = {
            id: configChecked.value,
            price: configPrice
        };
        orderData.configuration_name = configName;
        
        totalPrice = configPrice;
    }
    
    // Модификации
    document.querySelectorAll('.modifications-table tbody tr').forEach(row => {
        const variantSelect = row.querySelector('.mod-variant-select');
        const propertySelect = row.querySelector('.mod-property-select');
        const modName = row.querySelector('.mod-name')?.textContent?.trim() || '';
        
        if (variantSelect && variantSelect.value > 0) {
            const variantOption = variantSelect.options[variantSelect.selectedIndex];
            const variantPrice = parseFloat(variantSelect.value) || 0;
            const variantName = variantOption.getAttribute('data-name') || variantOption.text.split('(')[0].trim();
            
            const modData = {
                group: modName,
                variant: {
                    name: variantName,
                    price: variantPrice
                },
                property: null
            };
            
            totalPrice += variantPrice;
            
            if (propertySelect && !propertySelect.disabled && propertySelect.value > 0) {
                const propOption = propertySelect.options[propertySelect.selectedIndex];
                const propPrice = parseFloat(propertySelect.value) || 0;
                const propName = propOption.text.split('(')[0].trim();
                
                modData.property = {
                    name: propName,
                    price: propPrice
                };
                
                totalPrice += propPrice;
            }
            
            orderData.modifications.push(modData);
        }
    });
    
    orderData.total_price = totalPrice;
    
    const originalText = btn?.textContent || 'Заказать';
    if (btn) {
        btn.textContent = 'Добавление...';
        btn.disabled = true;
    }
    
    fetch('cart.php?action=add&id=' + productId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Показать модальное окно вместо прямого перехода
            showCartSuccessModal();
            if (btn) {
                btn.textContent = originalText;
                btn.disabled = false;
            }
        } else {
            throw new Error(data.error || 'Неизвестная ошибка');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('❌ Ошибка добавления в корзину:\n' + error.message);
        if (btn) {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    });
}


// ============================================
// МОДАЛЬНЫЕ ОКНА
// ============================================
function showAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function showRoleErrorModal(role) {
    const roleSpan = document.getElementById('userRoleProduct');
    if (roleSpan) roleSpan.textContent = role || 'неизвестна';
    const modal = document.getElementById('roleErrorModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeRoleErrorModal() {
    const modal = document.getElementById('roleErrorModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function logoutAndRedirect() {
    window.location.href = '../logout.php';
}

// Закрытие по Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAuthModal();
        closeRoleErrorModal();
        closeCartSuccessModal();
        closeOrderInfoModal();
    }
});

// Закрытие по клику на фон
const authModal = document.getElementById('authModal');
if (authModal) {
    authModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeAuthModal();
        }
    });
}

const roleModal = document.getElementById('roleErrorModal');
if (roleModal) {
    roleModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeRoleErrorModal();
        }
    });
}

const cartSuccessModal = document.getElementById('cartSuccessModal');
if (cartSuccessModal) {
    cartSuccessModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeCartSuccessModal();
        }
    });
}

const orderInfoModal = document.getElementById('orderInfoModal');
if (orderInfoModal) {
    orderInfoModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeOrderInfoModal();
        }
    });
}

function openSchemeLightbox(index) {
    if (schemeImages.length === 0) return;
    currentSchemeIndex = index;
    const lightbox = document.getElementById('schemeLightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const lightboxTitle = document.getElementById('lightboxTitle');
    
    if (lightbox && lightboxImg) {
        lightboxImg.src = schemeImages[index].src;
        lightboxImg.alt = schemeImages[index].alt;
        const card = document.querySelectorAll('.scheme-card')[index];
        if (card) {
            const titleEl = card.querySelector('h3');
            lightboxTitle.textContent = titleEl ? titleEl.textContent : '';
        }
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeSchemeLightbox(event) {
    if (event && event.target !== event.currentTarget && 
        !event.target.classList.contains('scheme-lightbox-close')) {
        return;
    }
    const lightbox = document.getElementById('schemeLightbox');
    if (lightbox) {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function navigateScheme(direction) {
    if (schemeImages.length === 0) return;
    currentSchemeIndex = (currentSchemeIndex + direction + schemeImages.length) % schemeImages.length;
    const lightboxImg = document.getElementById('lightboxImg');
    const lightboxTitle = document.getElementById('lightboxTitle');
    if (lightboxImg) {
        lightboxImg.src = schemeImages[currentSchemeIndex].src;
        lightboxImg.alt = schemeImages[currentSchemeIndex].alt;
        const card = document.querySelectorAll('.scheme-card')[currentSchemeIndex];
        if (card && lightboxTitle) {
            const titleEl = card.querySelector('h3');
            lightboxTitle.textContent = titleEl ? titleEl.textContent : '';
        }
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSchemeLightbox();
    else if (e.key === 'ArrowLeft') navigateScheme(-1);
    else if (e.key === 'ArrowRight') navigateScheme(1);
});

// Схемы для лайтбокса
const schemeImages = <?php 
    $schemeData = [];
    foreach ($schemes as $idx => $scheme) {
        $schemeData[] = [
            'src' => htmlspecialchars($scheme['image_url']),
            'alt' => htmlspecialchars($scheme['title'] ?? 'Схема')
        ];
    }
    echo json_encode($schemeData);
?>;
let currentSchemeIndex = 0;
</script>

<!-- Модальное окно для авторизации -->
<div id="authModal" class="auth-modal">
    <div class="auth-modal-content">
        <div class="auth-modal-header">
            <h3>Требуется авторизация</h3>
            <button class="auth-modal-close" onclick="closeAuthModal()">&times;</button>
        </div>
        <div class="auth-modal-body">
            <p>Для добавления товара в корзину необходимо войти в личный кабинет.</p>
        </div>
        <div class="auth-modal-footer">
            <a href="../authorization.php" class="btn-auth btn-login-page">Войти</a>
            <a href="../authorization.php#register" class="btn-auth btn-register-page">Зарегистрироваться</a>
            <button class="btn-auth btn-cancel" onclick="closeAuthModal()">Отмена</button>
        </div>
    </div>
</div>

<!-- Модальное окно для ошибки роли -->
<div id="roleErrorModal" class="role-modal">
    <div class="role-modal-content">
        <div class="role-modal-header">
            <h3>Доступ запрещён</h3>
            <button class="role-modal-close" onclick="closeRoleErrorModal()">&times;</button>
        </div>
        <div class="role-modal-body">
            <p>Добавление товаров в корзину доступно только клиентам.</p>
            <p>Ваша роль: <strong id="userRoleProduct"></strong></p>
        </div>
        <div class="role-modal-footer">
            <button class="btn-role btn-role-logout" onclick="logoutAndRedirect()">Выйти</button>
            <button class="btn-role btn-role-cancel" onclick="closeRoleErrorModal()">Закрыть</button>
        </div>
    </div>
</div>
</body>
</html>