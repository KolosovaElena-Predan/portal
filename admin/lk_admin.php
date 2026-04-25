<?php
$activePage = 'dashboard';
$pageTitle = 'Главная | Админ-панель';
require_once 'includes/auth_check.php';

// ============================================
// 1. СТАТИСТИКА (Счетчики)
// ============================================
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn(),
    'requests' => $pdo->query("SELECT COUNT(*) FROM request WHERE status = 'new'")->fetchColumn(),
    'products' => $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
    'services' => $pdo->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn(),
];

// ============================================
// 2. ДАННЫЕ ДЛЯ ГРАФИКОВ
// ============================================

// А) Статусы заявок (для круговой диаграммы)
$reqStmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM request GROUP BY status");
$reqData = $reqStmt->fetchAll(PDO::FETCH_KEY_PAIR);
// 🔹 Убрали жёсткие цвета, используем стандартную палитру
$statusColors = ['#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff', '#ff6384', '#c9cbcf'];
$reqLabels = []; $reqValues = []; $reqBgColors = [];
$colorIdx = 0;
foreach ($reqData as $status => $count) {
    $reqLabels[] = ['new' => 'Новые', 'processed' => 'В работе', 'closed' => 'Закрыты', 'cancelled' => 'Отменены'][$status] ?? $status;
    $reqValues[] = (int)$count;
    $reqBgColors[] = $statusColors[$colorIdx % count($statusColors)];
    $colorIdx++;
}

// Б) Посещения (линейный график за 7 дней)
$visitsTableExists = false;
try {
    $check = $pdo->query("SHOW TABLES LIKE 'visits'")->fetch();
    if ($check) $visitsTableExists = true;
} catch (Exception $e) { /* игнорируем */ }

$visitsDates = []; $visitsCounts = [];
if ($visitsTableExists) {
    try {
        $stmt = $pdo->query("SELECT DATE(created_at) as d, COUNT(*) as c FROM visits WHERE created_at >= NOW() - INTERVAL 7 DAY GROUP BY d ORDER BY d ASC");
        $vData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($vData as $date => $count) {
            $visitsDates[] = date('d.m', strtotime($date));
            $visitsCounts[] = (int)$count;
        }
    } catch (Exception $e) { /* Если ошибка в таблице */ }
}

