<?php
session_start();
require_once 'config.php';

// Получаем направления
try {
    $stmt = $pdo->prepare("SELECT * FROM directions ORDER BY sort_order ASC");
    $stmt->execute();
    $directions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $directions = [];
}

// Получаем оборудование
try {
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE is_available = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $equipment = [];
}

// Получаем команду
try {
    $stmt = $pdo->prepare("SELECT * FROM team ORDER BY sort_order ASC");
    $stmt->execute();
    $team = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $team = [];
}

// Получаем образовательные программы
try {
    $stmt = $pdo->prepare("SELECT * FROM education WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $education = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/header_lab.css" />
    <link rel="stylesheet" href="css/main_lab.css" />
    <link rel="stylesheet" href="css/footer.css" />
    <title>О лаборатории — Лаборатория ПЭТ</title>
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
        
        /* === НАПРАВЛЕНИЯ === */
        .directions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        .direction-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #e7e8f3;
        }
        .direction-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(26,25,130,0.12);
            border-color: #1a1982;
        }
        .direction-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: #1a1982;
        }
        .direction-name {
            font-size: 22px;
            font-weight: 700;
            color: #1a1982;
            margin-bottom: 12px;
        }
        .direction-desc {
            font-size: 15px;
            color: #555;
            line-height: 1.7;
        }
        
        /* === ОБОРУДОВАНИЕ: Компактный список === */
        .equipment-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 900px;
            margin: 0 auto;
        }
        .equipment-item {
            display: flex;
            align-items: center;
            gap: 20px;
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid #e7e8f3;
        }
        .equipment-item:hover {
            border-color: #1a1982;
            box-shadow: 0 4px 15px rgba(26,25,130,0.1);
            transform: translateX(5px);
        }
        .equipment-item-img {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            background: #f0f0f0;
            flex-shrink: 0;
        }
        .equipment-item-info {
            flex: 1;
        }
        .equipment-item-name {
            font-size: 16px;
            font-weight: 700;
            color: #1a1982;
            margin-bottom: 4px;
        }
        .equipment-item-meta {
            font-size: 13px;
            color: #888;
            margin-bottom: 6px;
        }
        .equipment-item-desc {
            font-size: 14px;
            color: #555;
            line-height: 1.5;
        }
        
        /* === КОМАНДА: 3 карточки в ряд === */
        .team-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            padding: 20px 10px;
        }
        .team-card-3 {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }
        .team-card-3:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(26,25,130,0.15);
        }
        .team-photo-3 {
            width: 100%;
            height: 220px;
            background: #e7e8f3;
        }
        .team-info-3 {
            padding: 18px 15px;
        }
        .team-name-3 {
            font-size: 17px;
            font-weight: 700;
            color: #1a1982;
            margin-bottom: 4px;
        }
        .team-position-3 {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
        }
        .team-contact-3 {
            font-size: 12px;
            color: #888;
        }
        
        /* === ОБРАЗОВАНИЕ === */
        .education-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .education-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            gap: 25px;
            align-items: flex-start;
        }
        .education-icon {
            font-size: 36px;
            color: #1a1982;
            flex-shrink: 0;
        }
        .education-content {
            flex: 1;
        }
        .education-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a1982;
            margin-bottom: 8px;
        }
        .education-type {
            display: inline-block;
            background: #e7e8f3;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            color: #1a1982;
            margin-bottom: 10px;
        }
        .education-desc {
            font-size: 14px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        .education-duration {
            font-size: 13px;
            color: #888;
        }
        
        /* Адаптивность */
        @media (max-width: 992px) {
            .team-grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 768px) {
            .team-grid-3 {
                grid-template-columns: 1fr;
            }
            .team-photo-3 {
                height: 260px;
            }
            .equipment-item {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }
            .equipment-item-img {
                width: 100px;
                height: 100px;
            }
            .education-card {
                flex-direction: column;
            }
        }
		
	/* === Оборудование на всю ширину === */
.section-equipment-full {
    width: 100%;
    background: #f9f9fb;
    padding: 60px 0;
    border-radius: 0;
}

.section-equipment-full .section-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.section-equipment-full .section-title {
    margin-bottom: 40px;
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
            
            <!-- О лаборатории -->
            <section class="section" id="about" style="padding-top: 40px;">
                <h2 class="section-title">О ЛАБОРАТОРИИ</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; margin-bottom: 50px;">
                    <div style="position: relative; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 30px rgba(26,25,130,0.15);">
                        <img src="img/la.jpg" 
                             alt="Лаборатория ПЭТ" 
                             style="width: 100%; height: auto; display: block;"
                             onerror="this.src='https://via.placeholder.com/600x400/1a1982/ffffff?text=Лаборатория+ПЭТ'">
                    </div>
                    <div>
                        <h3 style="font-family: 'Inter-Bold', Helvetica, sans-serif; font-size: 24px; color: #1a1982; margin-bottom: 20px;">
                            Современный научно-исследовательский центр
                        </h3>
                        <p style="font-size: 16px; color: #555; line-height: 1.8; margin-bottom: 15px;">
                            Лаборатория ПЭТ занимается разработкой инновационных технологий и устройств 
                            для снижения затрат энергетических ресурсов и повышения эффективности энергетических процессов.
                        </p>
                        <p style="font-size: 16px; color: #555; line-height: 1.8; margin-bottom: 25px;">
                            Наша миссия — развитие инновационных технологий и подготовка высококвалифицированных 
                            специалистов для работы в сфере высоких технологий.
                        </p>
                    </div>
                </div>
            </section>
            
            <!-- Направления работы -->
            <section class="section" id="directions">
                <h2 class="section-title">НАПРАВЛЕНИЯ РАБОТЫ</h2>
                <div class="directions-grid">
                    <?php if (!empty($directions)): ?>
                        <?php foreach ($directions as $dir): ?>
                        <div class="direction-card">
                            <div class="direction-name"><?= htmlspecialchars($dir['name']) ?></div>
                            <p class="direction-desc"><?= htmlspecialchars($dir['description']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="direction-card">
                            <div class="direction-name">Возобновляемая энергетика</div>
                            <p class="direction-desc">Исследования в области солнечной, ветровой и геотермальной энергетики.</p>
                        </div>
                        <div class="direction-card">
                            <div class="direction-name">Энергоэффективность</div>
                            <p class="direction-desc">Технологии снижения потребления энергоресурсов в промышленности и ЖКХ.</p>
                        </div>
                        <div class="direction-card">
                            <div class="direction-name">Умные сети</div>
                            <p class="direction-desc">Разработка интеллектуальных систем управления энергосетями.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Оборудование: компактный список -->
			<!--<section class="section-equipment-full" id="equipment">
				<div class="section-inner">
					<h2 class="section-title">ИСПОЛЬЗУЕМОЕ ОБОРУДОВАНИЕ</h2>
					<div class="equipment-list">
						<?php if (!empty($equipment)): ?>
							<?php foreach ($equipment as $eq): ?>
							<div class="equipment-item">
								<img src="<?= htmlspecialchars($eq['img_url'] ?? 'img/default.png') ?>" 
									 alt="<?= htmlspecialchars($eq['name']) ?>" 
									 class="equipment-item-img">
								<div class="equipment-item-info">
									<div class="equipment-item-name"><?= htmlspecialchars($eq['name']) ?></div>
									<div class="equipment-item-meta">
										<?= htmlspecialchars($eq['manufacturer']) ?> · <?= htmlspecialchars($eq['model']) ?> · <?= $eq['year'] ?>
									</div>
									<p class="equipment-item-desc"><?= htmlspecialchars($eq['description']) ?></p>
								</div>
							</div>
							<?php endforeach; ?>
						<?php else: ?>
							<p style="text-align: center; color: #777; padding: 30px;">Оборудование временно недоступно</p>
						<?php endif; ?>
					</div>
				</div>
			</section>-->
            
            <!-- Команда: 3 карточки в ряд -->
            <section class="section" id="team">
                <h2 class="section-title">КОМАНДА</h2>
                <div class="team-grid-3">
                    <?php if (!empty($team)): ?>
                        <?php foreach ($team as $member): ?>
                        <div class="team-card-3">
                            <div class="team-photo-3">
                                <img src="<?= htmlspecialchars($member['photo_url'] ?? 'img/team/default.jpg') ?>" 
                                     alt="<?= htmlspecialchars($member['name']) ?>"
                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                            </div>
                            <div class="team-info-3">
                                <div class="team-name-3"><?= htmlspecialchars($member['name']) ?></div>
                                <div class="team-position-3"><?= htmlspecialchars($member['position']) ?></div>
                                <?php if ($member['email']): ?>
                                <div class="team-contact-3">📧 <?= htmlspecialchars($member['email']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Заглушки -->
                        <div class="team-card-3">
                            <div class="team-photo-3" style="display: flex; align-items: center; justify-content: center; background: #e7e8f3;">
                                <i class="fas fa-user" style="font-size: 45px; color: #1a1982;"></i>
                            </div>
                            <div class="team-info-3">
                                <div class="team-name-3">Иванов Иван Иванович</div>
                                <div class="team-position-3">Руководитель лаборатории</div>
                            </div>
                        </div>
                        <div class="team-card-3">
                            <div class="team-photo-3" style="display: flex; align-items: center; justify-content: center; background: #e7e8f3;">
                                <i class="fas fa-user" style="font-size: 45px; color: #1a1982;"></i>
                            </div>
                            <div class="team-info-3">
                                <div class="team-name-3">Петрова Мария Сергеевна</div>
                                <div class="team-position-3">Ведущий инженер</div>
                            </div>
                        </div>
                        <div class="team-card-3">
                            <div class="team-photo-3" style="display: flex; align-items: center; justify-content: center; background: #e7e8f3;">
                                <i class="fas fa-user" style="font-size: 45px; color: #1a1982;"></i>
                            </div>
                            <div class="team-info-3">
                                <div class="team-name-3">Сидоров Алексей Петрович</div>
                                <div class="team-position-3">Научный сотрудник</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Образование (закомментировано, как в оригинале) -->
            <!--<section class="section" id="education" style="background: #f9f9fb; border-radius: 20px;">
                <h2 class="section-title">ОБРАЗОВАТЕЛЬНАЯ ДЕЯТЕЛЬНОСТЬ</h2>
                <div class="education-list">
                    <?php if (!empty($education)): ?>
                        <?php foreach ($education as $edu): ?>
                        <div class="education-card">
                            <div class="education-icon">
                                <?php
                                $icons = ['course' => 'graduation-cap', 'lecture' => 'chalkboard-teacher', 'workshop' => 'tools', 'internship' => 'user-graduate'];
                                $icon = $icons[$edu['type']] ?? 'book';
                                ?>
                                <i class="fas fa-<?= $icon ?>"></i>
                            </div>
                            <div class="education-content">
                                <div class="education-title"><?= htmlspecialchars($edu['title']) ?></div>
                                <span class="education-type">
                                    <?php
                                    $types = ['course' => 'Курс', 'lecture' => 'Лекция', 'workshop' => 'Семинар', 'internship' => 'Стажировка'];
                                    echo $types[$edu['type']] ?? 'Программа';
                                    ?>
                                </span>
                                <p class="education-desc"><?= htmlspecialchars($edu['description']) ?></p>
                                <div class="education-duration">
                                    <?= htmlspecialchars($edu['duration']) ?>
                                    <?php if ($edu['start_date']): ?> | Начало: <?= date('d.m.Y', strtotime($edu['start_date'])) ?><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #777;">Образовательные программы временно недоступны</p>
                    <?php endif; ?>
                </div>
            </section>-->
            
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