<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    // Обновление статуса
    if ($action === 'update_status') {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['new', 'processed', 'closed']) && $id > 0) {
            $pdo->prepare("UPDATE request SET status = ? WHERE id = ?")->execute([$status, $id]);
            echo json_encode(['success' => true]);
            exit;
        }
    }

    // Назначение специалиста поддержки
    if ($action === 'assign_support') {
        $request_id = (int)($_POST['request_id'] ?? 0);
        $support_id = (int)($_POST['support_id'] ?? 0);

        if ($request_id <= 0) {
            echo json_encode(['error' => 'Неверный ID заявки']);
            exit;
        }

        // Снять назначение
        if ($support_id === 0) {
            $pdo->prepare("UPDATE request SET user_clientsupport_id = NULL WHERE id = ?")
                ->execute([$request_id]);
            echo json_encode(['success' => true]);
            exit;
        }

        // Проверяем, что support_id — это специалист поддержки
        $stmt = $pdo->prepare("SELECT id FROM user WHERE id = ? AND role = 'support_specialist'");
        $stmt->execute([$support_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Пользователь не является специалистом поддержки']);
            exit;
        }

        // Получаем client_id из заявки
        $stmt = $pdo->prepare("SELECT user_id FROM request WHERE id = ?");
        $stmt->execute([$request_id]);
        $client_id = (int)$stmt->fetchColumn();
        if (!$client_id) {
            echo json_encode(['error' => 'Заявка не найдена']);
            exit;
        }

      
        $pdo->prepare("DELETE FROM clientsupport WHERE client_id = ?")->execute([$client_id]);

   
        $pdo->prepare("INSERT INTO clientsupport (client_id, support_id) VALUES (?, ?)")
            ->execute([$client_id, $support_id]);
        $clientsupport_id = $pdo->lastInsertId();

     
        $pdo->prepare("UPDATE request SET user_clientsupport_id = ? WHERE id = ?")
            ->execute([$clientsupport_id, $request_id]);

        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Неверные данные']);
    exit;
}

$stmt = $pdo->query("
    SELECT 
        r.id, r.user_id, r.message, r.status, r.type, r.datetime,
        u.name AS user_name,
        dt.name AS device_type_name,
        r.user_clientsupport_id,
        su.name AS support_name
    FROM request r
    LEFT JOIN user u ON r.user_id = u.id
    LEFT JOIN device_type dt ON r.device_type_id = dt.id
    LEFT JOIN clientsupport cs ON r.user_clientsupport_id = cs.id
    LEFT JOIN user su ON cs.support_id = su.id
    ORDER BY r.datetime DESC
");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Все специалисты поддержки
$supports = $pdo->query("
    SELECT id, name FROM user WHERE role = 'support_specialist'
")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="admin-title">Запросы</h2>

<h3 class="form-title">Список запросов</h3>
<table id="requestsTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Оборудование</th>
            <th>Сообщение</th>
            <th>Статус</th>
            <th>Специалист</th>
            <th>Дата</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requests as $r): ?>
        <tr data-id="<?= (int)$r['id'] ?>">
            <td><?= htmlspecialchars($r['id']) ?></td>
            <td><?= htmlspecialchars($r['user_name']) ?></td>
            <td><?= htmlspecialchars($r['device_type_name']) ?></td>
            <td class="request-message"><?= htmlspecialchars($r['message']) ?></td>
            <td>
                <select class="status-select" data-id="<?= (int)$r['id'] ?>">
                    <option value="new" <?= $r['status'] === 'new' ? 'selected' : '' ?>>Новый</option>
                    <option value="processed" <?= $r['status'] === 'processed' ? 'selected' : '' ?>>В работе</option>
                    <option value="closed" <?= $r['status'] === 'closed' ? 'selected' : '' ?>>Закрыт</option>
                </select>
            </td>
            <td>
                <select class="support-select" data-request-id="<?= (int)$r['id'] ?>">
                    <option value="0" <?= empty($r['user_clientsupport_id']) ? 'selected' : '' ?>>Не назначен</option>
                    <?php foreach ($supports as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= !empty($r['support_name']) && $r['support_name'] === $s['name'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><?= htmlspecialchars($r['datetime']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>