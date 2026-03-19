<?php
session_start();
require_once 'config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

try {
    // Получаем основной продукт
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("<h2 style='text-align:center;color:#d9534f;margin-top:150px;'>Продукт не найден.</h2>");
    }

    // Получаем изображения
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, is_main DESC");
    $stmt->execute([$product_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получаем комплектации
    $stmt = $pdo->prepare("SELECT * FROM product_configurations WHERE product_id = ? ORDER BY sort_order");
    $stmt->execute([$product_id]);
    $configurations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получаем модификации
    $stmt = $pdo->prepare("SELECT * FROM product_modifications WHERE product_id = ? ORDER BY sort_order");
    $stmt->execute([$product_id]);
    $modifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получаем схемы применения
    $stmt = $pdo->prepare("SELECT * FROM product_schemes WHERE product_id = ? ORDER BY sort_order");
    $stmt->execute([$product_id]);
    $schemes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получаем файлы
    $stmt = $pdo->prepare("SELECT * FROM product_files WHERE product_id = ? ORDER BY sort_order");
    $stmt->execute([$product_id]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ВАРИАНТЫ ИСПОЛЬЗОВАНИЯ УДАЛЕНЫ ИЗ ЗАПРОСА

} catch (Exception $e) {
    error_log($e->getMessage());
    die("<h2 style='text-align:center;color:#d9534f;margin-top:150px;'>Ошибка загрузки.</h2>");
}

$inCart = isset($_SESSION['cart'][$product_id]);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="style_main.css" />
    <link rel="stylesheet" href="css/style_header_footer.css" />
    <link rel="stylesheet" href="css/header_mip.css" />
    <title><?= htmlspecialchars($product['name']) ?></title>

    <!-- Минимальные стили только для этой страницы -->
    <style>
        .product-page-new { max-width: 1000px; margin: 120px auto 40px; padding: 0 20px; font-family: sans-serif; }
        .product-name { text-align: center; margin-bottom: 30px; }

        /* Галерея */
        .gallery-main img { max-width: 100%; height: auto; max-height: 500px; display: block; margin: 0 auto; }
        .gallery-thumbs { display: flex; gap: 10px; justify-content: center; margin-top: 10px; }
        .gallery-thumbs img { width: 80px; height: 80px; object-fit: cover; cursor: pointer; opacity: 0.6; }
        .gallery-thumbs img.active { opacity: 1; border: 2px solid #1a1982; }

        /* Описание */
        .description-content { background: #fff; padding: 20px; margin: 20px 0; line-height: 1.6; }
        .description-content h2 { color: #1a1982; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .description-content table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .description-content td, .description-content th { border: 1px solid #ddd; padding: 8px; text-align: left; }

        /* Конфигуратор */
        .order-block-section { background: #f9f9f9; padding: 20px; margin: 30px 0; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #fff; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; vertical-align: top; }
        th { background: #eee; }

        select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }

        .order-total-block { text-align: right; margin-top: 20px; padding-top: 20px; border-top: 2px solid #1a1982; }
        .order-total { font-size: 24px; font-weight: bold; color: #1a1982; margin-bottom: 15px; }

        .btn-order { background: #1a1982; color: #fff; border: none; padding: 15px 40px; font-size: 18px; cursor: pointer; border-radius: 5px; }
        .btn-order:hover { background: #14136b; }
        .btn-order:disabled { background: #ccc; cursor: not-allowed; }

        /* Схемы и Файлы */
        .schemes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .scheme-card { border: 1px solid #eee; padding: 10px; border-radius: 5px; }
        .scheme-card img { width: 100%; height: 150px; object-fit: cover; }
        .files-group { margin-bottom: 20px; }
        .files-list { list-style: none; padding: 0; }
        .files-list li { margin-bottom: 5px; }
        .files-list a { text-decoration: none; color: #1a1982; font-weight: 500; }
        .files-list a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="screen">
        <div class="div">
            <?php require_once 'header_mip.php'; ?>

            <div class="product-page-new">

                <!-- 1. НАЗВАНИЕ -->
                <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>

                <!-- 2. ГАЛЕРЕЯ -->
                <?php if (!empty($images)): ?>
                <section class="product-gallery-section">
                    <div class="gallery-main">
                        <img id="mainImage" src="<?= htmlspecialchars($images[0]['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div class="gallery-thumbs">
                        <?php foreach ($images as $img): ?>
                        <img src="<?= htmlspecialchars($img['image_url']) ?>" class="thumb <?= $img['is_main'] ? 'active' : '' ?>" onclick="changeMainImage('<?= htmlspecialchars($img['image_url']) ?>')">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <!-- 3. ОПИСАНИЕ -->
                <?php if (!empty($product['full_description'])): ?>
                <section class="product-description-section">
                    <div class="description-content">
                        <?= $product['full_description'] ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- 4. БЛОК ЗАКАЗА -->
                <section class="order-block-section" id="orderBlock">
                    <h2>Конфигуратор заказа</h2>

                    <!-- 4.1 Комплектации -->
                    <?php if (!empty($configurations)): ?>
                    <h3>Выберите комплектацию</h3>
                    <table class="configurations-table">
                        <thead>
                            <tr>
                                <th>Комплектация</th>
                                <th>Характеристики</th>
                                <th>Цена</th>
                                <th>Выбор</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($configurations as $config):
                                $chars = json_decode($config['characteristics'], true) ?? [];
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($config['name']) ?></strong></td>
                                <td>
                                    <?php foreach ($chars as $key => $value): ?>
                                    <div><?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?></div>
                                    <?php endforeach; ?>
                                </td>
                                <td><?= number_format($config['price'], 2, ',', ' ') ?> ₽</td>
                                <td>
                                    <label>
                                        <input type="radio" name="configuration" value="<?= $config['id'] ?>" data-price="<?= $config['price'] ?>">
                                    </label>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <!-- 4.2 Таблица доп. модификаций (Зависимые списки) -->
                    <?php if (!empty($modifications)): ?>
                    <div class="modifications-table-wrapper">
                        <h3>Дополнительные опции</h3>
                        <table class="modifications-table">
                            <thead>
                                <tr>
                                    <th style="width: 25%">Модификация</th>
                                    <th style="width: 37%">Вариант (База)</th>
                                    <th style="width: 38%">Доп. свойство (Зависит от варианта)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modifications as $mod):
                                    // Декодируем JSON. Ожидаем структуру: массив объектов, у каждого есть поле 'properties'
                                    $options = json_decode($mod['options'], true) ?? [];
                                ?>
                                <tr data-mod-id="<?= $mod['id'] ?>">
                                    <td class="mod-name"><?= htmlspecialchars($mod['group_name']) ?></td>

                                    <!-- Столбец 2: Выбор варианта -->
                                    <td class="mod-select-cell">
                                        <select class="mod-variant-select" onchange="updatePropertiesList(this)">
                                            <option value="0" data-props='[]'>— Не выбрано —</option>
                                            <?php foreach ($options as $opt):
                                                // Сохраняем весь массив свойств в data-attribute для быстрого доступа JS
                                                $propsJson = htmlspecialchars(json_encode($opt['properties'] ?? []), ENT_QUOTES, 'UTF-8');
                                            ?>
                                            <option value="<?= $opt['price'] ?>" data-props='<?= $propsJson ?>'>
                                                <?= htmlspecialchars($opt['name']) ?> (+<?= number_format($opt['price'], 2, ',', ' ') ?> ₽)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>

                                    <!-- Столбец 3: Выбор свойства (заполняется динамически) -->
                                    <td class="mod-property-cell">
                                        <select class="mod-property-select" disabled onchange="updateTotal()">
                                            <option value="0">— Сначала выберите вариант —</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <!-- 4.3 Итого и кнопка -->
                    <div class="order-total-block">
                        <div class="order-total">
                            Итого: <span id="totalPrice"><?= number_format($product['base_price'], 2, ',', ' ') ?> ₽</span>
                        </div>

                        
                        <button class="btn-order" onclick="addToCart(<?= $product_id ?>)">Заказать</button>
                        
                    </div>
                </section>

                <!-- 5. СХЕМЫ ПРИМЕНЕНИЯ -->
                <?php if (!empty($schemes)): ?>
                <section class="schemes-section">
                    <h2>Схемы применения</h2>
                    <div class="schemes-grid">
                        <?php foreach ($schemes as $scheme): ?>
                        <div class="scheme-card">
                            <?php if ($scheme['image_url']): ?>
                            <img src="<?= htmlspecialchars($scheme['image_url']) ?>" alt="">
                            <?php endif; ?>
                            <?php if ($scheme['title']): ?><h3><?= htmlspecialchars($scheme['title']) ?></h3><?php endif; ?>
                            <?php if ($scheme['description']): ?><p><?= nl2br(htmlspecialchars($scheme['description'])) ?></p><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- 6. ФАЙЛЫ -->
                <?php if (!empty($files)): ?>
                <section class="files-section">
                    <h2>Файлы</h2>
                    <?php
                    $files_grouped = [];
                    foreach ($files as $file) { $files_grouped[$file['group_name']][] = $file; }
                    foreach ($files_grouped as $group_name => $group_files):
                    ?>
                    <div class="files-group">
                        <h3><?= htmlspecialchars($group_name) ?></h3>
                        <ul class="files-list">
                            <?php foreach ($group_files as $file): ?>
                            <li>
                                <a href="<?= htmlspecialchars($file['file_url']) ?>" download>
                                    <?= htmlspecialchars($file['file_name']) ?>
                                    <?php if ($file['file_size']): ?>(<?= round($file['file_size'] / 1024, 1) ?> KB)<?php endif; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </section>
                <?php endif; ?>

                <!-- 7. ВАРИАНТЫ ИСПОЛЬЗОВАНИЯ -- УДАЛЕНО -->

            </div>

            <?php require_once 'footer_mip.php'; ?>
        </div>
    </div>

    <script>
        // Переключение фото
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
        }

        let basePrice = <?= $product['base_price'] ?>;

        // Слушаем радио-кнопки комплектаций
        document.querySelectorAll('input[name="configuration"]').forEach(radio => {
            radio.addEventListener('change', function() {
                basePrice = parseFloat(this.dataset.price);
                updateTotal();
            });
        });

        /**
         * Функция обновления списка свойств при выборе варианта
         * Вызывается при изменении второго столбца (mod-variant-select)
         */
        function updatePropertiesList(selectElement) {
            // Находим строку таблицы, где произошло изменение
            const row = selectElement.closest('tr');
            const propertySelect = row.querySelector('.mod-property-select');

            // Получаем JSON со свойствами из выбранного option
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const propertiesJson = selectedOption.getAttribute('data-props');
            const properties = JSON.parse(propertiesJson || '[]');

            // Очищаем текущий список свойств
            propertySelect.innerHTML = '';

            if (properties.length === 0) {
                const opt = document.createElement('option');
                opt.value = 0;
                opt.text = "Нет дополнительных опций";
                opt.disabled = true;
                opt.selected = true;
                propertySelect.appendChild(opt);
                propertySelect.disabled = true;
            } else {
                // Добавляем опцию "Не выбрано"
                const defaultOpt = document.createElement('option');
                defaultOpt.value = 0;
                defaultOpt.text = "— Без доп. свойства —";
                propertySelect.appendChild(defaultOpt);

                // Заполняем список свойствами из выбранного варианта
                properties.forEach(prop => {
                    const opt = document.createElement('option');
                    opt.value = prop.price || 0;
                    opt.text = `${prop.name} (+${Number(prop.price).toFixed(2).replace('.', ',')} ₽)`;
                    propertySelect.appendChild(opt);
                });

                propertySelect.disabled = false;
                propertySelect.value = 0; // Сбрасываем выбор на "без свойства"
            }

            // Пересчитываем итоговую цену (сбросив стоимость старого свойства)
            updateTotal();
        }

        // Функция пересчета итоговой цены
        function updateTotal() {
            let total = basePrice;

            // 1. Суммируем выбранные варианты (второй столбец)
            document.querySelectorAll('.mod-variant-select').forEach(select => {
                total += parseFloat(select.value) || 0;
            });

            // 2. Суммируем выбранные свойства (третий столбец)
            document.querySelectorAll('.mod-property-select').forEach(select => {
                if (!select.disabled) {
                    total += parseFloat(select.value) || 0;
                }
            });

            document.getElementById('totalPrice').textContent = total.toFixed(2).replace('.', ',') + ' ₽';
        }

        function addToCart(productId) {
            const configChecked = document.querySelector('input[name="configuration"]:checked');
            if (document.querySelectorAll('input[name="configuration"]').length > 0 && !configChecked) {
                alert('Пожалуйста, выберите базовую комплектацию.');
                return;
            }

            fetch('cart.php?action=add&id=' + productId)
            .then(() => {
                alert('Товар добавлен в корзину!');
                location.reload();
            });
        }
    </script>
</body>
</html>