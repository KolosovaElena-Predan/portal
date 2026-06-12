<?php
session_start();
require_once '../config.php';

// Получаем данные авторизованного пользователя
$userData = null;
$user_id = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT name, email FROM user WHERE id = ?");
        $stmt->execute([$user_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error loading user: " . $e->getMessage());
    }
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['question'] ?? '');
    $consent = isset($_POST['consent']);
    
    if (!$name || !$email || !$message) {
        $error = 'Все поля обязательны для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email';
    } elseif (!$consent) {
        $error = 'Необходимо согласие на обработку персональных данных';
    } else {
        try {
            // Сохраняем вопрос в таблицу request
            // type = 'q' означает вопрос (question)
            $stmt = $pdo->prepare("
                INSERT INTO request (user_id, message, status, datetime, type) 
                VALUES (?, ?, 'new', NOW(), 'q')
            ");
            
            // Формируем сообщение с данными пользователя
            $full_message = json_encode([
                'name' => $name,
                'email' => $email,
                'question' => $message,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
            
            $stmt->execute([$user_id ?? 0, $full_message]);
            
            $success = 'Ваш вопрос отправлен! Мы ответим вам в ближайшее время.';
            $_POST = []; // Очищаем форму
            
        } catch (Exception $e) {
            $error = 'Ошибка при отправке вопроса. Пожалуйста, попробуйте позже.';
            error_log("Error saving question: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/header_lab.css" />
    <link rel="stylesheet" href="../css/footer.css" />
    <title>Поддержка — Лаборатория ПЭТ</title>
    <style>
        .question-page {
            max-width: 1200px;
            margin: 150px auto 80px;
            padding: 0 20px;
        }
        .question-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            align-items: start;
        }
        
        /* Левая колонка — Контакты */
        .contacts-card {
            background: linear-gradient(135deg, #1a1982 0%, #2d2bb5 100%);
            border-radius: 20px;
            padding: 40px 30px;
            color: white;
            position: sticky;
            top: 140px;
        }
        .contacts-title {
            font-family: "Inter-Bold", sans-serif;
            font-size: 24px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255,255,255,0.3);
        }
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 25px;
        }
        .contact-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .contact-icon i {
            font-size: 18px;
        }
        .contact-text {
            font-size: 15px;
            line-height: 1.5;
        }
        .contact-text strong {
            display: block;
            margin-bottom: 3px;
            font-weight: 600;
        }
        .contact-text a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        .contact-text a:hover {
            opacity: 0.9;
            text-decoration: underline;
        }
        .social-links {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }
        .social-link {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            transition: all 0.3s;
        }
        .social-link:hover {
            background: white;
            color: #1a1982;
            transform: translateY(-3px);
        }
        
        /* Правая колонка — Форма */
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
            font-size: 32px;
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
        
        /* Адаптивность */
        @media (max-width: 968px) {
            .question-layout {
                grid-template-columns: 1fr;
            }
            .contacts-card {
                position: static;
                order: 2;
            }
            .question-card {
                order: 1;
                padding: 40px 30px;
            }
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
            .contacts-card {
                padding: 30px 20px;
            }
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
            
            <div class="question-page">
                <div class="question-layout">
                    
                    <!-- ЛЕВАЯ КОЛОНКА: Контакты -->
                    <div class="contacts-card">
                        <div class="contacts-title">Контакты</div>
                        
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="contact-text">
                                <strong>Адрес</strong>
                                г. Чита, ул. Баргузинская, 49
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-phone"></i></div>
                            <div class="contact-text">
                                <strong>Телефон</strong>
                                <a href="tel:+73022123456">+7 (924) 371-62-05</a>
                            </div>
                        </div>
                        
                        <!--<div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div class="contact-text">
                                <strong>Email</strong>
                                <a href="mailto:support@pet-lab.ru">support@pet-lab.ru</a>
                            </div>
                        </div>-->
                    </div>
                    
                    <!-- ПРАВАЯ КОЛОНКА: Форма -->
                    <div class="question-card">
                        <div class="question-header">
                            <h1 class="question-title">Задать вопрос</h1>
                        </div>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="question-form">
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
                                    value="<?= htmlspecialchars($_POST['full_name'] ?? $userData['name'] ?? '') ?>"
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
                                    value="<?= htmlspecialchars($_POST['email'] ?? $userData['email'] ?? '') ?>"
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
                                    placeholder="Опишите ваш вопрос подробно..."
                                ><?= htmlspecialchars($_POST['question'] ?? '') ?></textarea>
                            </div>

                            <!-- Согласие -->
                            <div class="consent-box">
                                <input 
                                    type="checkbox" 
                                    name="consent" 
                                    id="consent"
                                    required
                                    <?= isset($_POST['consent']) ? 'checked' : '' ?>
                                >
                                <label for="consent">
                                    Я соглашаюсь на обработку персональных данных и принимаю 
                                    <a href="privacy_policy.php" target="_blank">политику конфиденциальности</a>
                                </label>
                            </div>

                            <!-- Кнопка -->
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane"></i>
                                Отправить вопрос
                            </button>
                        </form>
                    </div>
                    
                </div>
            </div>
            
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