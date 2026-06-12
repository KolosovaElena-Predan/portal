<?php
session_start();
require_once 'config.php';

// Получаем направления
try {
    $stmt = $pdo->prepare("SELECT id, name, description FROM directions ORDER BY sort_order ASC LIMIT 3");
    $stmt->execute();
    $directions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $directions = [];
}

// Получаем команду
try {
    $stmt = $pdo->prepare("SELECT id, name, position, photo_url FROM team ORDER BY sort_order ASC LIMIT 3");
    $stmt->execute();
    $team = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $team = [];
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
    <title>Лаборатория перспективных энергетических технологий — Главная</title>
    
    <style>
        /* === БАЗОВЫЕ НАСТРОЙКИ === */
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #f6f6f6;
    color: #161551;
    line-height: 1.6;
}

/* === HERO SECTION — ПОЛНАЯ ШИРИНА, НИЖЕ, МИНИМАЛИЗМ === */
.hero-minimal-split {
    display: flex;
    width: 100%;
    max-width: 100%;
    margin: 0;
    min-height: 540px;
    padding-top: 110px;
    background: #ffffff;
    position: relative;
    overflow: hidden;
    box-sizing: border-box;
}

.hero-content-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 0 60px 0 80px;
    max-width: 55%;
}

.hero-text-content {
    max-width: 650px;
}

.hero-main-title {
    font-family: "Inter-Bold", Helvetica, sans-serif;
    font-weight: 700;
    font-size: 44px;
    color: #000000; /* Чёрный заголовок */
    line-height: 1.2;
    margin-bottom: 16px;
}

.title-highlight {
    color: #1a1982; /* Возвращаем синий акцент для выделения */
    font-weight: 700;
}

.hero-description {
    font-family: "Inter-Regular", Helvetica, sans-serif;
    font-weight: 400;
    font-size: 18px;
    color: #555555;
    line-height: 1.6;
}

.hero-image-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0;
    height: 100%;
}

.hero-image-box {
    width: 100%;
    max-width: 650px;
    height: 100%;
    min-height: 420px;
    border-radius: 20px 0 0 20px;
    overflow: hidden;
    box-shadow: -12px 20px 40px rgba(26, 25, 130, 0.15); /* Синяя тень */
}

.hero-background-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}

.hero-image-wrapper::before { display: none; }

/* === СЕКЦИИ === */
.section {
    padding: 80px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.section-title {
    font-size: 32px;
    font-weight: 700;
    color: #000000; /* Чёрный заголовок */
    text-align: center;
    margin-bottom: 50px;
    position: relative;
}

.section-title::after {
	display: none;
    content: '';
    display: block;
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, #1a1982, #4a49d9); /* Синий градиент */
    margin: 15px auto 0;
    border-radius: 2px;
}

.view-all {
    text-align: center;
    margin-top: 40px;
}

.view-all-link {
    color: #1a1982;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    padding: 12px 24px;
    border: 2px solid #1a1982;
    border-radius: 8px;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.view-all-link:hover {
    background: #1a1982;
    color: #ffffff;
}

/* === О ЛАБОРАТОРИИ — ФОН С ЛЁГКИМ СИНИМ ОТТЕНКОМ === */
.about-section {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(26, 25, 130, 0.08);
}

.about-text {
    font-size: 17px;
    color: #444;
    max-width: 850px;
    margin: 0 auto 30px;
    text-align: center;
    line-height: 1.8;
}

/* === НАПРАВЛЕНИЯ === */
.directions-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.direction-item {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(26, 25, 130, 0.06);
    transition: 0.3s;
    border: 1px solid #e7e8f3;
}

.direction-item:hover {
    box-shadow: 0 8px 25px rgba(26, 25, 130, 0.12);
    border-color: #1a1982;
}

.direction-name {
    font-size: 22px;
    font-weight: 700;
    color: #000000; /* Чёрный заголовок */
    margin-bottom: 10px;
}

.direction-desc {
    font-size: 15px;
    color: #555;
    line-height: 1.7;
}

/* === ПРОЕКТЫ — ФОН С СИНИМ ОТТЕНКОМ === */
.projects-section {
    background: #f9f9fb; /* Лёгкий сине-серый фон */
    border-radius: 20px;
}

.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
}

.project-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(26, 25, 130, 0.08);
    transition: 0.3s;
}

