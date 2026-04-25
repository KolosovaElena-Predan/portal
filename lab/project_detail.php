<?php
session_start();
require_once '../config.php';

// Получаем ID проекта
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($project_id <= 0) {
    header('Location: projects.php');
    exit;
}

// Получаем данные проекта
try {
    $stmt = $pdo->prepare("SELECT * FROM lab_projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        header('Location: projects.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: projects.php');
    exit;
}

// Определяем статус на русском
$status_labels = [
    'active' => 'Активный',
    'completed' => 'Завершён',
    'planned' => 'Планируется'
];
$status_classes = [
    'active' => 'status-active',
    'completed' => 'status-completed',
    'planned' => 'status-planned'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($project['name']) ?> — Лаборатория перспективных энергетических технологий</title>
    <link rel="stylesheet" href="../css/header_lab.css">
    <link rel="stylesheet" href="../css/footer.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f6f6f6;
            color: #161551;
            line-height: 1.6;
        }
        
        .project-detail {
            max-width: 1200px;
            margin: 0 auto;
            padding: 120px 20px 60px;
        }
        
        .project-header {
            margin-bottom: 40px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #1a1982;
            text-decoration: none;
            margin-bottom: 30px;
            font-weight: 500;
            transition: 0.3s;
        }
        
        .back-link:hover {
            gap: 12px;
            color: #4a49d9;
        }
        
        .project-title {
            font-size: 40px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 20px;
        }
        
        .project-meta {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e7e8f3;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 14px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-active {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .status-completed {
            background: #e8f5e9;
            color: #388e3c;
        }
        
        .status-planned {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .project-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 4px 20px rgba(26, 25, 130, 0.1);
        }
        
        .project-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(26, 25, 130, 0.08);
        }
        
        .project-content h2 {
            font-size: 24px;
            color: #000000;
            margin: 30px 0 15px 0;
        }
        
        .project-content h2:first-of-type {
            margin-top: 0;
        }
        
        .project-content p {
            color: #444;
            margin-bottom: 20px;
            line-height: 1.8;
        }
        
        .budget-info {
            background: #f9f9fb;
            padding: 20px;
            border-radius: 12px;
            margin-top: 30px;
            border-left: 4px solid #1a1982;
        }
        
        .budget-info p {
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .project-detail {
                padding: 100px 15px 40px;
            }
            .project-title {
                font-size: 28px;
            }
            .project-content {
                padding: 25px;
            }
            .project-meta {
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <?php 
    $context = 'lab';
    require_once '../header.php'; 
    ?>
    
    <div class="project-detail">
        <div class="project-header">
            <a href="projects.php" class="back-link">← Назад к проектам</a>
            <h1 class="project-title"><?= htmlspecialchars($project['name']) ?></h1>
            
            <div class="project-meta">
                <div class="meta-item">
                    📅 Начало: <?= date('d.m.Y', strtotime($project['start_date'])) ?>
                </div>
                <?php if ($project['end_date']): ?>
                <div class="meta-item">
                    🏁 Окончание: <?= date('d.m.Y', strtotime($project['end_date'])) ?>
                </div>
                <?php endif; ?>
                <div class="meta-item">
                    <span class="status-badge <?= $status_classes[$project['status']] ?>">
                        <?= $status_labels[$project['status']] ?>
                    </span>
                </div>
            </div>
        </div>
        
        <?php if ($project['img_url']): ?>
        <img src="<?= htmlspecialchars($project['img_url']) ?>" alt="<?= htmlspecialchars($project['name']) ?>" class="project-image">
        <?php endif; ?>
        
        <div class="project-content">
            <h2>Описание проекта</h2>
            <p><?= nl2br(htmlspecialchars($project['full_description'] ?? $project['short_description'] ?? '')) ?></p>
            
            <?php if ($project['budget']): ?>
            <div class="budget-info">
                <p><strong>💰 Бюджет проекта:</strong> <?= number_format($project['budget'], 0, ',', ' ') ?> ₽</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    require_once '../footer.php';
    ?>
</body>
</html>