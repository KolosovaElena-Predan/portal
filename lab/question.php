<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="style_main.css" />
    <title>Задать вопрос</title>
    <style>
        /* Стиль для согласия — можно позже перенести в CSS-файлы */
        .consent-container {
            margin: 20px 0;
            font-size: 16px;
            line-height: 1.4;
        }

        .consent-container input[type="checkbox"] {
            margin-right: 8px;
            transform: scale(1.2);
            vertical-align: middle;
        }

        .consent-container a {
            color: #007bff;
            text-decoration: none;
        }

        .consent-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="screen">
        <div class="div">
            <!-- Шапка -->
            <?php require_once 'header.php'; ?>

            <!-- Основное содержимое -->
            <div class="login-container">
                <div class="login-box" style="height: 550px;">
                    <h2 class="login-title">Задайте свой вопрос</h2>

                    <form action="submit_question.php" method="POST" class="question-form">
                        <input
                            type="text"
                            name="full_name"
                            placeholder="Ваше ФИО"
                            class="input-field"
                            required
                        />
                        <input
                            type="email"
                            name="email"
                            placeholder="Электронная почта"
                            class="input-field"
                            required
                        />
                        <input
                            type="text"
                            name="device_name"
                            placeholder="Наименование устройства"
                            class="input-field"
                        />
                        <textarea
                            name="question"
                            placeholder="Ваш вопрос"
                            class="input-field"
                            rows="4"
                            required
                        ></textarea>

                        <!-- Согласие на обработку персональных данных -->
                        <div class="consent-container">
                            <label>
                                <input type="checkbox" name="consent" required>
                                Я соглашаюсь на обработку персональных данных и принимаю
                                <a href="privacy_policy.php" target="_blank">политику конфиденциальности</a>.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">Отправить</button>
                    </form>
                </div>
            </div>

            <!-- Подвал -->
            <?php require_once 'footer.php'; ?>
        </div>
    </div>
</body>
</html>