.project-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(26, 25, 130, 0.15);
}

.project-image {
    width: 100%;
    height: 220px;
    object-fit: cover;
    background: #e7e8f3; /* Синеватый фон-заглушка */
}

.project-content {
    padding: 25px;
}

.project-title {
    font-size: 20px;
    font-weight: 700;
    color: #000000; /* Чёрный заголовок */
    margin-bottom: 12px;
}

.project-desc {
    font-size: 14px;
    color: #666;
    margin-bottom: 15px;
    line-height: 1.6;
}

.project-link {
    color: #1a1982;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: 0.3s;
}

.project-link:hover {
    color: #4a49d9;
    gap: 10px;
}

/* === КОМАНДА === */
.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 40px;
    justify-items: center;
}

.team-member {
    text-align: center;
    background: white;
    padding: 60px 30px 30px;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(26, 25, 130, 0.08);
    width: 100%;
    max-width: 320px;
    position: relative;
}

.team-photo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    margin: -60px auto 20px auto;
    border: 5px solid #ffffff;
    background: #f7f7fd; /* Лёгкий синий фон */
    box-shadow: 0 4px 12px rgba(26, 25, 130, 0.15);
}

.team-name {
    font-size: 20px;
    font-weight: 700;
    color: #000000; /* Чёрный заголовок */
    margin-bottom: 8px;
}

.team-position {
    font-size: 14px;
    color: #666;
}

/* === НОВОСТИ — ФОН С СИНИМ ОТТЕНКОМ === */
.news-section {
    background: #f9f9fb;
    border-radius: 20px;
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.news-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(26, 25, 130, 0.08);
    transition: 0.3s;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(26, 25, 130, 0.12);
}

.news-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: #e7e8f3;
}

.news-content {
    padding: 25px;
}

.news-date {
    font-size: 12px;
    color: #999;
    margin-bottom: 10px;
}

.news-title {
    font-size: 18px;
    font-weight: 700;
    color: #000000; /* Чёрный заголовок */
    margin-bottom: 12px;
    line-height: 1.4;
}

.news-excerpt {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
}

/* === КНОПКИ В HERO — СИНИЕ === */
.hero-buttons {
    display: flex;
    gap: 15px;
    justify-content: flex-start;
    flex-wrap: wrap;
    margin-top: 24px;
}

.btn-hero {
    padding: 14px 32px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    text-decoration: none;
    transition: 0.3s;
    display: inline-block;
}

.btn-hero-primary {
    background: #1a1982;
    color: white;
}

.btn-hero-primary:hover {
    background: #4a49d9;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(26, 25, 130, 0.3);
}

.btn-hero-secondary {
    background: transparent;
    color: #1a1982;
    border: 2px solid #1a1982;
}

.btn-hero-secondary:hover {
    background: #1a1982;
    color: white;
}

/* === АДАПТИВ === */
@media (max-width: 1100px) {
    .hero-minimal-split { min-height: 500px; padding-top: 100px; }
    .hero-main-title { font-size: 38px; }
    .hero-content-wrapper { padding: 0 40px 0 60px; }
    .hero-image-box { max-width: 550px; min-height: 380px; }
}

@media (max-width: 968px) {
    .hero-minimal-split {
        flex-direction: column;
        padding-top: 90px;
        min-height: auto;
    }
    .hero-content-wrapper {
        max-width: 100%;
        padding: 40px 30px;
        text-align: center;
    }
    .hero-text-content { margin: 0 auto; }
    .hero-buttons { justify-content: center; }
    .hero-image-wrapper {
        justify-content: center;
        padding: 20px 20px 40px;
    }
    .hero-image-box {
        max-width: 100%;
        height: 360px;
        border-radius: 16px 0 0 16px;
    }
    .section { padding: 60px 15px; }
    .section-title { font-size: 28px; }
    .projects-grid, .news-grid { grid-template-columns: 1fr; }
}

