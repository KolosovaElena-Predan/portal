<?php
$activePage = 'users';
$pageTitle = 'Пользователи | Админ-панель';
require_once 'includes/auth_check.php';

// Проверка прав: только админ и специалист поддержки
if (!in_array($_SESSION['role'] ?? '', ['admin', 'support_specialist'])) {
    redirect('index.php');
}

// Обработка блокировки/разблокировки
if (isset($_GET['block']) && is_numeric($_GET['block'])) {
    $userId = (int)$_GET['block'];
    $reason = $_GET['reason'] ?? 'Заблокирован администратором';
    $pdo->prepare("UPDATE user SET is_blocked = 1, block_reason = ?, blocked_at = NOW() WHERE id = ?")->execute([$reason, $userId]);
    redirect('users.php?success=blocked');
}

if (isset($_GET['unblock']) && is_numeric($_GET['unblock'])) {
    $userId = (int)$_GET['unblock'];
    $pdo->prepare("UPDATE user SET is_blocked = 0, block_reason = NULL, blocked_at = NULL WHERE id = ?")->execute([$userId]);
    redirect('users.php?success=unblocked');
}

// Получаем пользователей с информацией о блокировке
$users = $pdo->query("SELECT id, email, name, login, role, is_blocked, block_reason, blocked_at FROM user ORDER BY id DESC")->fetchAll();

$pageScript = '$("#usersTable").DataTable({ "responsive": true, "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<style>
.badge-blocked { background: #dc3545; color: white; }
.badge-active { background: #28a745; color: white; }
.blocked-row { background: #fff5f5; }
.btn-block-user { background: #ffc107; color: #212529; border-color: #ffc107; }
.btn-block-user:hover { background: #e0a800; }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Управление пользователями</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_GET['success'])): ?>
                <?php if ($_GET['success'] === 'blocked'): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-check-circle"></i> Пользователь заблокирован
                    </div>
                <?php elseif ($_GET['success'] === 'unblocked'): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-check-circle"></i> Пользователь разблокирован
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Список пользователей</h3></div>
                <div class="card-body">
                    <table id="usersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Имя</th>
                                <th>Login</th>
                                <th>Email</th>
                                <th>Роль</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr class="<?= $u['is_blocked'] ? 'blocked-row' : '' ?>">
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($u['login'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-<?= 
                                        ['admin'=>'danger','support_specialist'=>'warning','client'=>'info','guest'=>'secondary'][$u['role']] ?? 'secondary' 
                                    ?>">
                                        <?= htmlspecialchars($u['role']) ?>
                                    </span>
                                 </td>
                                 <td>
                                    <?php if ($u['is_blocked']): ?>
                                        <span class="badge badge-blocked"><i class="fas fa-ban"></i> Заблокирован</span>
                                        <?php if ($u['block_reason']): ?>
                                            <br><small class="text-muted">Причина: <?= htmlspecialchars($u['block_reason']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-active"><i class="fas fa-check-circle"></i> Активен</span>
                                    <?php endif; ?>
                                 </td>
                                 <td>
                                    <a href="user_edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-primary" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($u['is_blocked']): ?>
                                        <a href="?unblock=<?= $u['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Разблокировать пользователя?')" title="Разблокировать">
                                            <i class="fas fa-unlock"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-block-user" onclick="blockUser(<?= $u['id'] ?>)" title="Заблокировать">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    <?php endif; ?>
                                    <a href="user_delete.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить пользователя?')" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                 </td>
                             </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function blockUser(userId) {
    let reason = prompt('Введите причину блокировки (необязательно):');
    if (reason === null) return;
    window.location.href = '?block=' + userId + '&reason=' + encodeURIComponent(reason);
}
</script>

<?php require_once 'includes/footer.php'; ?>