// В) Заявки по дням (столбчатая диаграмма за 30 дней)
$orderChartData = $pdo->query("
    SELECT DATE(datetime) as d, 
           COUNT(*) as total,
           SUM(CASE WHEN type='r' THEN 1 ELSE 0 END) as products,
           SUM(CASE WHEN type='s' THEN 1 ELSE 0 END) as services
    FROM request 
    WHERE datetime >= NOW() - INTERVAL 30 DAY
    GROUP BY d ORDER BY d ASC
")->fetchAll(PDO::FETCH_ASSOC);

$orderDates = array_column($orderChartData, 'd');
$orderProducts = array_column($orderChartData, 'products');
$orderServices = array_column($orderChartData, 'services');

// ============================================
// 3. СКРИПТЫ (Chart.js)
// ============================================
$pageScript = '
const chartScript = document.createElement("script");
chartScript.src = "https://cdn.jsdelivr.net/npm/chart.js";
document.head.appendChild(chartScript);

chartScript.onload = function() {
    // 1. График статусов заявок (Doughnut)
    new Chart(document.getElementById("requestsChart"), {
        type: "doughnut",
        data: {
            labels: ' . json_encode($reqLabels) . ',
            datasets: [{
                data: ' . json_encode($reqValues) . ',
                backgroundColor: ' . json_encode($reqBgColors) . ',
                borderWidth: 1,
                borderColor: "#fff"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "70%",
            plugins: {
                legend: {
                    position: "bottom",
                    labels: { padding: 15, usePointStyle: true }
                }
            }
        }
    });

    // 2. График посещений (Line)
    if(document.getElementById("visitsChart")) {
        new Chart(document.getElementById("visitsChart"), {
            type: "line",
            data: {
                labels: ' . json_encode($visitsDates) . ',
                datasets: [{
                    label: "Посещения",
                    data: ' . json_encode($visitsCounts) . ',
                    borderColor: "#36a2eb",
                    backgroundColor: "rgba(54,162,235,0.1)",
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 3
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: "rgba(0,0,0,0.05)" } }, x: { grid: { display: false } } }
            }
        });
    }

    // 3. График заказов (Bar)
    new Chart(document.getElementById("ordersChart"), {
        type: "bar",
        data: {
            labels: ' . json_encode($orderDates) . ',
            datasets: [
                {
                    label: "Товары",
                    data: ' . json_encode($orderProducts) . ',
                    backgroundColor: "rgba(54,162,235,0.6)",
                    borderColor: "#36a2eb",
                    borderWidth: 1
                },
                {
                    label: "Услуги",
                    data: ' . json_encode($orderServices) . ',
                    backgroundColor: "rgba(75,192,192,0.6)",
                    borderColor: "#4bc0c0",
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: "rgba(0,0,0,0.05)" } },
                x: { ticks: { maxRotation: 45, minRotation: 45 }, grid: { display: false } }
            },
            plugins: {
                legend: { position: "top" }
            }
        }
    });
};
';

require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/sidebar.php';
?>

<!-- 🔹 Дополнительные стили для нейтрального дизайна -->
<style>
    /* Нейтральные карточки статистики */
    .small-box {
        background: #fff !important;
        color: #333 !important;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .small-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .small-box .inner {
        padding: 15px;
    }
    .small-box h3 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1982; /* Акцентный цвет для цифр */
        margin: 0 0 5px 0;
    }
    .small-box p {
        font-size: 1rem;
        color: #6c757d;
        margin: 0;
        font-weight: 500;
    }
    .small-box .small-box-footer {
        background: rgba(0,0,0,0.03) !important;
        color: #495057 !important;
        padding: 8px 15px;
        text-align: right;
        font-size: 0.85rem;
    }
    .small-box .small-box-footer:hover {
        background: rgba(0,0,0,0.06) !important;
        color: #1a1982 !important;
    }
    
    /* Карточки графиков */
    .card {
        border: 1px solid #dee2e6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .card-header {
        background: #fff;
        border-bottom: 1px solid #dee2e6;
        padding: 12px 1.25rem;
    }
    .card-header h3.card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Панель управления</h1>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <!-- Счетчики (нейтральный дизайн) -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box">
                        <div class="inner">
                            <h3><?= $stats['users'] ?></h3>
                            <p>Пользователей</p>
                        </div>
                        <a href="users.php" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box">
                        <div class="inner">
                            <h3><?= $stats['requests'] ?></h3>
                            <p>Новых заявок</p>
                        </div>
                        <a href="requests.php" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box">
                        <div class="inner">
                            <h3><?= $stats['products'] ?></h3>
                            <p>Товаров</p>
                        </div>
                        <a href="products.php" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box">
                        <div class="inner">
                            <h3><?= $stats['services'] ?></h3>
                            <p>Услуг</p>
                        </div>
                        <a href="services.php" class="small-box-footer">Подробнее <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Графики: Заявки и Посещения -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Статусы заявок</h3>
                        </div>
                        <div class="card-body" style="height: 300px;">
                            <canvas id="requestsChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Посещения (7 дней)</h3>
                        </div>
                        <div class="card-body" style="height: 300px;">
                            <?php if ($visitsTableExists): ?>
                                <canvas id="visitsChart"></canvas>
                            <?php else: ?>
                                <div class="text-center text-muted pt-5">
                                    <i class="fas fa-chart-line fa-3x mb-3"></i><br>
                                    График посещений недоступен.<br>
                                    <small>Выполните SQL-запрос для создания таблицы <code>visits</code>.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- График заказов -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Заявки за 30 дней</h3>
                        </div>
                        <div class="card-body" style="height: 350px;">
                            <canvas id="ordersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>