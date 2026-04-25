<?php
session_start();
require_once '../config.php';

try {
    $stmt = $pdo->prepare("
        SELECT 
            n.id, 
            n.title, 
            n.content, 
            n.datetime,
            n.section,
            (SELECT image_url FROM news_images WHERE news_id = n.id ORDER BY is_main DESC, sort_order LIMIT 1) as main_image
        FROM news n
        WHERE n.section = 'lab'
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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:title" content="Новости — Лаборатория ПЭТ" />
    <meta property="og:description" content="Актуальная информация о проектах и достижениях лаборатории перспективных энергетических технологий" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/header_lab.css" />
    <link rel="stylesheet" href="../css/footer.css" />
    <link rel="stylesheet" href="css/style_news_lab.css" />
    <title>Новости — Лаборатория ПЭТ</title>
</head>
<body>
    <div class="screen">
        <div class="div">
            <?php 
                $context = 'lab';
                require_once '../header.php'; 
            ?>
            
            <!-- Заголовок страницы -->
            <section class="news-header-section">
                <h1 class="news-page-title">Новости лаборатории</h1>
                <p class="news-page-subtitle">Актуальная информация о проектах, исследованиях и достижениях</p>
            </section>

            <!-- Контейнер новостей -->
            <div class="news-container">
                <?php if (empty($newsList)): ?>
                    <div class="no-news">
                        <i class="fas fa-newspaper"></i>
                        <p>Новостей пока нет</p>
                    </div>
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
                            <?php else: ?>
                                <a href="news_view.php?id=<?= $item['id'] ?>" class="news-image-link">
                                    <div class="news-item-image-placeholder">
                                        <i class="fas fa-flask"></i>
                                    </div>
                                </a>
                            <?php endif; ?>
                            
                            <div class="news-item-content">
                                <div class="news-item-header">
                                    <div class="news-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?= date('d.m.Y', strtotime($item['datetime'])) ?>
                                    </div>
                                    <h2 class="news-title">
                                        <a href="news_view.php?id=<?= $item['id'] ?>">
                                            <?= htmlspecialchars($item['title']) ?>
                                        </a>
                                    </h2>
                                </div>
                                
                                <p class="news-excerpt">
                                    <?= htmlspecialchars(mb_strimwidth(strip_tags($item['content']), 0, 200, '...')) ?>
                                </p>
                                
                                <a href="news_view.php?id=<?= $item['id'] ?>" class="btn-read-more">
                                    Читать далее <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

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