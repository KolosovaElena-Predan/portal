<?php
$activePage = 'users';
$pageTitle = 'Пользователи | Админ-панель';

require_once 'includes/auth_check.php';

$users = $pdo->query("SELECT id, email, name, login, role FROM user ORDER BY id DESC")->fetchAll();

$pageScript = '$("#usersTable").DataTable({ "responsive": true, "language": {"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"} });';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Управление пользователями</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Список пользователей</h3></div>
                <div class="card-body">
                    <table id="usersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr><th>ID</th><th>Имя</th><th>Login</th><th>Email</th><th>Роль</th><th>Действия</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= e($u['name'] ?? '—') ?></td>
                                <td><?= e($u['login'] ?? '—') ?></td>
                                <td><?= e($u['email'] ?? '—') ?></td>
                                <td><span class="badge badge-<?= ['admin'=>'danger','support_specialist'=>'warning','guest'=>'secondary'][$u['role']] ?? 'info' ?>"><?= e($u['role']) ?></span></td>
                                <td>
                                    <a href="user_delete.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')"><i class="fas fa-trash"></i></a>
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

<?php require_once 'includes/footer.php'; ?>