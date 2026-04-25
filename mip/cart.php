<?php
session_start();
require_once 'config.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $current_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $redirect = 'authorization.php?redirect=' . urlencode($current_url);
    header('Location: ' . $redirect);
    exit;
}


if (isset($_GET['action']) && $_GET['action'] === 'add' && !empty($_GET['id'])) {
    header('Content-Type: application/json');
    
    $productId = (int)$_GET['id'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Уникальный ключ на основе конфигурации
    $configKey = 'prod_' . $productId;
    
    if (!empty($input['configuration'])) {
        $configKey .= '_cfg_' . $input['configuration']['id'];
    }
    
    if (!empty($input['modifications']) && is_array($input['modifications'])) {
        foreach ($input['modifications'] as $mod) {
            $configKey .= '_mod_' . md5(
                ($mod['variant']['name'] ?? '') . 
                ($mod['property']['name'] ?? '')
            );
        }
    }
    
    $configKey = md5($configKey);
    
    // Проверяем товар в БД
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Товар не найден']);
        exit;
    }
    
    // Инициализируем корзину
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Если товар с такой же конфигурацией уже есть — увеличиваем количество
    if (isset($_SESSION['cart'][$configKey])) {
        $_SESSION['cart'][$configKey]['quantity']++;
    } else {
        // Добавляем новую позицию с уникальной конфигурацией
        $_SESSION['cart'][$configKey] = [
            'config_key' => $configKey,
            'product_id' => $productId,
            'name' => $product['name'],
            'base_price' => (float)$product['base_price'],
            'configuration' => $input['configuration'] ?? null,
            'configuration_name' => $input['configuration_name'] ?? '',
            'modifications' => $input['modifications'] ?? [],
            'total_price' => (float)($input['total_price'] ?? $product['base_price']),
            'quantity' => 1,
            'img_url' => '',
            'added_at' => date('Y-m-d H:i:s')
        ];
        
        // Получаем изображение товара
        $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? LIMIT 1");
        $stmt->execute([$productId]);
        $img = $stmt->fetchColumn();
        $_SESSION['cart'][$configKey]['img_url'] = $img ?: 'img/placeholder.jpg';
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Товар добавлен в корзину',
        'cart_count' => count($_SESSION['cart'])
    ]);
    exit;
}


// Изменение кол-ва

if (isset($_POST['action']) && $_POST['action'] === 'update_qty') {
    header('Content-Type: application/json');
    
    $configKey = $_POST['config_key'] ?? '';
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    
    if (isset($_SESSION['cart'][$configKey])) {
        $_SESSION['cart'][$configKey]['quantity'] = $quantity;
        session_write_close();
    }
    
    echo json_encode(['success' => true]);
    exit;
}


// Удаление товара из корзины
if (isset($_GET['action']) && $_GET['action'] === 'remove' && !empty($_GET['key'])) {
    $configKey = $_GET['key'];
    unset($_SESSION['cart'][$configKey]);
    header('Location: cart.php');
    exit;
}

// Оформление закааз

if (isset($_POST['action']) && $_POST['action'] === 'checkout') {
    header('Content-Type: application/json');
    
    $address = trim($_POST['address'] ?? '');
    
    if (empty($address)) {
        echo json_encode(['error' => 'Укажите адрес доставки']);
        exit;
    }
    
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $configKey => $item) {
            // 🔥 Пересчитываем итоговую сумму на сервере для надёжности
            $lineTotal = (float)$item['total_price'] * (int)$item['quantity'];
            
            $message = json_encode([
                'quantity' => (int)$item['quantity'],
                'address' => $address,
                'product_name' => $item['name'],
                'product_id' => $item['product_id'],
                'configuration' => $item['configuration'],
                'configuration_name' => $item['configuration_name'],
                'modifications' => $item['modifications'],
                'unit_price' => (float)$item['total_price'],
                'line_total' => $lineTotal
            ], JSON_UNESCAPED_UNICODE);
            
            $pdo->prepare("
                INSERT INTO request (user_id, product_id, message, status, datetime, type)
                VALUES (?, ?, ?, 'new', NOW(), 'r')
            ")->execute([$_SESSION['user_id'], $item['product_id'], $message]);
        }
        
        // Очищаем корзину после успешного оформления
        $_SESSION['cart'] = [];
        session_write_close();
        
        echo json_encode(['success' => true]);
        exit;
    }
}

// Загрузка данных корзины

