<?php
session_start();
require_once '../config.php';
$newsId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($newsId <= 0) {
    header('Location: news.php');
    exit;
}
try {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ? AND section = 'lab'");
    $stmt->execute([$newsId]);
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$news) {
        header('Location: news.php');
        exit;
    }
    $stmt = $pdo->prepare("
        SELECT * FROM news_images
        WHERE news_id = ?
        ORDER BY is_main DESC, sort_order, id
    ");
    $stmt->execute([$newsId]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $mainImage = !empty($images) ? $images[0] : null;
    $galleryImages = array_slice($images, 1);
} catch (Exception $e) {
    error_log("Ошибка загрузки новости: " . $e->getMessage());
    die("Ошибка загрузки новости");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <meta property="og:title" content="<?= htmlspecialchars($news['title']) ?>" />
    <meta property="og:description" content="<?= htmlspecialchars(mb_strimwidth(strip_tags($news['content']), 0, 160, '...')) ?>" />
    <?php if ($mainImage): ?>
        <meta property="og:image" content="../<?= htmlspecialchars($mainImage['image_url']) ?>" />
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/header_lab.css" />
    <link rel="stylesheet" href="../css/footer.css" />
    <link rel="stylesheet" href="css/style_news_lab.css" />
    <title><?= htmlspecialchars($news['title']) ?> — Новости лаборатории</title>
</head>
<body>
    <div class="screen">
        <div class="div">
            <?php $context = 'lab'; require_once '../header.php'; ?>
            
            <article class="news-detail-container">
                <header class="news-detail-header">
                    <h1 class="news-detail-title"><?= htmlspecialchars($news['title']) ?></h1>
                    <div class="news-detail-meta">
                        <time class="news-detail-date" datetime="<?= $news['datetime'] ?>">
                            <i class="far fa-calendar-alt"></i>
                            <?= date('d.m.Y в H:i', strtotime($news['datetime'])) ?>
                        </time>
                    </div>
                </header>
                
                <?php if ($mainImage): ?>
                    <div class="news-main-image-wrapper">
                        <!-- ✅ Исправлен путь -->
                        <img src="../<?= htmlspecialchars($mainImage['image_url']) ?>"
                             alt="<?= htmlspecialchars($news['title']) ?>"
                             class="news-main-image"
                             loading="lazy">
                    </div>
                <?php endif; ?>
                
                <div class="news-detail-content">
                    <?= nl2br(htmlspecialchars($news['content'])) ?>
                </div>
                
                <?php if (!empty($galleryImages)): ?>
                    <div class="news-gallery">
                        <h3 class="gallery-title">
                            <i class="fas fa-images"></i>
                            Фотографии
                        </h3>
                        <div class="gallery-grid">
                            <?php foreach ($galleryImages as $img): ?>
                                <div class="gallery-item" onclick="openLightbox('../<?= htmlspecialchars($img['image_url']) ?>')">
                                    <!-- ✅ Исправлен путь -->
                                    <img src="../<?= htmlspecialchars($img['image_url']) ?>"
                                         alt="Фото к новости"
                                         loading="lazy">
                                    <div class="gallery-overlay">
                                        <i class="fas fa-search-plus"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="news-detail-footer">
                    <a href="news.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        Вернуться к списку новостей
                    </a>
                </div>
            </article>
            
            <?php if (!isset($context)) { $context = 'lab'; } require_once '../footer.php'; ?>
        </div>
    </div>
    
    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close">&times;</span>
        <img id="lightbox-img" src="" alt="Полноразмерное изображение">
    </div>
    <script>
    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('active');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLightbox();
    });
    </script>
</body>
</html>