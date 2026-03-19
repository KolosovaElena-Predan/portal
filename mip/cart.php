<?php
session_start();
require_once 'config.php';

// 🔐 Проверка авторизации — если пользователь не вошёл, перенаправляем на login
if (!isset($_SESSION['user_id'])) {
    // Сохраняем текущий URL для возврата после входа
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $current_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    // 🔗 Редирект на authorization.php в родительской папке
    $redirect = '../authorization.php?redirect=' . urlencode($current_url);
    header('Location: ' . $redirect);
    exit;
}

// Добавление товара в корзину
if (isset($_GET['action']) && $_GET['action'] === 'add' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    header('Location: cart.php');
    exit;
}

// Удаление товара из корзины
if (isset($_GET['action']) && $_GET['action'] === 'remove' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    unset($_SESSION['cart'][$id]);
    header('Location: cart.php');
    exit;
}

// Оформление заказа
if (isset($_POST['action']) && $_POST['action'] === 'checkout') {
    $address = trim($_POST['address'] ?? '');

    if (empty($address)) {
        echo json_encode(['error' => 'Укажите адрес доставки'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $stmt = $pdo->prepare("SELECT name, base_price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $prod = $stmt->fetch();

            if (!$prod) continue;

            $message = json_encode([
                'quantity' => $quantity,
                'address' => $address,
                'product_name' => $prod['name']
            ], JSON_UNESCAPED_UNICODE);

            $pdo->prepare("
                INSERT INTO request (user_id, device_type_id, product_id, message, status, datetime, type)
                VALUES (?, ?, ?, ?, 'new', NOW(), 'r')
            ")->execute([$_SESSION['user_id'], $product_id, $product_id, $message]);
        }

        $_SESSION['cart'] = [];
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Загрузка данных корзины
$products = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.base_price as price,
               (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as img_url
        FROM products p
        WHERE p.id IN ($placeholders)
    ");
    $stmt->execute($ids);
    $raw_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($raw_products as $p) {
        $quantity = $_SESSION['cart'][$p['id']];
        $price = (float)$p['price'];
        $subtotal = $price * $quantity;
        $total += $subtotal;

        $products[] = [
            'id' => $p['id'],
            'name' => $p['name'],
            'price' => $price,
            'img_url' => $p['img_url'] ?? 'img/placeholder.jpg',
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="css/style_header_footer.css" />
    <link rel="stylesheet" href="css/style_mip.css" />
    <link rel="stylesheet" href="css/style_main.css" />
    <link rel="stylesheet" href="css/header_mip.css" />
    <title>Корзина</title>
    <style>
    .cart-page {
        width: 100%;
        max-width: 1400px;
        margin: 250px auto 80px;
        padding: 0 20px;
    }
    .cart-title {
        font-family: "Inter-Bold", sans-serif;
        font-size: 40px;
        color: #000;
        text-align: center;
        margin-bottom: 40px;
    }
    .cart-layout {
        display: flex;
        gap: 40px;
    }
    .cart-items {
        flex: 2;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    .cart-summary {
        flex: 1;
        background: #ffffff;
        border-radius: 18px;
        padding: 30px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 200px;
    }
    .summary-title {
        font-family: "Inter-Bold", sans-serif;
        font-size: 24px;
        color: #000;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eee;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin: 12px 0;
        font-size: 18px;
    }
    .summary-label { color: #555; }
    .summary-value { font-weight: 600; color: #1a1982; }
    .total-row {
        border-top: 2px solid #1a1982;
        margin-top: 20px;
        padding-top: 20px;
        font-size: 24px;
        font-weight: bold;
    }
    .cart-item {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }
    .cart-image {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 12px;
        background: #f0f0f0;
    }
    .cart-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .cart-name {
        font-family: "Inter-Bold", sans-serif;
        font-size: 22px;
        color: #000;
        margin: 0 0 10px 0;
    }
    .cart-price {
        font-family: "Inter-Medium", sans-serif;
        font-size: 20px;
        color: #1a1982;
        margin: 5px 0;
    }
    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 12px 0;
    }
    .qty-btn {
        width: 36px;
        height: 36px;
        border: 1px solid #ccc;
        background: #f7f7f3;
        border-radius: 8px;
        font-size: 18px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .qty-btn:hover {
        background: #e7e8f3;
        border-color: #1a1982;
    }
    .qty-input {
        width: 60px;
        height: 36px;
        text-align: center;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        font-family: "Inter-Regular", sans-serif;
    }
    .cart-remove {
        color: #d9534f;
        font-size: 16px;
        text-decoration: underline;
        cursor: pointer;
        margin-top: 8px;
    }
    .checkout-form .input-field {
        width: 100%;
        height: 60px;
        padding: 0 16px;
        margin-bottom: 24px;
        border-radius: 12px;
        font-size: 18px;
    }
    .checkout-btn {
        width: 100%;
        height: 60px;
        background: linear-gradient(120deg, #1a1982, #0d0c4a);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 20px;
        font-family: "Inter-Medium", sans-serif;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 8px rgba(26, 25, 130, 0.3);
    }
    .checkout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(26, 25, 130, 0.4);
    }
    .empty-cart {
        text-align: center;
        padding: 60px 20px;
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }
    .empty-text {
        font-family: "Inter-Light", sans-serif;
        font-size: 24px;
        color: #777;
        margin-bottom: 24px;
    }
    @media (max-width: 1024px) {
        .cart-layout { flex-direction: column; }
        .cart-summary { position: static; }
    }
    </style>
</head>
<body>
    <div class="screen">
        <div class="div">
            <?php require_once 'header_mip.php'; ?>

            <div class="cart-page">
                <h1 class="cart-title">Ваша корзина</h1>

                <?php if (empty($products)): ?>
                <div class="empty-cart">
                    <p class="empty-text">Корзина пуста</p>
                    <a href="catalog.php" class="btn btn-primary" style="padding: 12px 32px; font-size: 18px;">
                        Перейти в каталог
                    </a>
                </div>
                <?php else: ?>
                <div class="cart-layout">
                    <div class="cart-items">
                        <?php foreach ($products as $p): ?>
                        <div class="cart-item" data-id="<?= $p['id'] ?>">
                            <img src="<?= htmlspecialchars($p['img_url']) ?>"
                                 alt="<?= htmlspecialchars($p['name']) ?>"
                                 class="cart-image"
                                 onerror="this.src='https://via.placeholder.com/140x140?text=Нет+фото'">

                            <div class="cart-details">
                                <h3 class="cart-name"><?= htmlspecialchars($p['name']) ?></h3>

                                <div class="cart-price">
                                    <?= number_format($p['price'], 2, ',', ' ') ?> ₽ / шт
                                </div>

                                <div class="quantity-controls">
                                    <button class="qty-btn" onclick="changeQty(<?= $p['id'] ?>, -1)">−</button>
                                    <input type="number" class="qty-input" value="<?= $p['quantity'] ?>"
                                           min="1" onchange="updateQty(<?= $p['id'] ?>)">
                                    <button class="qty-btn" onclick="changeQty(<?= $p['id'] ?>, 1)">+</button>
                                </div>

                                <div class="cart-price">
                                    Итого: <span class="cart-subtotal">
                                        <?= number_format($p['subtotal'], 2, ',', ' ') ?> ₽
                                    </span>
                                </div>

                                <div class="cart-remove" onclick="removeItem(<?= $p['id'] ?>)">Удалить</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <h2 class="summary-title">Оформление заказа</h2>

                        <div class="summary-row">
                            <span class="summary-label">Товаров:</span>
                            <span class="summary-value"><?= count($products) ?></span>
                        </div>

                        <div class="summary-row">
                            <span class="summary-label">На сумму:</span>
                            <span class="summary-value">
                                <?= number_format($total, 2, ',', ' ') ?> ₽
                            </span>
                        </div>

                        <div class="total-row">
                            Итого: <span id="total-display">
                                <?= number_format($total, 2, ',', ' ') ?> ₽
                            </span>
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
        </div>
    </div>

    <script>
    const prices = <?= json_encode(array_column($products, 'price', 'id'), JSON_UNESCAPED_UNICODE) ?>;

    function changeQty(id, delta) {
        const input = document.querySelector(`.cart-item[data-id="${id}"] .qty-input`);
        if (!input) return;
        let val = parseInt(input.value) || 1;
        val = Math.max(1, val + delta);
        input.value = val;
        updateCart(id, val);
    }

    function updateQty(id) {
        const input = document.querySelector(`.cart-item[data-id="${id}"] .qty-input`);
        if (!input) return;
        let val = Math.max(1, parseInt(input.value) || 1);
        input.value = val;
        updateCart(id, val);
    }

    function updateCart(id, quantity) {
        const subtotal = (prices[id] * quantity).toFixed(2);
        document.querySelector(`.cart-item[data-id="${id}"] .cart-subtotal`).textContent =
            subtotal.replace('.', ',') + ' ₽';

        let total = 0;
        document.querySelectorAll('.cart-subtotal').forEach(el => {
            const val = parseFloat(el.textContent.replace(/[^0-9,]/g, '').replace(',', '.'));
            total += val;
        });

        document.getElementById('total-display').textContent =
            total.toFixed(2).replace('.', ',') + ' ₽';
    }

    function removeItem(id) {
        if (!confirm('Удалить товар из корзины?')) return;
        window.location.href = `cart.php?action=remove&id=${id}`;
    }

    function checkout() {
        const address = document.querySelector('input[name="address"]').value.trim();
        if (!address) {
            alert('Укажите адрес доставки');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'checkout');
        formData.append('address', address);

        fetch('cart.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Заказ оформлен!');
                    window.location.href = 'lk_user.php';
                } else {
                    alert('Ошибка: ' + (data.error || ''));
                }
            })
            .catch(err => {
                console.error('Ошибка:', err);
                alert('Произошла ошибка при оформлении заказа');
            });
    }
    </script>
</body>
</html>