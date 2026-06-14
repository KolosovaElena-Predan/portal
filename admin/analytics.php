<?php
$activePage = 'analytics';
$pageTitle = 'Аналитика | Админ-панель';
require_once 'includes/auth_check.php';

// Проверка прав (только админ)
if (!in_array($_SESSION['role'] ?? '', ['admin'])) {
    redirect('lk_admin.php');
}

// ============================================
// ЗАГРУЗКА ДИНАМИЧЕСКИХ СТАТУСОВ
// ============================================
$statusLabels = [];
$statusColors = [];
$statusList = [];
$closedStatusCodes = [];

try {
    $stmtStatuses = $pdo->query("SELECT * FROM request_statuses WHERE is_active = 1 ORDER BY sort_order");
    $statusesList = $stmtStatuses->fetchAll(PDO::FETCH_ASSOC);
    foreach ($statusesList as $s) {
        $statusLabels[$s['code']] = $s['name'];
        $statusColors[$s['code']] = $s['color'];
        $statusList[$s['code']] = $s;
        if ($s['is_closed']) {
            $closedStatusCodes[] = $s['code'];
        }
    }
} catch (PDOException $e) {
    // Стандартные статусы, если таблица не создана
    $statusLabels = ['new' => 'Новый', 'processed' => 'В обработке', 'closed' => 'Закрыт', 'cancelled' => 'Отклонён'];
    $statusColors = ['new' => '#ef4444', 'processed' => '#f59e0b', 'closed' => '#10b981', 'cancelled' => '#6b7280'];
    $closedStatusCodes = ['closed', 'cancelled'];
    foreach ($statusLabels as $code => $name) {
        $statusList[$code] = [
            'code' => $code,
            'name' => $name,
            'color' => $statusColors[$code],
            'is_active' => 1,
            'is_closed' => in_array($code, $closedStatusCodes)
        ];
    }
}

// Строим динамические столбцы для SQL
$statusColumns = [];
foreach ($statusList as $code => $status) {
    $statusColumns[] = "SUM(CASE WHEN status = '{$code}' THEN 1 ELSE 0 END) as {$code}_count";
}
$statusColumnsSql = !empty($statusColumns) ? implode(', ', $statusColumns) : "0 as new_count, 0 as processed_count, 0 as closed_count, 0 as cancelled_count";

$activeTab = $_GET['tab'] ?? 'visits';

// Проверка наличия таблицы visits
$visitsTableExists = false;
try {
    $check = $pdo->query("SHOW TABLES LIKE 'visits'")->fetch();
    if ($check) $visitsTableExists = true;
} catch (Exception $e) {}

$dateFrom = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
$dateTo = $_GET['to'] ?? date('Y-m-d');

$params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];

// Данные о посещениях
$chartData = [];
if ($visitsTableExists) {
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as d, COUNT(*) as c, COUNT(DISTINCT ip_address) as u 
        FROM visits 
        WHERE created_at >= ? AND created_at <= ?
        GROUP BY d ORDER BY d ASC
    ");
    $stmt->execute($params);
    $chartData = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$visitsChartData = [];
foreach ($chartData as $row) {
    $visitsChartData[] = ['d' => $row['d'], 'c' => (int)$row['c'], 'u' => (int)$row['u']];
}

$summary = [];
if ($visitsTableExists) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            COUNT(DISTINCT ip_address) as unique_ips
        FROM visits 
        WHERE created_at >= ? AND created_at <= ?
    ");
    $stmt->execute($params);
    $summary = $stmt->fetch();
}

$topPages = [];
if ($visitsTableExists) {
    $stmt = $pdo->prepare("
        SELECT page_url, COUNT(*) as cnt, COUNT(DISTINCT ip_address) as uniq 
        FROM visits 
        WHERE created_at >= ? AND created_at <= ?
        GROUP BY page_url ORDER BY cnt DESC LIMIT 10
    ");
    $stmt->execute($params);
    $topPages = $stmt->fetchAll();
}

// Данные о заявках с динамическими статусами
$requestsParams = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];

