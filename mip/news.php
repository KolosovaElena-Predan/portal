<?php
session_start();
require_once 'config.php';

// Получаем все новости
try {
    $stmt = $pdo->prepare("
        SELECT 
            n.*,
            (SELECT image_url FROM news_images WHERE news_id = n.id ORDER BY is_main DESC, sort_order LIMIT 1) as main_image
        FROM news n
        ORDER BY n.datetime DESC
    ");
    $stmt->execute();
    $newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $newsList = [];
    error_log("Ошибка загрузки новостей: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style_header_footer.css" />
    <link rel="stylesheet" href="css/style_mip.css" />
    <link rel="stylesheet" href="css/style_main.css" />
    <link rel="stylesheet" href="css/style_news.css" />
    <link rel="stylesheet" href="css/header_mip.css" />
    <title>Новости — ООО МИП "НПЦ ПИТиА"</title>
</head>
<body>
    <div class="screen">
        <div class="div">
            
            <!-- Шапка -->
            <?php require_once 'header_mip.php'; ?>

            <!-- Заголовок страницы -->
            <div class="news-header-section">
                <h1 class="news-page-title">Новости</h1>
                <p class="news-page-subtitle">Актуальная информация о наших проектах и достижениях</p>
            </div>

            <!-- Список новостей -->
            <div class="news-container">
                <?php if (empty($newsList)): ?>
                    <p class="no-news">Новостей пока нет</p>
                <?php else: ?>
                    <?php foreach ($newsList as $item): ?>
                        <article class="news-item">
                            <?php if (!empty($item['main_image'])): ?>
                                <a href="news_view.php?id=<?= $item['id'] ?>" class="news-image-link">
                                    <img src="<?= htmlspecialchars($item['main_image']) ?>" 
                                         alt="<?= htmlspecialchars($item['title']) ?>"
                                         class="news-item-image"
                                         loading="lazy">
                                </a>
                            <?php endif; ?>
                            
                            <div class="news-item-content">
                                <div class="news-item-header">
                                    <time class="news-date" datetime="<?= $item['datetime'] ?>">
                                        <?= date('d.m.Y', strtotime($item['datetime'])) ?>
                                    </time>
                                </div>
                                
                                <h2 class="news-title">
                                    <a href="news_view.php?id=<?= $item['id'] ?>">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </a>
                                </h2>
                                
                                <p class="news-excerpt">
                                    <?= htmlspecialchars(mb_strimwidth(strip_tags($item['content']), 0, 250, '...')) ?>
                                </p>
                                
                                <a href="news_view.php?id=<?= $item['id'] ?>" class="btn-read-more">
                                    Читать далее</i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Подвал -->
            <?php require_once 'footer_mip.php'; ?>
            
        </div>
    </div>
</body>
</html>