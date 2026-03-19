<?php
require_once __DIR__ . '/../config.php';
?>

<h2 class="admin-title">Главная</h2>

<div class="dashboard-grid">
    <div class="stat-card">
        <h3>Оборудование</h3>
        <p><?= (int)$pdo->query("SELECT COUNT(*) FROM device_type")->fetchColumn() ?></p>
    </div>
    <div class="stat-card">
        <h3>Пользователи</h3>
        <p><?= (int)$pdo->query("SELECT COUNT(*) FROM user")->fetchColumn() ?></p>
    </div>
    <div class="stat-card">
        <h3>Запросы</h3>
        <p><?= (int)$pdo->query("SELECT COUNT(*) FROM request WHERE status = 'new'")->fetchColumn() ?></p>
    </div>
    <div class="stat-card">
        <h3>Новости</h3>
        <p><?= (int)$pdo->query("SELECT COUNT(*) FROM news")->fetchColumn() ?></p>
    </div>
</div>