$cartItems = [];
$total = 0;
$totalQuantity = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $configKey => $item) {
        $subtotal = (float)$item['total_price'] * (int)$item['quantity'];
        $total += $subtotal;
        $totalQuantity += (int)$item['quantity'];
        
        $cartItems[] = [
            'config_key' => $configKey,
            'product_id' => $item['product_id'],
            'name' => $item['name'],
            'price' => (float)$item['total_price'],
            'img_url' => $item['img_url'],
            'quantity' => (int)$item['quantity'],
            'subtotal' => $subtotal,
            'configuration_name' => $item['configuration_name'] ?? '',
            'modifications' => $item['modifications']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/style_header_footer.css" />
<link rel="stylesheet" href="css/style_main.css" />
<link rel="stylesheet" href="css/header_mip.css" />
<link rel="stylesheet" href="css/style_cart.css" />
<title>Корзина</title>
</head>
<body>
<div class="screen">
<div class="div">
<?php 
				$context = 'mip';
				require_once '../header.php'; 
			?>

<div class="cart-page">
    <h1 class="cart-title">Ваша корзина</h1>
    
    <?php if (empty($cartItems)): ?>
    <div class="empty-cart">
        <p class="empty-text">Корзина пуста</p>
        <a href="catalog.php" class="btn btn-primary" style="padding: 12px 32px; font-size: 18px;">
            Перейти в каталог
        </a>
    </div>
    <?php else: ?>
    <div class="cart-layout">
        <div class="cart-items">
            <?php foreach ($cartItems as $item): ?>
            <div class="cart-item" data-config-key="<?= htmlspecialchars($item['config_key']) ?>" data-price="<?= $item['price'] ?>">
                <img src="<?= htmlspecialchars($item['img_url']) ?>"
                     alt="<?= htmlspecialchars($item['name']) ?>"
                     class="cart-image"
                     onerror="this.src='https://via.placeholder.com/140x140?text=Нет+фото'">
                
                <div class="cart-details">
                    <div class="cart-header">
                        <h3 class="cart-name"><?= htmlspecialchars($item['name']) ?></h3>
                        <div class="cart-right-info">
                            <span class="cart-price"><?= number_format($item['price'], 2, ',', ' ') ?> ₽</span>
                        </div>
                    </div>
                    
                    <!-- Конфигурация (название, а не цена) -->
                    <?php if (!empty($item['configuration_name'])): ?>
                    <div class="cart-config">
                        <strong>Комплектация:</strong> <?= htmlspecialchars($item['configuration_name']) ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Модификации -->
                    <?php if (!empty($item['modifications'])): ?>
                    <div class="cart-mods">
                        <strong>Опции:</strong>
                        <ul>
                            <?php foreach ($item['modifications'] as $mod): ?>
                            <li>
                                <?= htmlspecialchars($mod['group']) ?>: 
                                <?= htmlspecialchars($mod['variant']['name']) ?>
                                <?php if (!empty($mod['property'])): ?>
                                    (+ <?= htmlspecialchars($mod['property']['name']) ?>)
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <div class="cart-footer">
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="changeQty('<?= htmlspecialchars($item['config_key']) ?>', -1)">−</button>
                            <input type="number" class="qty-input" value="<?= $item['quantity'] ?>"
                                   min="1" onchange="updateQty('<?= htmlspecialchars($item['config_key']) ?>', this.value)">
                            <button class="qty-btn" onclick="changeQty('<?= htmlspecialchars($item['config_key']) ?>', 1)">+</button>
                        </div>
                        
                        <div class="cart-subtotal-display">
                            Итого: <span data-subtotal="<?= $item['subtotal'] ?>"><?= number_format($item['subtotal'], 2, ',', ' ') ?> ₽</span>
                        </div>
                        
                        <button class="cart-remove" onclick="removeItem('<?= htmlspecialchars($item['config_key']) ?>')">
                            <i class="fas fa-trash"></i> Удалить
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <h2 class="summary-title">Оформление заказа</h2>
            <div class="summary-row">
                <span class="summary-label">Количество:</span>
                <span class="summary-value" id="total-quantity"><?= $totalQuantity ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">На сумму:</span>
                
                <span class="summary-value" id="total-amount"><?= number_format($total, 2, ',', ' ') ?> ₽</span>
            </div>
            <div class="total-row">
                Итого: <span id="total-display"><?= number_format($total, 2, ',', ' ') ?> ₽</span>
            </div>
            
            <form class="checkout-form">
                <input type="text" name="address" class="input-field"
                       placeholder="Адрес доставки *" required>
                <button type="button" class="checkout-btn" onclick="checkout()">
                    Оформить заказ
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php
if (!isset($context)) {
    $context = 'lab';
}
require_once '../footer.php';
?>
</div>

<script>
// Цены храним как числа для точных вычислений
const prices = <?= json_encode(array_column($cartItems, 'price', 'config_key'), JSON_UNESCAPED_UNICODE) ?>;

// Форматирование цены в рубли
function formatPrice(amount) {
    return amount.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽';
}

// Пересчёт общих итогов по корзине
function recalculateTotals() {
    let totalQty = 0;
    let total = 0;
    
    document.querySelectorAll('.cart-item').forEach(item => {
        const configKey = item.dataset.configKey;
        const qtyInput = item.querySelector('.qty-input');
        const quantity = parseInt(qtyInput.value) || 1;
        const price = prices[configKey] || 0;
        const subtotal = price * quantity;
        
        // Обновляем отображение подытога для товара
        const subtotalEl = item.querySelector('.cart-subtotal-display span');
        subtotalEl.textContent = formatPrice(subtotal);
        subtotalEl.dataset.subtotal = subtotal;
        
        totalQty += quantity;
        total += subtotal;
    });
    
    // Обновляем элементы с итогами (включая "На сумму:")
    const totalQtyEl = document.getElementById('total-quantity');
    const totalAmountEl = document.getElementById('total-amount');
    const totalDisplayEl = document.getElementById('total-display');
    
    if (totalQtyEl) totalQtyEl.textContent = totalQty;
    if (totalAmountEl) totalAmountEl.textContent = formatPrice(total);
    if (totalDisplayEl) totalDisplayEl.textContent = formatPrice(total);
    
    return { totalQty, total };
}

// Изменение количества через кнопки +/-
function changeQty(configKey, delta) {
    const item = document.querySelector(`.cart-item[data-config-key="${configKey}"]`);
    if (!item) return;
    
    const input = item.querySelector('.qty-input');
    let val = parseInt(input.value) || 1;
    val = Math.max(1, val + delta);
    input.value = val;
    
    // Обновляем визуал сразу, серверный запрос — в фоне
    recalculateTotals();
    updateCart(configKey, val);
}

// Изменение количества через ввод в поле
function updateQty(configKey, quantity) {
    let val = Math.max(1, parseInt(quantity) || 1);
    
    const item = document.querySelector(`.cart-item[data-config-key="${configKey}"]`);
    if (item) {
        item.querySelector('.qty-input').value = val;
    }
    
    recalculateTotals();
    updateCart(configKey, val);
}

// Отправка обновления количества на сервер (возвращает Promise)
function updateCart(configKey, quantity) {
    const formData = new FormData();
    formData.append('action', 'update_qty');
    formData.append('config_key', configKey);
    formData.append('quantity', quantity);
    
    return fetch('cart.php', { method: 'POST', body: formData })
        .then(res => {
            if (!res.ok) throw new Error('Network error');
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                console.error('Ошибка сервера:', data);
                throw new Error(data.error || 'Ошибка обновления');
            }
        })
        .catch(err => {
            console.error('Ошибка сохранения количества:', err);
            // Не блокируем пользователя, но логируем ошибку
        });
}

// Удаление товара
function removeItem(configKey) {
    if (!confirm('Удалить товар из корзины?')) return;
    window.location.href = `cart.php?action=remove&key=${encodeURIComponent(configKey)}`;
}

// Оформление заказа с ожиданием завершения всех обновлений
async function checkout() {
    const address = document.querySelector('input[name="address"]').value.trim();
    if (!address) {
        alert('Укажите адрес доставки');
        return;
    }
    
    const btn = document.querySelector('.checkout-btn');
    const originalText = btn.textContent;
    
    // Блокируем интерфейс на время оформления
    btn.disabled = true;
    btn.textContent = 'Оформление...';
    document.querySelectorAll('.qty-btn, .cart-remove').forEach(el => el.disabled = true);
    
    try {
        // Небольшая задержка, чтобы гарантировать завершение предыдущих fetch-запросов
        await new Promise(resolve => setTimeout(resolve, 100));
        
        const formData = new FormData();
        formData.append('action', 'checkout');
        formData.append('address', address);
        
        const res = await fetch('cart.php', { 
            method: 'POST', 
            body: formData,
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await res.json();
        
        if (data.success) {
            alert('Заказ оформлен!');
            window.location.href = 'lk_user.php';
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось оформить заказ'));
        }
    } catch (err) {
        console.error('Ошибка оформления:', err);
        alert('Произошла ошибка при оформлении заказа. Попробуйте ещё раз.');
    } finally {
        // Разблокируем интерфейс
        btn.disabled = false;
        btn.textContent = originalText;
        document.querySelectorAll('.qty-btn, .cart-remove').forEach(el => el.disabled = false);
    }
}

// Инициализация: пересчитываем итоги при загрузке страницы на всякий случай
document.addEventListener('DOMContentLoaded', () => {
    recalculateTotals();
});
</script>
</body>
</html>