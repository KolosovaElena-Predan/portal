<?php
$activePage = 'users';
$pageTitle = 'Редактирование пользователя';
require_once 'includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

// 1. Получаем данные пользователя
try {
    $stmt = $pdo->prepare("SELECT id, email, name, login, role FROM user WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) { redirect('users.php'); }
} catch (PDOException $e) { die("Ошибка загрузки: " . $e->getMessage()); }

$currentUserId = $_SESSION['user_id'] ?? 0;
$currentRole   = $_SESSION['role'] ?? '';
$originalRole  = $user['role'];
$isSelf        = ($id === $currentUserId);

// Права доступа
$canEditRole      = in_array($currentRole, ['admin', 'support_specialist'], true);
$canChangePassword = $isSelf || $canEditRole;

// Начальное состояние блокировки (зависит от ТЕКУЩЕЙ роли пользователя)
$lockPersonal = in_array($originalRole, ['guest', 'user', 'client'], true);
$lockAttr     = $lockPersonal ? 'disabled readonly' : '';
$lockClass    = $lockPersonal ? 'bg-light text-muted' : '';
$lockStyle    = $lockPersonal ? 'pointer-events: none; cursor: not-allowed;' : '';

// Блокировка доступа к странице
if (!$canChangePassword && !$canEditRole) { redirect('users.php'); }

// ============================================
// 2. ОБРАБОТКА ФОРМЫ
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $submittedRole   = $_POST['role'] ?? $originalRole;

    $processPersonal = $canEditRole && !in_array($submittedRole, ['guest', 'user', 'client'], true);

    if ($processPersonal) {
        $name  = trim($_POST['name'] ?? '');
        $login = trim($_POST['login'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (!$name) $error = 'Имя обязательно';
        elseif (!$login) $error = 'Login обязателен';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Некорректный Email';
    } else {
        // Игнорируем ввод, берём из БД
        $name  = $user['name'];
        $login = $user['login'];
        $email = $user['email'];
    }

    // Роль
    $role = $canEditRole ? $submittedRole : $originalRole;
    if ($canEditRole && !in_array($role, ['admin', 'support_specialist', 'user', 'guest', 'client'], true)) {
        $error = 'Неверная роль';
    }

    // Пароль
    if ($newPassword) {
        if (!$canChangePassword) {
            $error = 'У вас нет прав для смены пароля';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Пароль должен содержать минимум 6 символов';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Пароли не совпадают';
        }
    }

    // 💾 СОХРАНЕНИЕ
    if (!$error) {
        try {
            $pdo->beginTransaction();
            
            // Формируем параметры СТРОГО в порядке появления ? в запросе
            $params = [$name, $login, $email, $role];
            $sql    = "UPDATE user SET name=?, login=?, email=?, role=?";
            
            if ($newPassword) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql .= ", password=?";
                $params[] = $hash; // Хэш идёт ПЕРЕД ID
            }
            
            $sql .= " WHERE id=?";
            $params[] = $id; // ID всегда последний
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pdo->commit();
            
            $success = 'Данные пользователя успешно обновлены!';
            
            // Обновляем сессию, если редактировали себя
            if ($isSelf) {
                $_SESSION['name']  = $name;
                $_SESSION['login'] = $login;
                $_SESSION['email'] = $email;
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php'; 
require_once 'includes/navbar.php'; 
require_once 'includes/sidebar.php'; 
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">
                Редактирование пользователя №<?= $user['id'] ?>
                <!--<span id="lockBadge" class="badge <?= $lockPersonal ? 'badge-warning' : 'badge-success' ?> ml-2">
                    <i class="fas <?= $lockPersonal ? 'fa-lock' : 'fa-unlock' ?>"></i>
                    <?= $lockPersonal ? 'Личные данные защищены' : 'Редактирование доступно' ?>
                </span>-->
            </h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-exclamation-triangle"></i> <?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle"></i> <?= e($success) ?></div>
            <?php endif; ?>

            <form method="POST" class="card">
                <div class="card-body">
                    <h5 class="mb-3">Личные данные</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Имя</label>
                                <input type="text" name="name" class="form-control personal-field <?= $lockClass ?>" 
                                       value="<?= e($user['name']) ?>" <?= $lockAttr ?> style="<?= $lockStyle ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Login</label>
                                <input type="text" name="login" class="form-control personal-field <?= $lockClass ?>" 
                                       value="<?= e($user['login']) ?>" <?= $lockAttr ?> style="<?= $lockStyle ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control personal-field <?= $lockClass ?>" 
                                       value="<?= e($user['email']) ?>" <?= $lockAttr ?> style="<?= $lockStyle ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Роль</label>
                                <?php if ($canEditRole): ?>
                                <select name="role" id="roleSelect" class="form-control">
                                    <option value="guest" <?= $originalRole === 'guest' ? 'selected' : '' ?>>Guest</option>
                                    <option value="client" <?= $originalRole === 'client' ? 'selected' : '' ?>>Client</option>
                                    <option value="support_specialist" <?= $originalRole === 'support_specialist' ? 'selected' : '' ?>>Support</option>
                                    <option value="admin" <?= $originalRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <!--<small class="text-muted">Выбор Admin/Support разблокирует поля выше</small>-->
                                <?php else: ?>
                                <input type="text" class="form-control" value="<?= e($user['role']) ?>" readonly>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Смена пароля</h5>
                    <?php if ($canChangePassword): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Новый пароль</label>
                                <input type="password" name="new_password" class="form-control" placeholder="••••••••" autocomplete="new-password">
                                <small class="text-muted">Оставьте пустым, чтобы не менять</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Подтвердите пароль</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info"><i class="fas fa-info-circle"></i> Смена пароля доступна только администраторам или самому пользователю.</div>
                    <?php endif; ?>
                </div>

                <div class="card-footer">
                    <button type="submit" name="save" class="btn btn-primary">
                         Сохранить изменения
                    </button>
                    <a href="users.php" class="btn btn-secondary">
                        Назад к списку
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    if (!roleSelect) return;

    const fields = document.querySelectorAll('.personal-field');
    const badge = document.getElementById('lockBadge');
    const restrictedRoles = ['guest', 'user', 'client'];

    function updateLockState(isRestricted) {
        fields.forEach(field => {
            if (isRestricted) {
                field.setAttribute('disabled', true);
                field.setAttribute('readonly', true);
                field.classList.add('bg-light', 'text-muted');
                field.style.pointerEvents = 'none';
                field.style.cursor = 'not-allowed';
            } else {
                field.removeAttribute('disabled');
                field.removeAttribute('readonly');
                field.classList.remove('bg-light', 'text-muted');
                field.style.pointerEvents = '';
                field.style.cursor = '';
            }
        });

        badge.className = isRestricted ? 'badge badge-warning ml-2' : 'badge badge-success ml-2';
        badge.innerHTML = isRestricted 
            ? '<i class="fas fa-lock"></i> Личные данные защищены' 
            : '<i class="fas fa-unlock"></i> Редактирование доступно';
    }

    roleSelect.addEventListener('change', function() {
        const isRestricted = restrictedRoles.includes(this.value);
        updateLockState(isRestricted);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>