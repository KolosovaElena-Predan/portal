<?php
session_start();
require_once 'config.php';

// Получаем данные авторизованного пользователя
$userData = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT name, email FROM user WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error loading user  " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style_header_footer.css" />
    <link rel="stylesheet" href="css/style_mip.css" />
    <link rel="stylesheet" href="css/style_main.css" />
    <link rel="stylesheet" href="css/header_mip.css" />
    <title>Задать вопрос — ООО МИП "НПЦ ПИТиА"</title>
    <style>
        body {
            /*background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);*/
            min-height: 100vh;
        }
        .question-page {
            max-width: 900px;
            margin: 150px auto 80px;
            padding: 0 20px;
        }
        .question-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        .question-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .question-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1a1982, #0d0c4a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 4px 15px rgba(26, 25, 130, 0.3);
        }
        .question-icon i {
            color: white;
            font-size: 36px;
        }
        .question-title {
            font-family: "Inter-Bold", sans-serif;
            font-size: 36px;
            color: #1a1982;
            margin: 0 0 10px;
        }
        .question-subtitle {
            font-family: "Inter-Regular", sans-serif;
            font-size: 16px;
            color: #666;
        }
        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #e8f4fd, #d4e9f7);
            border-radius: 50px;
            font-size: 14px;
            color: #1a1982;
            margin-bottom: 30px;
            border: 2px solid #1a1982;
        }
        .user-badge i {
            font-size: 18px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-label {
            display: block;
            font-family: "Inter-Medium", sans-serif;
            font-size: 15px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-label .required {
            color: #e74c3c;
            margin-left: 3px;
        }
        .input-field {
            width: 100%;
            height: 55px;
            padding: 0 20px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 16px;
            font-family: "Inter-Regular", sans-serif;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        .input-field:focus {
            outline: none;
            border-color: #1a1982;
            box-shadow: 0 0 0 4px rgba(26, 25, 130, 0.1);
        }
        .input-field:disabled {
            background: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            border-color: #e1e8ed;
        }
        textarea.input-field {
            min-height: 150px;
            padding: 20px;
            resize: vertical;
        }
        .consent-box {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin: 25px 0;
        }
        .consent-box input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            cursor: pointer;
            accent-color: #1a1982;
        }
        .consent-box label {
            font-size: 14px;
            line-height: 1.6;
            color: #555;
            cursor: pointer;
        }
        .consent-box a {
            color: #1a1982;
            text-decoration: none;
            font-weight: 500;
        }
        .consent-box a:hover {
            text-decoration: underline;
        }
        .btn-submit {
            width: 100%;
            height: 60px;
            background: linear-gradient(135deg, #1a1982 0%, #0d0c4a 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-family: "Inter-Medium", sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(26, 25, 130, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 25, 130, 0.4);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-error {
            background: #fff5f5;
            color: #c53030;
            border: 2px solid #feb2b2;
        }
        .alert-success {
            background: #f0fff4;
            color: #276749;
            border: 2px solid #9ae6b4;
        }
        .auth-notice {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 15px;
            color: #666;
        }
        .auth-notice a {
            color: #1a1982;
            text-decoration: none;
            font-weight: 600;
        }
        .auth-notice a:hover {
            text-decoration: underline;
        }
        .field-hint {
            font-size: 13px;
            color: #666;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .field-hint i {
            font-size: 12px;
        }
        @media (max-width: 768px) {
            .question-card {
                padding: 30px 20px;
            }
            .question-title {
                font-size: 28px;
            }
            .btn-submit {
                height: 55px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="screen">
        <div class="div">
            <?php require_once 'header_mip.php'; ?>

            <div class="question-page">
                <div class="question-card">


                    

                    <!-- Форма -->
                    <form action="submit_question.php" method="POST" class="question-form">
                        <!-- ФИО -->
                        <div class="form-group">
                            <label class="form-label">
                                Ваше имя
                                <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="full_name" 
                                class="input-field"
                                value="<?= htmlspecialchars($userData['name'] ?? '') ?>"
                                <?= $userData ? 'readonly' : 'required' ?>
                                placeholder="Иванов Иван Иванович"
                            >
                            
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label class="form-label">
                                Электронная почта
                                <span class="required">*</span>
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                class="input-field"
                                value="<?= htmlspecialchars($userData['email'] ?? '') ?>"
                                <?= $userData ? 'readonly' : 'required' ?>
                                placeholder="example@mail.ru"
                            >
                            
                        </div>

                        <!-- Вопрос -->
                        <div class="form-group">
                            <label class="form-label">
                                Ваш вопрос
                                <span class="required">*</span>
                            </label>
                            <textarea 
                                name="question" 
                                class="input-field"
                                required
                                placeholder="Вопрос..."
                            ></textarea>
                        </div>

                        <!-- Согласие -->
                        <div class="consent-box">
                            <input 
                                type="checkbox" 
                                name="consent" 
                                id="consent"
                                required
                            >
                            <label for="consent">
                                Я соглашаюсь на обработку персональных данных и принимаю 
                                <a href="privacy_policy.php" target="_blank">политику конфиденциальности</a>
                            </label>
                        </div>

                        <!-- Кнопка -->
                        <button type="submit" class="btn-submit">
                           
                            Отправить вопрос
                        </button>
                    </form>
                </div>
            </div>

            <?php require_once 'footer_mip.php'; ?>
        </div>
    </div>
</body>
</html>