@media (max-width: 480px) {
    .hero-main-title { font-size: 30px; }
    .hero-description { font-size: 16px; }
    .hero-image-box { height: 280px; border-radius: 12px 0 0 12px; }
    .section-title { font-size: 24px; }
    .direction-name, .project-title, .team-name, .news-title { font-size: 18px; }
}
    </style>
</head>
<body>
    <?php 
				$context = 'lab';
				require_once '../header.php'; 
			?>
    
    <!-- HERO SECTION - Минималистичный сплит -->
<section class="hero-minimal-split">
    <div class="hero-content-wrapper">
        <div class="hero-text-content">
            <h1 class="hero-main-title">
                Лаборатория перспективных<br>
                <span class="title-highlight">энергетических технологий</span>
            </h1>
            <p class="hero-description">
             
            </p>
        </div>
    </div>
    <div class="hero-image-wrapper">
        <div class="hero-image-box">
            <img src="img/la.jpg" alt="Лаборатория" class="hero-background-image">
        </div>
    </div>
</section>
    
    <!-- О ЛАБОРАТОРИИ -->
	<section class="section" style="background: white; border-radius: 20px; margin-top: 40px; position: relative; z-index: 10; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
		<h2 class="section-title">О ЛАБОРАТОРИИ</h2>
		<p class="about-text">
			Лаборатория ПЭТ занимается разработкой инновационных технологий и устройств 
			для снижения затрат энергетических ресурсов и повышения эффективности энергетических процессов. 
		</p>
		<div class="view-all">
			<a href="about.php" class="view-all-link">Подробнее о нас </a>
		</div>
	</section>
    
    <!-- НАПРАВЛЕНИЯ -->
    <section class="section">
        <h2 class="section-title">НАПРАВЛЕНИЯ РАБОТЫ</h2>
        <div class="directions-list">
            <?php if (!empty($directions)): ?>
                <?php foreach ($directions as $dir): ?>
                <div class="direction-item">
                    <div class="direction-name"><?= htmlspecialchars($dir['name']) ?></div>
                    <p class="direction-desc"><?= htmlspecialchars(mb_strimwidth(strip_tags($dir['description']), 0, 200, '...')) ?></p>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="direction-item"><div class="direction-name">Энергоэффективность</div><p class="direction-desc">Технологии снижения потребления энергоресурсов в промышленности и ЖКХ. Энергетический аудит и оптимизация процессов.</p></div>
                <div class="direction-item"><div class="direction-name">Интеллектуальные системы</div><p class="direction-desc">Разработка интеллектуальных систем управления энергосетями. Мониторинг, аналитика и автоматизация энергетических процессов.</p></div>
            <?php endif; ?>
        </div>
        <div class="view-all">
			<a href="about.php#directions" class="view-all-link">Подробнее о нас </a>
		</div>
    </section>
    
    <!-- ПРОЕКТЫ -->
    <!-- ПРОЕКТЫ -->
<section class="section" id="projects" style="background: #f9f9fb; border-radius: 20px;">
    <h2 class="section-title">ПРОЕКТЫ</h2>
    <?php
    // Получаем проекты из таблицы lab_projects
    try {
        $stmt = $pdo->prepare("SELECT id, name, short_description, img_url, status FROM lab_projects ORDER BY sort_order ASC, start_date DESC LIMIT 3");
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $projects = [];
    }
    ?>
    <?php if (empty($projects)): ?>
        <div style="text-align: center; padding: 60px 20px; color: #777;">
            <p style="font-size: 18px; margin-bottom: 20px;">Проекты временно недоступны</p>
            <a href="projects.php" class="view-all-link">Перейти к проектам →</a>
        </div>
    <?php else: ?>
        <div class="projects-grid">
            <?php foreach ($projects as $proj): ?>
            <div class="project-card">
                <img src="<?= htmlspecialchars($proj['img_url'] ?? 'img/default_project.png') ?>" alt="<?= htmlspecialchars($proj['name']) ?>" class="project-image">
                <div class="project-content">
                    <div class="project-title"><?= htmlspecialchars($proj['name']) ?></div>
                    <p class="project-desc"><?= htmlspecialchars(mb_strimwidth(strip_tags($proj['short_description'] ?? ''), 0, 150, '...')) ?></p>
                    <a href="project_detail.php?id=<?= $proj['id'] ?>" class="project-link">Подробнее →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="view-all"><a href="projects.php" class="view-all-link">Все проекты →</a></div>
    <?php endif; ?>
