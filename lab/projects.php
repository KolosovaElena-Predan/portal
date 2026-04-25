<?php
session_start();
require_once 'config.php';

// Получаем все проекты
try {
    $stmt = $pdo->prepare("SELECT * FROM lab_projects ORDER BY sort_order ASC, start_date DESC");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $projects = [];
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
    <title>Проекты — Лаборатория ПЭТ</title>
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
        
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .project-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .project-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(26,25,130,0.15);
        }
        .project-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: #e7e8f3;
        }
        .project-content {
            padding: 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .project-title {
            font-size: 22px;
            font-weight: 700;
            color: #1a1982;
            margin-bottom: 12px;
        }
        .project-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
            align-self: flex-start;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .status-planned { background: #fff3cd; color: #856404; }
        .project-desc {
            font-size: 15px;
            color: #555;
            line-height: 1.7;
            margin-bottom: 20px;
            flex: 1;
        }
        .project-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #888;
            padding-top: 15px;
            border-top: 1px solid #e7e8f3;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .project-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #1a1982;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            margin-top: auto;
        }
        .project-link:hover {
            background: #4a49d9;
            gap: 12px;
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
                <h2 class="section-title">ПРОЕКТЫ ЛАБОРАТОРИИ</h2>
                <div class="projects-grid">
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $proj): ?>
                        <div class="project-card">
                            <img src="<?= htmlspecialchars($proj['img_url'] ?? 'img/default.png') ?>" 
                                 alt="<?= htmlspecialchars($proj['name']) ?>" 
                                 class="project-image">
                            <div class="project-content">
                                <div class="project-title"><?= htmlspecialchars($proj['name']) ?></div>
                                <?php
                                $statusText = [
                                    'active' => ' Активен',
                                    'completed' => ' Завершен',
                                    'planned' => ' Планируется'
                                ];
                                $statusClass = [
                                    'active' => 'status-active',
                                    'completed' => 'status-completed',
                                    'planned' => 'status-planned'
                                ];
                                ?>
                                <span class="project-status <?= $statusClass[$proj['status']] ?>">
                                    <?= $statusText[$proj['status']] ?>
                                </span>
                                <p class="project-desc"><?= htmlspecialchars(mb_strimwidth(strip_tags($proj['full_description'] ?? $proj['short_description'] ?? ''), 0, 200, '...')) ?></p>
                                <div class="project-meta">
                                    <?php if ($proj['start_date']): ?>
                                    <span>Старт: <?= date('d.m.Y', strtotime($proj['start_date'])) ?></span>
                                    <?php endif; ?>
                                    <?php if ($proj['end_date']): ?>
                                    <span>Окончание: <?= date('d.m.Y', strtotime($proj['end_date'])) ?></span>
                                    <?php endif; ?>
                                </div>
                                <!-- ССЫЛКА НА ПОЛНОЕ ОПИСАНИЕ ПРОЕКТА -->
                                <a href="project_detail.php?id=<?= $proj['id'] ?>" class="project-link">
                                    Подробнее о проекте →
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #777; grid-column: 1/-1; font-size: 18px;">
                            Проекты временно недоступны
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