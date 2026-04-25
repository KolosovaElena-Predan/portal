<?php
session_start();
require_once 'config.php';

// Получаем услуги
try {
    $stmt = $pdo->prepare("SELECT * FROM lab_services WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $services = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="css/header_lab.css" />
    <link rel="stylesheet" href="css/main_lab.css" />
    <link rel="stylesheet" href="css/footer.css" />
    <title>Услуги — Лаборатория ПЭТ</title>
    <style>
        .section { padding: 60px 20px; max-width: 1200px; margin: 0 auto; }
        .section-title {
            font-family: "Inter-Bold", Helvetica, sans-serif;
            font-weight: 700;
            font-size: 32px;
            color: #1a1982;
            text-align: center;
            margin-bottom: 40px;
        }
        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #1a1982, #4a49d9);
            margin: 15px auto 0;
            border-radius: 2px;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .service-card {
            background: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(26,25,130,0.12);
            border-color: #1a1982;
        }
        .service-icon {
            font-size: 48px;
            color: #1a1982;
            margin-bottom: 20px;
        }
        .service-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a1982;
            margin-bottom: 15px;
        }
        .service-desc {
            font-size: 15px;
            color: #555;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        .service-price {
            font-size: 20px;
            font-weight: 700;
            color: #1a1982;
            margin-bottom: 10px;
        }
        .service-duration {
            font-size: 14px;
            color: #888;
        }
        .btn-order {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #1a1982;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-order:hover {
            background: #14136b;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php 
				$context = 'lab';
				require_once '../header.php'; 
			?>
    
    <div class="screen" style="background-color: #ffffff">
        <div class="div" style="background-color: #ffffff">
            
            <section class="section">
                <h2 class="section-title">УСЛУГИ ЛАБОРАТОРИИ</h2>
                <div class="services-grid">
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <div class="service-title"><?= htmlspecialchars($service['name']) ?></div>
                            <p class="service-desc"><?= htmlspecialchars($service['description']) ?></p>
                            <?php if ($service['price']): ?>
                            <div class="service-price">
                                от <?= number_format($service['price'], 0, '.', ' ') ?> ₽
                            </div>
                            <?php endif; ?>
                            <?php if ($service['duration']): ?>
                            <div class="service-duration"><?= htmlspecialchars($service['duration']) ?></div>
                            <?php endif; ?>
                            <a href="question.php" class="btn-order">Заказать услугу</a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #777; grid-column: 1/-1; font-size: 18px;">
                            Услуги временно недоступны
                        </p>
                    <?php endif; ?>
                </div>
            </section>
            
            <?php
if (!isset($context)) {
    $context = 'lab';
}
require_once '../footer.php';
?>
        </div>
    </div>
</body>
</html>