// Общая статистика по заявкам
$requestsSummary = [];
$sql = "
    SELECT 
        COUNT(*) as total,
        {$statusColumnsSql},
        SUM(CASE WHEN type = 'q' THEN 1 ELSE 0 END) as questions,
        SUM(CASE WHEN type = 'r' THEN 1 ELSE 0 END) as orders,
        SUM(CASE WHEN type = 's' THEN 1 ELSE 0 END) as services
    FROM request 
    WHERE datetime >= ? AND datetime <= ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute($requestsParams);
$requestsSummary = $stmt->fetch();

// Заявки по дням для графика (с динамическими статусами)
$requestsChart = [];
$dynamicStatusColumns = [];
foreach ($statusList as $code => $status) {
    $dynamicStatusColumns[] = "SUM(CASE WHEN status = '{$code}' THEN 1 ELSE 0 END) as {$code}_count";
}
$statusColumnsGraph = implode(', ', $dynamicStatusColumns);

$sql = "
    SELECT 
        DATE(datetime) as d, 
        COUNT(*) as total,
        {$statusColumnsGraph}
    FROM request 
    WHERE datetime >= ? AND datetime <= ?
    GROUP BY d ORDER BY d ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($requestsParams);
$requestsChart = $stmt->fetchAll();

// Подготовка данных для графика
$requestsChartData = [];
foreach ($requestsChart as $row) {
    $item = ['d' => $row['d'], 'total' => (int)$row['total']];
    foreach ($statusList as $code => $status) {
        $item[$code . '_count'] = (int)($row[$code . '_count'] ?? 0);
    }
    $requestsChartData[] = $item;
}

// Круговая диаграмма статусов
$statusDistributionLabels = [];
$statusDistributionValues = [];
$statusDistributionColors = [];
foreach ($statusList as $code => $status) {
    $count = (int)($requestsSummary["{$code}_count"] ?? 0);
    if ($count > 0) {
        $statusDistributionLabels[] = $status['name'];
        $statusDistributionValues[] = $count;
        $statusDistributionColors[] = $status['color'];
    }
}
// Если нет данных, показываем пустые массивы
if (empty($statusDistributionLabels)) {
    $statusDistributionLabels = ['Нет данных'];
    $statusDistributionValues = [1];
    $statusDistributionColors = ['#e2e8f0'];
}

// ТОП пользователей по заявкам
$topUsers = [];
$stmt = $pdo->prepare("
    SELECT 
        u.name as user_name, 
        u.email as user_email, 
        COUNT(r.id) as request_count
    FROM request r
    LEFT JOIN user u ON r.user_id = u.id
    WHERE r.datetime >= ? AND r.datetime <= ?
    GROUP BY r.user_id
    ORDER BY request_count DESC
    LIMIT 10
");
$stmt->execute($requestsParams);
$topUsers = $stmt->fetchAll();

// Экспорт таблицы
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $activeTab === 'visits' && $visitsTableExists) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=visits_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");
    
    fputcsv($output, ['Дата', 'Время', 'IP', 'Страница', 'Авторизован'], ';', '"');
    
    $stmt = $pdo->prepare("
        SELECT created_at, ip_address, page_url, user_id
        FROM visits 
        WHERE created_at >= ? AND created_at <= ?
        ORDER BY created_at DESC 
        LIMIT 5000
    ");
    $stmt->execute($params);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            date('d.m.Y', strtotime($row['created_at'])),
            date('H:i', strtotime($row['created_at'])),
            $row['ip_address'],
            $row['page_url'],
            $row['user_id'] ? 'Да' : 'Нет'
        ], ';', '"');
    }
    fclose($output);
    exit;
}

// Экспорт заявок 
if (isset($_GET['export']) && $_GET['export'] === 'csv_requests' && $activeTab === 'requests') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=requests_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");
    
    fputcsv($output, ['ID', 'Пользователь', 'Email', 'Тип', 'Сообщение', 'Статус', 'Дата', 'Продукт'], ';', '"');
    
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as user_name, u.email as user_email, p.name as product_name 
        FROM request r 
        LEFT JOIN user u ON r.user_id = u.id 
        LEFT JOIN products p ON r.product_id = p.id
        WHERE r.datetime >= ? AND r.datetime <= ?
        ORDER BY r.datetime DESC 
        LIMIT 5000
    ");
    $stmt->execute($requestsParams);
    
    $typeMap = ['q' => 'Вопрос', 'r' => 'Заказ', 's' => 'Услуга'];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['user_name'] ?? 'Гость',
            $row['user_email'] ?? '—',
            $typeMap[$row['type']] ?? $row['type'],
            mb_substr($row['message'], 0, 200),
            $statusLabels[$row['status']] ?? $row['status'],
            date('d.m.Y H:i', strtotime($row['datetime'])),
            $row['product_name'] ?? '—'
        ], ';', '"');
    }
    fclose($output);
    exit;
}

