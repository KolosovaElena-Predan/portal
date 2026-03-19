<?php
session_start();
require_once 'config.php';

try {
    $stmt = $pdo->prepare("SELECT id, title, content, image_url, datetime FROM News ORDER BY datetime DESC");
    $stmt->execute();
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $news = [];
    error_log("Ошибка загрузки новостей: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="css/main_lab.css" />
    <link rel="stylesheet" href="css/header_lab.css" />
    <link rel="stylesheet" href="css/footer.css" />
    <link rel="stylesheet" href="css/news.css" />
    <title>Новости</title>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <main class="content-wrapper">
        <div class="news-container">
            <h1 class="news-page-title">Новости</h1>

            <?php if (empty($news)): ?>
                <div class="news-empty">
                    <p>Новости пока отсутствуют.</p>
                </div>
            <?php else: ?>
                <?php foreach ($news as $item): ?>
                    <?php
                        $content = strip_tags($item['content']);
                        $limit = 300;
                        $excerpt = mb_strimwidth($content, 0, $limit, '');
                        $remainder = mb_strlen($content) > $limit ? mb_substr($content, $limit) : '';
                        $hasRemainder = !empty($remainder);
                    ?>

                    <article class="news-item">
                        <h2 class="news-title"><?= htmlspecialchars($item['title']) ?></h2>
                        <time class="news-date" datetime="<?= $item['datetime'] ?>">
                            <?= date('d.m.Y H:i', strtotime($item['datetime'])) ?>
                        </time>

                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?= htmlspecialchars($item['image_url']) ?>"
                                 alt="<?= htmlspecialchars($item['title']) ?>"
                                 class="news-image">
                        <?php endif; ?>

                        <div class="news-excerpt">
                            <?= htmlspecialchars($excerpt) ?>
                            <?php if ($hasRemainder): ?>
                                <span class="news-dots" id="dots-<?= $item['id'] ?>">...</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($hasRemainder): ?>
                            <div class="news-full" id="full-<?= $item['id'] ?>">
                                <?= nl2br(htmlspecialchars($remainder)) ?>
                            </div>
                            <button class="btn btn-read-more" onclick="toggleContent(<?= $item['id'] ?>)">
                                <span id="btn-text-<?= $item['id'] ?>">Читать далее</span>
                            </button>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once 'footer.php'; ?>

    <script>
        function toggleContent(id) {
            const fullText = document.getElementById('full-' + id);
            const btnText = document.getElementById('btn-text-' + id);
            const dots = document.getElementById('dots-' + id);

            if (fullText.style.display === 'block') {
                fullText.style.display = 'none';
                btnText.textContent = 'Читать далее';
                if (dots) dots.style.display = 'inline';
            } else {
                fullText.style.display = 'block';
                btnText.textContent = 'Свернуть';
                if (dots) dots.style.display = 'none';
            }
        }
    </script>
</body>
</html>