</section>
    
    <!-- КОМАНДА -->
    <section class="section">
        <h2 class="section-title">КОМАНДА</h2>
        <div class="team-grid">
            <?php if (!empty($team)): ?>
                <?php foreach ($team as $member): ?>
                <div class="team-member">
                    <img src="<?= htmlspecialchars($member['photo_url'] ?? 'img/team/default.jpg') ?>" alt="<?= htmlspecialchars($member['name']) ?>" class="team-photo">
                    <div class="team-name"><?= htmlspecialchars($member['name']) ?></div>
                    <div class="team-position"><?= htmlspecialchars($member['position']) ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Заглушки с фото выше и по центру -->
                <div class="team-member">
                    <div class="team-photo" style="display: flex; align-items: center; justify-content: center; background: #e7e8f3; color: #1a1982; font-size: 50px;">👤</div>
                    <div class="team-name">Иванов Иван Иванович</div>
                    <div class="team-position">Руководитель лаборатории</div>
                </div>
                <div class="team-member">
                    <div class="team-photo" style="display: flex; align-items: center; justify-content: center; background: #e7e8f3; color: #1a1982; font-size: 50px;">👤</div>
                    <div class="team-name">Петрова Мария Сергеевна</div>
                    <div class="team-position">Ведущий инженер</div>
                </div>
                <div class="team-member">
                    <div class="team-photo" style="display: flex; align-items: center; justify-content: center; background: #e7e8f3; color: #1a1982; font-size: 50px;">👤</div>
                    <div class="team-name">Сидоров Алексей Петрович</div>
                    <div class="team-position">Научный сотрудник</div>
                </div>
            <?php endif; ?>
        </div>
        <div class="view-all"><a href="about.php#team" class="view-all-link">Вся команда </a></div>
    </section>
    
    <!-- НОВОСТИ -->
<!--<section class="section" style="background: #f9f9fb; border-radius: 20px;">
    <h2 class="section-title">ПОСЛЕДНИЕ НОВОСТИ</h2>
    <?php
    // Получаем новости только для лаборатории (section = 'lab')
    try {
        $stmt = $pdo->prepare("
            SELECT n.id, n.title, n.content, n.datetime, ni.image_url
            FROM news n
            LEFT JOIN news_images ni ON n.id = ni.news_id AND ni.is_main = 1
            WHERE n.section = 'lab'
            ORDER BY n.datetime DESC
            LIMIT 3
        ");
        $stmt->execute();
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $news = [];
    }
    ?>
    <?php if (empty($news)): ?>
        <div class="news-grid">
            <div class="news-card">
                <img src="img/default_news.png" alt="Пример новости" class="news-image">
                <div class="news-content">
                    <div class="news-date">01.04.2024</div>
                    <div class="news-title">Запуск нового проекта по энергоэффективности</div>
                    <p class="news-excerpt">Наша лаборатория приступает к реализации масштабного исследования...</p>
                </div>
            </div>
        </div>
        <div class="view-all"><a href="news.php" class="view-all-link">Все новости →</a></div>
    <?php else: ?>
        <div class="news-grid">
            <?php foreach ($news as $item): ?>
            <a href="news.php?id=<?= $item['id'] ?>" class="news-card" style="text-decoration: none; color: inherit;">
                <img src="<?= htmlspecialchars($item['image_url'] ?? 'img/default_news.png') ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="news-image">
                <div class="news-content">
                    <div class="news-date"><?= date('d.m.Y', strtotime($item['datetime'])) ?></div>
                    <div class="news-title"><?= htmlspecialchars($item['title']) ?></div>
                    <p class="news-excerpt"><?= htmlspecialchars(mb_strimwidth(strip_tags($item['content']), 0, 120, '...')) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="view-all"><a href="news.php" class="view-all-link">Все новости →</a></div>
    <?php endif; ?>
</section>-->
    
    <?php
if (!isset($context)) {
    $context = 'lab';
}
require_once '../footer.php';
?>
</body>
</html>