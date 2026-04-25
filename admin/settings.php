<?php
$activePage = 'settings';
$pageTitle = 'Настройки сайта | Админ-панель';
require_once 'includes/auth_check.php';

// Проверка прав: только админ
if (!in_array($_SESSION['role'] ?? '', ['admin'])) {
    redirect('lk_admin.php');
}

$error = '';
$success = '';
$sections = [];

// ============================================
// Обработка сохранения
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    try {
        $pdo->beginTransaction();
        
        // Получаем все настройки из БД для валидации ключей
        $validKeys = $pdo->query("SELECT `key`, `type`, `options` FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        
        foreach ($_POST['settings'] as $key => $value) {
            // Пропускаем несуществующие ключи (защита)
            if (!array_key_exists($key, $validKeys)) continue;
            
            // Обработка чекбоксов
            $type = $validKeys[$key] ?? 'text';
            if ($type === 'checkbox') {
                $value = isset($_POST['settings'][$key]) ? '1' : '0';
            }
            
            $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = ?");
            $stmt->execute([$value, $key]);
        }
        
        $pdo->commit();
        $success = 'Настройки сохранены!';
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Ошибка БД: ' . $e->getMessage();
    }
}

// ============================================
// Загрузка настроек для отображения
// ============================================
$settingsRaw = $pdo->query("SELECT * FROM settings ORDER BY section, sort_order, `key`")->fetchAll();

// Группируем по секциям
foreach ($settingsRaw as $s) {
    $sections[$s['section']][] = $s;
}

// Перевод названий секций
$sectionLabels = [
    'general' => '📋 Общие',
    'contacts' => '📞 Контакты',
    'social' => '🔗 Соцсети',
    'system' => '⚙️ Системные'
];

$pageScript = '
// Автогенерация slug-подобных значений (если нужно)
document.querySelectorAll("[data-autoslug]").forEach(input => {
    input.addEventListener("input", function() {
        const target = document.getElementById(this.dataset.autoslug);
        if (target) {
            let slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)/g, "");
            target.value = slug;
        }
    });
});
';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">⚙️ Настройки сайта</h1>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="card">
                <div class="card-header">
                    <h3 class="card-title">Редактирование параметров</h3>
                </div>
                
                <div class="card-body">
                    <!-- Вкладки по секциям -->
                    <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                        <?php $first = true; foreach ($sections as $section => $items): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $first ? 'active' : '' ?>" 
                               id="tab-<?= e($section) ?>" 
                               data-toggle="tab" 
                               href="#content-<?= e($section) ?>" 
                               role="tab">
                                <?= $sectionLabels[$section] ?? e($section) ?>
                            </a>
                        </li>
                        <?php $first = false; endforeach; ?>
                    </ul>
                    
                    <div class="tab-content mt-3" id="settingsTabContent">
                        <?php $first = true; foreach ($sections as $section => $items): ?>
                        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" 
                             id="content-<?= e($section) ?>" 
                             role="tabpanel">
                            
                            <div class="row">
                                <?php foreach ($items as $setting): ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?= e($setting['label']) ?></label>
                                        
                                        <?php 
                                        $key = $setting['key'];
                                        $value = $setting['value'];
                                        $type = $setting['type'];
                                        $options = json_decode($setting['options'] ?? '[]', true);
                                        ?>
                                        
                                        <?php if ($type === 'textarea'): ?>
                                            <textarea name="settings[<?= e($key) ?>]" 
                                                      class="form-control" 
                                                      rows="3"><?= e($value) ?></textarea>
                                        
                                        <?php elseif ($type === 'checkbox'): ?>
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" 
                                                       type="checkbox" 
                                                       name="settings[<?= e($key) ?>]" 
                                                       id="setting_<?= e($key) ?>" 
                                                       value="1" 
                                                       <?= $value ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="setting_<?= e($key) ?>">
                                                    Включено
                                                </label>
                                            </div>
                                        
                                        <?php elseif ($type === 'select' && !empty($options)): ?>
                                            <select name="settings[<?= e($key) ?>]" class="form-control">
                                                <?php foreach ($options as $optValue => $optLabel): ?>
                                                <option value="<?= e($optValue) ?>" 
                                                        <?= $value == $optValue ? 'selected' : '' ?>>
                                                    <?= e($optLabel) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        
                                        <?php else: ?>
                                            <input type="<?= $type === 'number' ? 'number' : 'text' ?>" 
                                                   name="settings[<?= e($key) ?>]" 
                                                   class="form-control" 
                                                   value="<?= e($value) ?>"
                                                   <?= $type === 'number' ? 'step="any"' : '' ?>>
                                        <?php endif; ?>
                                        
                                        <?php if ($setting['description']): ?>
                                        <small class="form-text text-muted"><?= e($setting['description']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                        </div>
                        <?php $first = false; endforeach; ?>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" name="save_settings" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить настройки
                    </button>
                    <a href="lk_admin.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Назад
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>