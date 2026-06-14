<?php
$activePage = 'users';
$pageTitle = 'Редактирование пользователя';
require_once 'includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

// 1. Получаем данные пользователя
try {
    $stmt = $pdo->prepare("SELECT id, email, name, login, role, is_blocked, block_reason, blocked_at FROM user WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) { redirect('users.php'); }
} catch (PDOException $e) { die("Ошибка загрузки: " . $e->getMessage()); }

$currentUserId = $_SESSION['user_id'] ?? 0;
$currentRole   = $_SESSION['role'] ?? '';
$originalRole  = $user['role'];
$isSelf        = ($id === $currentUserId);

// Права доступа
$canEditRole      = in_array($currentRole, ['admin'], true);
$canChangePassword = $isSelf || $canEditRole;
$canBlockUser      = in_array($currentRole, ['admin', 'support_specialist'], true) && !$isSelf;

// Обработка блокировки/разблокировки со страницы редактирования
if (isset($_POST['block_action'])) {
    $action = $_POST['block_action'];
    if ($action === 'block' && $canBlockUser) {
        $reason = trim($_POST['block_reason'] ?? 'Заблокирован администратором');
        $pdo->prepare("UPDATE user SET is_blocked = 1, block_reason = ?, blocked_at = NOW() WHERE id = ?")->execute([$reason, $id]);
        $success = 'Пользователь заблокирован';
        // Обновляем данные
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    } elseif ($action === 'unblock' && $canBlockUser) {
        $pdo->prepare("UPDATE user SET is_blocked = 0, block_reason = NULL, blocked_at = NULL WHERE id = ?")->execute([$id]);
        $success = 'Пользователь разблокирован';
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    }
}

// Начальное состояние блокировки полей
$lockPersonal = in_array($originalRole, ['guest', 'user', 'client'], true);
$lockAttr     = $lockPersonal ? 'disabled readonly' : '';
$lockClass    = $lockPersonal ? 'bg-light text-muted' : '';
$lockStyle    = $lockPersonal ? 'pointer-events: none; cursor: not-allowed;' : '';

// Блокировка доступа к странице
if (!$canChangePassword && !$canEditRole) { redirect('users.php'); }

// ============================================
// 2. ОБРАБОТКА ФОРМЫ СОХРАНЕНИЯ
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
        $name  = $user['name'];
        $login = $user['login'];
        $email = $user['email'];
    }

    // Роль
    $role = $canEditRole ? $submittedRole : $originalRole;
    if ($canEditRole && !in_array($role, ['admin', 'support_specialist', 'client', 'guest'], true)) {
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
            
            $params = [$name, $login, $email, $role];
            $sql = "UPDATE user SET name=?, login=?, email=?, role=?";
            
            if ($newPassword) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql .= ", password=?";
                $params[] = $hash;
            }
            
            $sql .= " WHERE id=?";
            $params[] = $id;
            
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
            
            // Обновляем данные пользователя
            $stmt = $pdo->prepare("SELECT id, email, name, login, role, is_blocked, block_reason, blocked_at FROM user WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
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

<style>
.blocked-info {
    background: #fff5f5;
    border-left: 4px solid #dc3545;
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 8px;
}
.blocked-info i {
    color: #dc3545;
    margin-right: 8px;
}
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">
                Редактирование пользователя №<?= $user['id'] ?>
                <?php if ($user['is_blocked']): ?>
                    <span class="badge badge-danger ml-2"><i class="fas fa-ban"></i> Заблокирован</span>
                <?php else: ?>
                    <span class="badge badge-success ml-2"><i class="fas fa-check-circle"></i> Активен</span>
                <?php endif; ?>
            </h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($user['is_blocked'] && $user['block_reason']): ?>
                <div class="blocked-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Причина блокировки:</strong> <?= htmlspecialchars($user['block_reason']) ?>
                    <?php if ($user['blocked_at']): ?>
                        <br><small>Дата блокировки: <?= date('d.m.Y H:i', strtotime($user['blocked_at'])) ?></small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <form method="POST" class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Личные данные</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Имя</label>
                                        <input type="text" name="name" class="form-control personal-field <?= $lockClass ?>" 
                                               value="<?= htmlspecialchars($user['name']) ?>" <?= $lockAttr ?> style="<?= $lockStyle ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Login</label>
                                        <input type="text" name="login" class="form-control personal-field <?= $lockClass ?>" 
                                               value="<?= htmlspecialchars($user['login']) ?>" <?= $lockAttr ?> style="<?= $lockStyle ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control personal-field <?= $lockClass ?>" 
                                               value="<?= htmlspecialchars($user['email']) ?>" <?= $lockAttr ?> style="<?= $lockStyle ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Роль</label>
                                        <?php if ($canEditRole): ?>
                                        <select name="role" id="roleSelect" class="form-control">
                                            <option value="guest" <?= $originalRole === 'guest' ? 'selected' : '' ?>>Guest</option>
                                            <option value="client" <?= $originalRole === 'client' ? 'selected' : '' ?>>Client</option>
                                            <option value="support_specialist" <?= $originalRole === 'support_specialist' ? 'selected' : '' ?>>Support Specialist</option>
                                            <option value="admin" <?= $originalRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <?php else: ?>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
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
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                            <a href="users.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Назад к списку
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="col-md-4">
                    <!-- Блок управления блокировкой -->
                    <?php if ($canBlockUser): ?>
                    <div class="card">
                        <div class="card-header <?= $user['is_blocked'] ? 'bg-danger' : 'bg-warning' ?> text-white">
                            <h3 class="card-title">
                                <i class="fas <?= $user['is_blocked'] ? 'fa-ban' : 'fa-lock' ?>"></i>
                                Блокировка пользователя
                            </h3>
                        </div>
                        <form method="POST" class="card-body">
                            <?php if ($user['is_blocked']): ?>
                                <p>Пользователь currently заблокирован.</p>
                                <?php if ($user['block_reason']): ?>
                                    <div class="alert alert-danger">
                                        <strong>Причина:</strong> <?= htmlspecialchars($user['block_reason']) ?>
                                    </div>
                                <?php endif; ?>
                                <button type="submit" name="block_action" value="unblock" class="btn btn-success btn-block" onclick="return confirm('Разблокировать пользователя?')">
                                    <i class="fas fa-unlock"></i> Разблокировать
                                </button>
                            <?php else: ?>
                                <p>Заблокируйте пользователя, если он нарушает правила.</p>
                                <div class="form-group">
                                    <label>Причина блокировки</label>
                                    <textarea name="block_reason" class="form-control" rows="3" placeholder="Нарушение правил, спам, мошенничество..."></textarea>
                                </div>
                                <button type="submit" name="block_action" value="block" class="btn btn-block-user btn-block" onclick="return confirm('Заблокировать пользователя?')">
                                    <i class="fas fa-lock"></i> Заблокировать
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    if (!roleSelect) return;

    const fields = document.querySelectorAll('.personal-field');
    const restrictedRoles = ['guest', 'client'];

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
    }

    roleSelect.addEventListener('change', function() {
        const isRestricted = restrictedRoles.includes(this.value);
        updateLockState(isRestricted);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>