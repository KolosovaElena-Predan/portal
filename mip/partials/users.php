<?php
require_once __DIR__ . '/../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    // Добавление
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        $allowedRoles = ['client', 'admin', 'support_specialist', 'guest'];
        if ($name && $email && $login && $password && in_array($role, $allowedRoles)) {
            // Проверка email
            $stmt = $pdo->prepare("SELECT 1 FROM user WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(['error' => 'Email уже используется']);
                exit;
            }

            // Проверка login
            $stmt = $pdo->prepare("SELECT 1 FROM user WHERE login = ?");
            $stmt->execute([$login]);
            if ($stmt->fetch()) {
                echo json_encode(['error' => 'Логин уже используется']);
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO user (name, email, login, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $login, $hash, $role]);

            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['error' => 'Заполните все поля']);
        exit;
    }

    // Удаление
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM user WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['error' => 'Неверный ID']);
        exit;
    }

    // Изменение роли
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $role = $_POST['role'] ?? '';
        $allowedRoles = ['client', 'admin', 'support_specialist', 'guest'];
        if ($id > 0 && in_array($role, $allowedRoles)) {
            $pdo->prepare("UPDATE user SET role = ? WHERE id = ?")->execute([$role, $id]);
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['error' => 'Неверная роль']);
        exit;
    }

    echo json_encode(['error' => 'Неизвестное действие']);
    exit;
}

$users = $pdo->query("SELECT id, name, email, login, role FROM user ORDER BY id")->fetchAll();
?>

<!-- Форма добавления -->
<h2 class="admin-title">Пользователи</h2>

<h3 class="form-title">Добавить пользователя</h3>
<form id="addUserForm" class="form-section">
    <input type="text" name="name" class="input-field" placeholder="Имя *" required><br><br>
    <input type="email" name="email" class="input-field" placeholder="Email *" required><br><br>
    <input type="text" name="login" class="input-field" placeholder="Логин *" required><br><br>
    <input type="password" name="password" class="input-field" placeholder="Пароль *" required><br><br>
    <select name="role" class="input-field" required>
        <option value="" disabled selected>Роль</option>
        <option value="client">Клиент</option>
        <option value="admin">Админ</option>
        <option value="support_specialist">Специалист поддержки</option>
        <option value="guest">Гость</option>
    </select><br><br>
    <button type="submit" class="btn-primary">Добавить пользователя</button>
</form>

<!-- Список -->
<h3 class="form-title">Список пользователей</h3>
<table id="usersTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Email</th>
            <th>Логин</th>
            <th>Роль</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr data-id="<?= (int)$u['id'] ?>">
            <td><?= htmlspecialchars($u['id']) ?></td>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['login'] ?? '—') ?></td>
            <td>
                <select class="role-select" data-original="<?= htmlspecialchars($u['role']) ?>">
                    <option value="client" <?= $u['role'] === 'client' ? 'selected' : '' ?>>Клиент</option>
                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Админ</option>
                    <option value="support_specialist" <?= $u['role'] === 'support_specialist' ? 'selected' : '' ?>>Специалист</option>
                    <option value="guest" <?= $u['role'] === 'guest' ? 'selected' : '' ?>>Гость</option>
                </select>
            </td>
            <td>
                <button class="btn-secondary delete-user" data-id="<?= (int)$u['id'] ?>">Удалить</button>
                <button class="btn-primary save-user-role" data-id="<?= (int)$u['id'] ?>">Сохранить</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>