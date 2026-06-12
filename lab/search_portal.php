<?php
session_start();
$context = 'lab';
require_once '../header.php';
require_once '../config.php';

$query = trim($_GET['q'] ?? '');
$checkOnly = isset($_GET['check_only']) && $_GET['check_only'] == 1;

// Проверка наличия результатов (AJAX)
if ($checkOnly) {
    header('Content-Type: application/json');
    $hasResults = false;
    
    if (strlen($query) >= 2) {
        // Проверка проектов
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM lab_projects WHERE name LIKE ? OR short_description LIKE ?");
        $stmt->execute(["%$query%", "%$query%"]);
        if ($stmt->fetchColumn() > 0) $hasResults = true;
        
        // Проверка услуг
        if (!$hasResults) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM lab_services WHERE name LIKE ? OR description LIKE ?");
            $stmt->execute(["%$query%", "%$query%"]);
            if ($stmt->fetchColumn() > 0) $hasResults = true;
        }
        
        // Проверка новостей
        if (!$hasResults) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM news WHERE title LIKE ? OR content LIKE ?");
            $stmt->execute(["%$query%", "%$query%"]);
            if ($stmt->fetchColumn() > 0) $hasResults = true;
        }
    }
    
    echo json_encode(['has_results' => $hasResults]);
    exit;
}

// Полный поиск для отображения страницы
$projects = [];
$services = [];
$news = [];

if (strlen($query) >= 2) {
    // Поиск проектов
    $stmt = $pdo->prepare("
        SELECT id, name, short_description as content 
        FROM lab_projects 
        WHERE name LIKE ? OR short_description LIKE ?
        LIMIT 20
    ");
    $stmt->execute(["%$query%", "%$query%"]);
    $projects = $stmt->fetchAll();
    
    // Поиск услуг
    $stmt = $pdo->prepare("
        SELECT id, name, description as content 
        FROM lab_services 
        WHERE name LIKE ? OR description LIKE ?
        LIMIT 20
    ");
    $stmt->execute(["%$query%", "%$query%"]);
    $services = $stmt->fetchAll();
    
    // Поиск новостей
    $stmt = $pdo->prepare("
        SELECT id, title, content 
        FROM news 
        WHERE title LIKE ? OR content LIKE ?
        LIMIT 20
    ");
    $stmt->execute(["%$query%", "%$query%"]);
    $news = $stmt->fetchAll();
}

$totalResults = count($projects) + count($services) + count($news);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск по лаборатории</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .search-page {
            max-width: 1000px;
            margin: 180px auto 80px;
            padding: 0 20px;
        }
        .search-header h1 {
            font-size: 32px;
            color: #00302e;
            margin-bottom: 20px;
        }
        .search-query {
            padding: 15px 20px;
            background: #f0f6f4;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .search-query strong {
            color: #00a896;
        }
        .search-stats {
            margin-bottom: 20px;
            color: #4a6a65;
        }
        .search-section {
            margin-bottom: 40px;
        }
        .search-section-title {
            font-size: 20px;
            color: #00302e;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e0e8e5;
        }
        .search-results {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .search-item {
            padding: 18px 20px;
            background: white;
            border-radius: 12px;
            border: 1px solid #e0e8e5;
            transition: all 0.3s;
        }
        .search-item:hover {
            transform: translateX(5px);
            border-color: #00a896;
            box-shadow: 0 4px 12px rgba(0,168,150,0.1);
        }
        .search-item-type {
            font-size: 12px;
            color: #00a896;
            margin-bottom: 6px;
        }
        .search-item h3 {
            margin: 0 0 8px;
            font-size: 18px;
        }
        .search-item h3 a {
            color: #00302e;
            text-decoration: none;
        }
        .search-item h3 a:hover {
            color: #00a896;
        }
        .search-item p {
            color: #4a6a65;
            line-height: 1.5;
            margin-bottom: 8px;
        }
        .search-empty {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            color: #8aa9a3;
        }
        .search-empty i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .search-other {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 24px;
            background: #00a896;
            color: white;
            border-radius: 30px;
            text-decoration: none;
        }
        .search-other:hover {
            background: #006e6a;
        }
    </style>
</head>
<body>
    <?php 
        $context = 'lab';
        require_once '../header.php'; 
    ?>
    
    <div class="search-page">
        <div class="search-header">
            <h1>Поиск по лаборатории</h1>
            <div class="search-query">
                <i class="fas fa-search"></i> 
                Результаты по запросу: <strong><?= htmlspecialchars($query) ?></strong>
            </div>
        </div>
        
        <?php if (empty($query)): ?>
            <div class="search-empty">
                <i class="fas fa-search"></i>
                <p>Введите поисковый запрос</p>
            </div>
        <?php elseif ($totalResults === 0): ?>
            <div class="search-empty">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Ничего не найдено по запросу "<?= htmlspecialchars($query) ?>"</p>
                <a href="/mip/search_portal.php?q=<?= urlencode($query) ?>" class="search-other">
                    <i class="fas fa-building"></i> Искать в разделе МИП
                </a>
            </div>
        <?php else: ?>
            <div class="search-stats">Найдено: <?= $totalResults ?> результатов</div>
            
            <?php if (!empty($projects)): ?>
                <div class="search-section">
                    <div class="search-section-title">
                        <i class="fas fa-project-diagram"></i> Проекты (<?= count($projects) ?>)
                    </div>
                    <div class="search-results">
                        <?php foreach ($projects as $item): ?>
                            <div class="search-item">
                                <div class="search-item-type"><i class="fas fa-project-diagram"></i> Проект</div>
                                <h3><a href="projects.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></a></h3>
                                <p><?= htmlspecialchars(mb_strimwidth(strip_tags($item['content']), 0, 150, '...')) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($services)): ?>
                <div class="search-section">
                    <div class="search-section-title">
                        <i class="fas fa-cogs"></i> Услуги (<?= count($services) ?>)
                    </div>
                    <div class="search-results">
                        <?php foreach ($services as $item): ?>
                            <div class="search-item">
                                <div class="search-item-type"><i class="fas fa-cogs"></i> Услуга</div>
                                <h3><a href="services.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></a></h3>
                                <p><?= htmlspecialchars(mb_strimwidth(strip_tags($item['content']), 0, 150, '...')) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($news)): ?>
                <div class="search-section">
                    <div class="search-section-title">
                        <i class="fas fa-newspaper"></i> Новости (<?= count($news) ?>)
                    </div>
                    <div class="search-results">
                        <?php foreach ($news as $item): ?>
                            <div class="search-item">
                                <div class="search-item-type"><i class="fas fa-newspaper"></i> Новость</div>
                                <h3><a href="news_view.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a></h3>
                                <p><?= htmlspecialchars(mb_strimwidth(strip_tags($item['content']), 0, 150, '...')) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php require_once '../footer.php'; ?>
</body>
</html>