<?php
session_start();
$context = 'mip';
require_once 'config.php';

// Подключаем получение данных конкретной услуги
require_once 'includes/get_service_data.php';

// Подключаем шапку
require_once '../header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style_header_footer.css" />
    <link rel="stylesheet" href="css/style_mip.css" />
    <link rel="stylesheet" href="css/style_main.css" />
    <link rel="stylesheet" href="css/style_services.css" />
    <link rel="stylesheet" href="css/header_mip.css" />
    <title><?= htmlspecialchars($service['name']) ?> — Услуги</title>
</head>
<body>
    <div class="screen">
        <div class="div">
            
            <!-- Шапка -->
            <?php 
                $context = 'mip';
                require_once '../header.php'; 
            ?>

            <div class="service-detail-container">
                <a href="services_catalog.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Назад к услугам
                </a>
                
                <div class="service-detail-content">
                    <h1 class="service-detail-title"><?= htmlspecialchars($service['name']) ?></h1>
                    
                    <?php if (!empty($service['img_url'])): ?>
                        <div class="service-detail-image">
                            <img src="<?= htmlspecialchars($service['img_url']) ?>" 
                                 alt="<?= htmlspecialchars($service['name']) ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="service-detail-info">
                        <div class="service-detail-price">
                            Стоимость: <strong><?= number_format($service['price'], 2, ',', ' ') ?> ₽</strong>
                        </div>
                        <?php if (!empty($service['duration'])): ?>
                            <div class="service-detail-duration">
                                Срок выполнения: <?= htmlspecialchars($service['duration']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="service-detail-description">
                        <h3>Описание услуги</h3>
                        <p><?= nl2br(htmlspecialchars($service['full_description'] ?: $service['short_description'])) ?></p>
                    </div>
                    
                    <div class="service-detail-order">
                        <button class="service-order-btn-detail" 
                                data-service-id="<?= $service['id'] ?>"
                                data-service-name="<?= htmlspecialchars($service['name']) ?>"
                                data-service-price="<?= $service['price'] ?>">
                            Заказать услугу
                        </button>
                    </div>
                </div>
            </div>

            <?php require_once '../footer.php'; ?>
            
        </div>
    </div>

    <script src="js/services.js"></script>
</body>
</html>