// Подготовка JSON для JavaScript
$statusListJson = json_encode($statusList, JSON_UNESCAPED_UNICODE);
$statusColorsJson = json_encode($statusColors, JSON_UNESCAPED_UNICODE);
$requestsChartDataJson = json_encode($requestsChartData);
$visitsChartDataJson = json_encode($visitsChartData);
$statusDistributionJson = json_encode([
    'labels' => $statusDistributionLabels,
    'values' => $statusDistributionValues,
    'colors' => $statusDistributionColors
]);

$pageScript = '
// Объявляем глобальные переменные для данных графиков
window.statusList = ' . $statusListJson . ';
window.statusColors = ' . $statusColorsJson . ';
window.visitsChartData = ' . $visitsChartDataJson . ';
const requestsChartData = ' . $requestsChartDataJson . ';
const statusDistribution = ' . $statusDistributionJson . ';

const chartScript = document.createElement("script");
chartScript.src = "https://cdn.jsdelivr.net/npm/chart.js";
document.head.appendChild(chartScript);

chartScript.onload = function() {
    // График посещений
    const visitsCtx = document.getElementById("visitsLineChart");
    if (visitsCtx && window.visitsChartData && window.visitsChartData.length > 0) {
        new Chart(visitsCtx, {
            type: "line",
            data: {
                labels: window.visitsChartData.map(item => item.d),
                datasets: [
                    {
                        label: "Всего посещений",
                        data: window.visitsChartData.map(item => item.c),
                        borderColor: "#2563eb",
                        backgroundColor: "rgba(37,99,235,0.1)",
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: "#2563eb"
                    },
                    {
                        label: "Уникальные",
                        data: window.visitsChartData.map(item => item.u),
                        borderColor: "#10b981",
                        borderDash: [6, 4],
                        fill: false,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: "#10b981"
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: "top",
                        labels: { padding: 20, usePointStyle: true, font: { size: 12 } }
                    },
                    tooltip: {
                        mode: "index",
                        intersect: false
                    }
                },
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1, font: { size: 11 } },
                        grid: { color: "rgba(0,0,0,0.04)" }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    }

    // График заявок (динамические статусы)
    const requestsCtx = document.getElementById("requestsLineChart");
    if (requestsCtx && requestsChartData && requestsChartData.length > 0) {
        const datasets = [
            {
                label: "Всего заявок",
                data: requestsChartData.map(item => item.total),
                borderColor: "#2563eb",
                backgroundColor: "rgba(37,99,235,0.05)",
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 3
            }
        ];
        
        const statusList = window.statusList || {};
        const statusColors = window.statusColors || {};
        
        Object.keys(statusList).forEach(function(code) {
            if (statusList[code] && statusList[code].is_active) {
                const statusData = requestsChartData.map(item => item[code + "_count"] || 0);
                const hasData = statusData.some(v => v > 0);
                if (hasData) {
                    datasets.push({
                        label: statusList[code].name,
                        data: statusData,
                        borderColor: statusColors[code] || "#6b7280",
                        borderDash: [6, 4],
                        fill: false,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3
                    });
                }
            }
        });
        
        new Chart(requestsCtx, {
            type: "line",
            data: {
                labels: requestsChartData.map(item => item.d),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: "top",
                        labels: { 
                            padding: 20, 
                            usePointStyle: true, 
                            font: { size: 12 },
                            filter: function(legendItem, data) {
                                return legendItem.index < 8;
                            }
                        } 
                    },
                    tooltip: {
                        mode: "index",
                        intersect: false
                    }
                },
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1, font: { size: 11 } },
                        grid: { color: "rgba(0,0,0,0.04)" }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    } else if (requestsCtx) {
        // Показываем сообщение, если нет данных
        const ctx = requestsCtx.getContext("2d");
        ctx.font = "14px Arial";
        ctx.fillStyle = "#94a3b8";
        ctx.textAlign = "center";
        ctx.fillText("Нет данных за выбранный период", requestsCtx.width / 2, requestsCtx.height / 2);
    }

    // Круговая диаграмма статусов
    const statusPieCtx = document.getElementById("statusPieChart");
    if (statusPieCtx && statusDistribution && statusDistribution.labels && statusDistribution.labels.length > 0) {
        new Chart(statusPieCtx, {
            type: "doughnut",
            data: {
                labels: statusDistribution.labels,
                datasets: [{
                    data: statusDistribution.values,
                    backgroundColor: statusDistribution.colors,
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: { padding: 15, font: { size: 12 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || "";
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
};
';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<style>
    .content-wrapper {
        background: #f8fafc;
        min-height: 100vh;
    }
    
    .content-header h1 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .analytics-tabs {
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
        display: flex;
        gap: 0.5rem;
    }
    .analytics-tabs .tab-link {
        padding: 0.75rem 1.5rem;
        font-size: 0.95rem;
        font-weight: 500;
        color: #64748b;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .analytics-tabs .tab-link:hover {
        color: #334155;
        border-bottom-color: #cbd5e1;
    }
    .analytics-tabs .tab-link.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
    }
    
    .card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        margin-bottom: 1.25rem;
    }
    
    .card-header {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.25rem;
        border-radius: 12px 12px 0 0;
    }
    
    .card-header h3.card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #334155;
        margin: 0;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    .stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .stat-card .value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
    }
    .stat-card .label {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
    }
    
    .filter-card .card-body {
        padding: 1rem 1.25rem;
    }
    .filter-card label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #475569;
        margin-bottom: 0.35rem;
        display: block;
    }
    .filter-card .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }
    .filter-card .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        outline: none;
    }
    
    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    .btn-primary {
        background: #2563eb;
        border-color: #2563eb;
    }
    .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }
    .btn-success {
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }
    .btn-success:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #1e293b;
    }
    
    .table {
        margin: 0;
    }
    .table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 600;
        font-size: 0.8rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 0.75rem 1rem;
    }
    .table tbody td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        color: #334155;
        vertical-align: middle;
    }
    .table tbody tr:hover {
        background: #f8fafc;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
        font-size: 0.8rem;
    }
    
    .chart-container {
        position: relative;
        height: 350px;
        width: 100%;
    }
    
    .pie-chart-container {
        position: relative;
        height: 250px;
        width: 100%;
        max-width: 350px;
        margin: 0 auto;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #94a3b8;
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.6;
    }
    
    /* Динамические цвета для бейджей статусов */
    .badge-status {
        display: inline-block;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .filter-card .row > div {
            margin-bottom: 0.75rem;
        }
        .stat-card {
            margin-bottom: 0.75rem;
        }
    }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Статистика</h1>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <div class="analytics-tabs">
                <a href="?tab=visits&from=<?= urlencode($dateFrom) ?>&to=<?= urlencode($dateTo) ?>" 
                   class="tab-link <?= $activeTab === 'visits' ? 'active' : '' ?>">
                    Посещения
                </a>
                <a href="?tab=requests&from=<?= urlencode($dateFrom) ?>&to=<?= urlencode($dateTo) ?>" 
                   class="tab-link <?= $activeTab === 'requests' ? 'active' : '' ?>">
                    Заявки
                </a>
            </div>
            
            <div class="card filter-card">
                <div class="card-header">
                    <h3 class="card-title">Период</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row align-items-end g-3">
                        <input type="hidden" name="tab" value="<?= e($activeTab) ?>">
                        <div class="col-md-4">
                            <label>С даты</label>
                            <input type="date" name="from" class="form-control" value="<?= e($dateFrom) ?>">
                        </div>
                        <div class="col-md-4">
                            <label>По дату</label>
                            <input type="date" name="to" class="form-control" value="<?= e($dateTo) ?>">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                Применить
                            </button>
                            <?php if ($activeTab === 'visits' && $visitsTableExists): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv', 'tab' => 'visits'])) ?>" class="btn btn-success">
                                Экспорт
                            </a>
                            <?php elseif ($activeTab === 'requests'): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv_requests', 'tab' => 'requests'])) ?>" class="btn btn-success">
                                Экспорт
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if ($activeTab === 'visits'): ?>
            
            <?php if ($visitsTableExists && $summary): ?>
            <div class="row mb-4">
                <div class="col-lg-6 col-6">
                    <div class="stat-card">
                        <div class="value"><?= number_format($summary['total'] ?? 0) ?></div>
                        <div class="label">Всего посещений</div>
                    </div>
                </div>
                <div class="col-lg-6 col-6">
                    <div class="stat-card">
                        <div class="value"><?= number_format($summary['unique_ips'] ?? 0) ?></div>
                        <div class="label">Уникальных посетителей</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Посещения по дням</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="chart-container">
                                <?php if ($visitsTableExists && !empty($chartData)): ?>
                                    <canvas id="visitsLineChart"></canvas>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-chart-line"></i>
                                        <p>Нет данных за выбранный период</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Популярные страницы</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr><th>Страница</th><th class="text-center">Посещения</th><th class="text-center">Уникальные</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topPages as $p): ?>
                                        <tr>
                                            <td><code class="text-muted" style="font-size: 0.85rem;"><?= e($p['page_url']) ?></code></td>
                                            <td class="text-center"><span class="badge" style="background: #dbeafe; color: #1e40af;"><?= $p['cnt'] ?></span></td>
                                            <td class="text-center"><span class="badge" style="background: #dcfce7; color: #166534;"><?= $p['uniq'] ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($topPages)): ?>
                                        <tr><td colspan="3" class="text-center text-muted py-4">Нет数据</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php else: // Заявки ?>
            
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="stat-card">
                        <div class="value"><?= number_format($requestsSummary['total'] ?? 0) ?></div>
                        <div class="label">Всего заявок</div>
                    </div>
                </div>
                <?php foreach ($statusList as $code => $status): ?>
                <div class="col-lg-3 col-6">
                    <div class="stat-card" style="border-left: 3px solid <?= $status['color'] ?>;">
                        <div class="value"><?= number_format($requestsSummary["{$code}_count"] ?? 0) ?></div>
                        <div class="label"><?= $status['name'] ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="row mb-4">
                <div class="col-lg-4 col-6">
                    <div class="stat-card" style="background: #eff6ff;">
                        <div class="value" style="color: #2563eb;"><?= number_format($requestsSummary['questions'] ?? 0) ?></div>
                        <div class="label">Вопросы</div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="stat-card" style="background: #fef3c7;">
                        <div class="value" style="color: #d97706;"><?= number_format($requestsSummary['orders'] ?? 0) ?></div>
                        <div class="label">Заказы</div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="stat-card" style="background: #ecfdf5;">
                        <div class="value" style="color: #059669;"><?= number_format($requestsSummary['services'] ?? 0) ?></div>
                        <div class="label">Услуги</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Динамика заявок по дням</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="chart-container">
                                <?php if (!empty($requestsChart)): ?>
                                    <canvas id="requestsLineChart"></canvas>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-chart-line"></i>
                                        <p>Нет данных за выбранный период</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Распределение по статусам</h3>
                        </div>
                        <div class="card-body">
                            <div class="pie-chart-container">
                                <canvas id="statusPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Активные пользователи</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr><th>Пользователь</th><th>Email</th><th class="text-center">Кол-во заявок</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($topUsers)): ?>
                                            <?php foreach ($topUsers as $u): ?>
                                            <tr>
                                                <td><strong><?= e($u['user_name'] ?? 'Гость') ?></strong></td>
                                                <td><?= e($u['user_email'] ?? '—') ?></td>
                                                <td class="text-center"><span class="badge" style="background: #dbeafe; color: #1e40af;"><?= $u['request_count'] ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                        <tr><td colspan="3" class="text-center text-muted py-4">Нет данных</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php endif; ?>
            
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>