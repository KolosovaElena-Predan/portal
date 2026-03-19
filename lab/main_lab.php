<?php
session_start();

// Подключение к БД
require_once 'config.php';

// Получаем 3 последние новости
try {
    $stmt = $pdo->prepare("SELECT title, content, image_url, datetime FROM News ORDER BY datetime DESC LIMIT 3");
    $stmt->execute();
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $news = [];
    error_log("Ошибка загрузки новостей: " . $e->getMessage());
}

// Получаем устройства (разработки) из БД
try {
    $stmt = $pdo->prepare("SELECT id, name, description, img_url FROM Device_type ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $devices = [];
    error_log("Ошибка загрузки устройств: " . $e->getMessage());
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
    <title>Главная</title>
</head>
<body>
<div class="screen" style="background-color: #ffffff">
    <div class="div" style="background-color: #ffffff">
		<div class="overlap-4">
			<div class="text-wrapper-7">Инновации для умного потребления энергии</div>
            <p class="text-wrapper-6">
                Лаборатория «Перспективные энергетические технологии» предлагает решения для автоматизации и оптимизации
                систем жизнеобеспечения.<br />Подберите оборудование, напишите или свяжитесь с нами напрямую.
            </p>
            <!--<button class="btn1" onclick="location.href='catalog.php'">
                Перейти в каталог
            </button>-->
        </div>
        <div class="overlap">
            <div class="view">
                <div class="overlap-group">
                    <div class="text-wrapper-2">О ЛАБОРАТОРИИ</div>
                    <p class="p">
						Наши разработки направлены на снижение затрат, повышение энергоэффективности и внедрение цифровых технологий.<br />
						В лаборатории активно участвуют студенты и молодые учёные, что позволяет совмещать научные исследования с подготовкой квалифицированных специалистов.
					</p>
                </div>
            </div>
            <div class="view-2">
				<div class="overlap-2">
					<div class="text-wrapper-2">НАШИ РАЗРАБОТКИ</div>

					<?php if (empty($devices)): ?>
						<!-- Если нет устройств в БД -->
						<div class="rectangle-2" style="text-align: center; padding: 20px; color: #777;">
							<p>Разработки временно недоступны</p>
						</div>
					<?php else: ?>
						<?php foreach ($devices as $dev): ?>
							<!-- Оставляем rectangle-2 как внешний контейнер (со стилями) -->
							<div class="rectangle-2">
								<!-- Внутри — flex-раскладка -->
								<div class="product-flex-layout">
									<!-- Левая часть: изображение -->
									<div class="product-image-side">
										<img src="<?= htmlspecialchars($dev['img_url']) ?>" 
											 alt="<?= htmlspecialchars($dev['name']) ?>" 
											 style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
									</div>

									<!-- Правая часть: название, описание, кнопка -->
									<div class="product-content-side">
										<!-- Название -->
										<div class="text-wrapper-5"><?= htmlspecialchars($dev['name']) ?></div>

										<!-- Краткое описание -->
										<p class="text-wrapper-4">
											<?= htmlspecialchars(mb_strimwidth(strip_tags($dev['description']), 0, 200, '...')) ?>
										</p>

										<!-- Кнопка -->
										<a href="product.php?id=<?= $dev['id'] ?>" style="text-decoration: none; color: inherit;">
											<div class="text-wrapper-3">Подробнее</div>
										</a>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
        </div>


		<!-- Контейнер: новости слева, поддержка справа -->
		<div class="content-flex-container" style="
			display: flex;
			gap: 40px;
			width: 80%;
			margin: 60px 40px;
			align-items: flex-start;
		">

			<!-- ЛЕВАЯ ЧАСТЬ: Новости -->
			<div class="news-section" style="flex: 1; min-width: 300px;">
				<div class="text-wrapper-9">ПОСЛЕДНИЕ НОВОСТИ</div>

				<?php if (empty($news)): ?>
    <div class="view-3">
        <div class="text-wrapper-11">Новости временно недоступны</div>
        <p class="text-wrapper-12">Следите за обновлениями.</p>
    </div>
				<?php else: ?>
					<?php foreach ($news as $item): ?>
						<div class="news-card" style="
							display: flex;
							gap: 20px;
							margin-bottom: 20px;
							background-color: #f7f7fd;
							border-radius: 12px;
							padding: 20px;
							box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
						">
							<!-- Левая часть: изображение -->
							<div class="news-image-side" style="flex: 0 0 200px; height: 150px;">
								<img src="<?= htmlspecialchars($item['image_url']) ?>" 
									 alt="<?= htmlspecialchars($item['title']) ?>" 
									 style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
							</div>

							<!-- Правая часть: название и описание -->
							<div class="news-content-side" style="flex: 1;">
								<div class="text-wrapper-11"><?= htmlspecialchars($item['title']) ?></div>
								<p class="text-wrapper-12">
									<?= htmlspecialchars(mb_strimwidth(strip_tags($item['content']), 0, 200, '...')) ?>
								</p>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<!-- ПРАВАЯ ЧАСТЬ: Поддержка (низкий блок) -->
			<div class="support-section">
				<div class="text-wrapper-10">ПОДДЕРЖКА</div>
				<p class="text-wrapper-13" style="margin: 16px 0;">
					Наши специалисты всегда готовы помочь вам с выбором, настройкой и эксплуатацией оборудования.
				</p>

				<!-- КНОПКА "ЗАДАТЬ ВОПРОС" -->
				<?php if (isset($_SESSION['user_id'])): ?>
					<a href="question.php">
						<button class="btn btn-1">Задать вопрос</button>
					</a>
				<?php else: ?>
					<a href="question.php">
						<button class="btn btn-1" style="font-size: 18px; padding: 15px 30px">Задать вопрос</button>
					</a>
				<?php endif; ?>
			</div>

		</div>
        <?php require_once 'footer.php'; ?>
        <?php require_once 'header.php'; ?>
    </div>
</div>
</body>
</html>