<?php
$pageTitle = 'Политика обработки персональных данных';
$context = 'lab';
require_once 'header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Политика обработки персональных данных | МИП «НПЦ ПИТиА»</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            color: #1e293b;
            line-height: 1.6;
        }
        
        .policy-container {
            max-width: 1000px;
            margin: 140px auto 80px;
            padding: 0 20px;
        }
        
        .policy-card {
            background: white;
            border-radius: 24px;
            padding: 50px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1a1982;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .last-updated {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        h2 {
            font-size: 22px;
            font-weight: 600;
            color: #0f172a;
            margin: 30px 0 15px 0;
            padding-left: 15px;
            border-left: 4px solid #1a1982;
        }
        
        p {
            color: #475569;
            margin-bottom: 15px;
            line-height: 1.7;
        }
        
        ul, ol {
            margin: 15px 0 15px 30px;
            color: #475569;
        }
        
        li {
            margin-bottom: 8px;
        }
        
        .consent-box {
            background: #f0f4ff;
            padding: 20px;
            border-radius: 12px;
            margin: 25px 0;
            border-left: 3px solid #1a1982;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 40px;
            padding: 12px 24px;
            background: #f1f5f9;
            color: #1a1982;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            background: #e2e8f0;
            transform: translateX(-5px);
        }
        
        @media (max-width: 768px) {
            .policy-card {
                padding: 30px 20px;
            }
            h1 {
                font-size: 26px;
            }
            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="policy-container">
    <div class="policy-card">
        <h1>Политика обработки персональных данных</h1>
        <div class="last-updated">
            Дата последнего обновления: 13 июня 2026 г.
        </div>
        
        <div class="consent-box">
            <strong>Ваше согласие</strong>
            <p style="margin-top: 10px; margin-bottom: 0;">
                Регистрируясь на нашем сайте, вы даёте согласие на обработку персональных данных 
                в соответствии с настоящей Политикой.
            </p>
        </div>
        
        <h2>1. Какие данные обрабатываются</h2>
        <ul>
            <li>Имя, фамилия</li>
            <li>Адрес электронной почты</li>
            <li>Номер телефона</li>
            <li>Адрес доставки</li>
            <li>История заказов</li>
            <li>IP-адрес и данные о посещениях</li>
        </ul>
        
        <h2>2. Цели обработки данных</h2>
        <ul>
            <li><strong>Регистрация и авторизация</strong> — для создания и управления учётной записью</li>
            <li><strong>Обработка заказов</strong> — для оформления и доставки товаров и услуг</li>
            <li><strong>Коммуникация</strong> — для ответа на вопросы и уведомлений о статусе заказов</li>
            <li><strong>Улучшение сервиса</strong> — для анализа работы сайта</li>
            <li><strong>Безопасность</strong> — для предотвращения мошенничества</li>
        </ul>
        
        <h2>3. Срок хранения данных</h2>
        <p>
            Ваши данные хранятся до момента удаления учётной записи или в течение срока, 
            необходимого для выполнения обязательств перед вами и соблюдения требований законодательства.
        </p>
        
        <h2>4. Права пользователя</h2>
        <ul>
            <li>Получать информацию о том, какие данные о вас хранятся</li>
            <li>Исправлять неточные данные</li>
            <li>Удалить свою учётную запись и все связанные с ней данные</li>
            <li>Отозвать согласие на обработку данных</li>
        </ul>
        <p>
            Для реализации ваших прав отправьте запрос на email: <a href="mailto:mip@zabgu.ru">mip@zabgu.ru</a>
        </p>
        
        <h2>5. Передача данных третьим лицам</h2>
        <p>
            Мы не передаём ваши персональные данные третьим лицам, за исключением случаев, 
            предусмотренных законом, или когда это необходимо для доставки заказа (службы доставки).
        </p>
        
        <h2>6. Защита данных</h2>
        <p>
            Мы используем современные методы защиты данных: шифрование SSL/TLS, 
            защищённые серверы, регулярное обновление программного обеспечения 
            и ограничение доступа к персональным данным.
        </p>
        
        <h2>7. Контактная информация</h2>
        <p>
            По всем вопросам, связанным с обработкой персональных данных, вы можете обратиться:<br>
            <strong>Email:</strong> <a href="mailto:mip@zabgu.ru">mip@zabgu.ru</a><br>
            <strong>Адрес:</strong> г. Чита, ул. Александрово-Заводская, 30
        </p>
        
        <a href="authorization.php" class="back-link">← Вернуться к регистрации</a>
    </div>
</div>

<?php require_once 'footer.php'; ?>
</body>
</html>