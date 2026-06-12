<?php


if (!isset($context)) {
    $context = 'lab'; 
}

$BASE = '';
$lab_path = $BASE . '/lab';
$mip_path = $BASE . '/mip';
?>

<footer class="portal-footer theme-<?= $context ?>">
    <link rel="stylesheet" href="../css/footer.css">
	<link rel="stylesheet" href="css/footer.css">
    <div class="footer-container">
        
        <!-- Лого и Контакты -->
        <div class="footer-col">
            <div class="footer-logo-area">
                <?php if ($context === 'lab'): ?>
                    <img src="<?= $lab_path ?>/img/image.png" alt="Логотип" class="footer-logo-img">
                    <div class="footer-title">Лаборатория ПЭТ</div>
                <?php else: ?>
                    <img src="<?= $mip_path ?>/img/logo_mip.png" alt="Логотип" class="footer-logo-img">
                    <div class="footer-title">ООО МИП «НПЦ ПИТиА»</div>
                <?php endif; ?>
            </div>
            
            <div class="footer-contacts">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>г. Чита, ул. Баргузинская, 49</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone-alt"></i>
                    <a href="tel:+73022222222">+7 (924) 371-62-05</a>
                </div>
                <!--<div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:info@zabgu.ru">
                        <?= $context === 'lab' ? 'lab@zabgu.ru' : 'mip@zabgu.ru' ?>
                    </a>
                </div>-->
            </div>
        </div>

        <!-- Навигация -->
        <div class="footer-col">
            <h4 class="footer-heading">Навигация</h4>
            <ul class="footer-links">
                <?php if ($context === 'lab'): ?>
                    <li><a href="<?= $lab_path ?>/news.php">Новости лаборатории</a></li>
                    <li><a href="<?= $lab_path ?>/projects.php">Наши проекты</a></li>
                    <li><a href="<?= $lab_path ?>/about.php#team">Наша команда</a></li>
                <?php else: ?>
                    <li><a href="<?= $mip_path ?>/catalog.php">Каталог продукции</a></li>
                    <li><a href="<?= $mip_path ?>/services_catalog.php">Услуги</a></li>
                    <li><a href="<?= $mip_path ?>/question.php">Задать вопрос</a></li>
					
                <?php endif; ?>
            </ul>
        </div>

        <!-- Дополнительно -->
        <div class="footer-col">
            <h4 class="footer-heading">Ресурсы</h4>
            <ul class="footer-links">
                <?php if ($context === 'lab'): ?>
                    <li><a href="<?= $lab_path ?>/about.php#education">Образование</a></li>
                    <li><a href="<?= $lab_path ?>/question.php">Задать вопрос</a></li>
                <?php else: ?>
                    <li><a href="<?= $mip_path ?>/lk_user.php">Личный кабинет</a></li>
                    <li><a href="<?= $mip_path ?>/privacy.php">Политика конфиденциальности</a></li>
                    <li><a href="<?= $mip_path ?>/question.php">Сотрудничество</a></li>
                <?php endif; ?>
            </ul>
            
            <!-- Кнопка "Наверх" -->
            <!-- <a href="#" class="btn-back-to-top" title="Наверх">
                <i class="fas fa-arrow-up"></i>
            </a>-->
        </div>

    </div>

    <!-- Нижняя полоса копирайта -->
    <div class="footer-bottom">
        <div class="footer-container">
            <div class="copyright">
                &copy; <?= date("Y") ?> 
                <?= $context === 'lab' ? 'Лаборатория ПЭТ ЗабГУ' : 'ООО МИП «НПЦ ПИТиА»' ?>
            </div>
            <div class="credits">
                Все права защищены
            </div>
        </div>
    </div>
</footer>