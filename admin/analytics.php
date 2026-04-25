<?php
$activePage = 'analytics';
$pageTitle = 'Аналитика | Админ-панель';
require_once 'includes/auth_check.php';

// Проверка прав (только админ)
if (!in_array($_SESSION['role'] ?? '', ['admin'])) {
    redirect('lk_admin.php');
}

$activeTab = $_GET['tab'] ?? 'visits';

// Проверка наличия таблицы visits
$visitsTableExists = false;
try {
    $check = $pdo->query("SHOW TABLES LIKE 'visits'")->fetch();
    if ($check) $visitsTableExists = true;
} catch (Exception $e) {}

// Фильтры
$dateFrom = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['to'] ?? date('Y-m-d');
$requestStatusFilter = $_GET['request_status'] ?? '';
$requestTypeFilter = $_GET['request_type'] ?? '';

// Параметры для запроса
$params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];

// ============================================
// ДАННЫЕ ДЛЯ ПОСЕЩЕНИЙ
// ============================================
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

// ============================================
// ДАННЫЕ ДЛЯ ЗАЯВОК (с фильтрами)
// ============================================
$requestsParams = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
$requestsWhere = "WHERE r.datetime >= ? AND r.datetime <= ?";
$requestsFilterParams = $requestsParams;

if ($requestStatusFilter) {
    $requestsWhere .= " AND r.status = ?";
    $requestsFilterParams[] = $requestStatusFilter;
}
if ($requestTypeFilter) {
    $requestsWhere .= " AND r.type = ?";
    $requestsFilterParams[] = $requestTypeFilter;
}

// Общая статистика по заявкам
$requestsSummary = [];
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN r.status = 'new' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN r.status = 'processed' THEN 1 ELSE 0 END) as processed_count,
        SUM(CASE WHEN r.status = 'closed' THEN 1 ELSE 0 END) as closed_count,
        SUM(CASE WHEN r.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
        SUM(CASE WHEN r.type = 'q' THEN 1 ELSE 0 END) as questions,
        SUM(CASE WHEN r.type = 'r' THEN 1 ELSE 0 END) as orders,
        SUM(CASE WHEN r.type = 's' THEN 1 ELSE 0 END) as services
    FROM request r
    $requestsWhere
");
$stmt->execute($requestsFilterParams);
$requestsSummary = $stmt->fetch();

// Заявки по дням (для графика)
$requestsChart = [];
$stmt = $pdo->prepare("
    SELECT 
        DATE(r.datetime) as d, 
        COUNT(*) as total,
        SUM(CASE WHEN r.status = 'new' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN r.status = 'processed' THEN 1 ELSE 0 END) as processed_count,
        SUM(CASE WHEN r.status = 'closed' THEN 1 ELSE 0 END) as closed_count,
        SUM(CASE WHEN r.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
    FROM request r
    $requestsWhere
    GROUP BY d ORDER BY d ASC
");
$stmt->execute($requestsFilterParams);
$requestsChart = $stmt->fetchAll();

// Статусное распределение
$statusDistribution = [
    'Новые' => (int)($requestsSummary['new_count'] ?? 0),
    'В работе' => (int)($requestsSummary['processed_count'] ?? 0),
    'Закрытые' => (int)($requestsSummary['closed_count'] ?? 0),
    'Отклонённые' => (int)($requestsSummary['cancelled_count'] ?? 0)
];

// ТОП пользователей по заявкам
$topUsers = [];
$stmt = $pdo->prepare("
    SELECT 
        u.name as user_name, 
        u.email as user_email, 
        COUNT(r.id) as request_count
    FROM request r
    LEFT JOIN user u ON r.user_id = u.id
    $requestsWhere
    GROUP BY r.user_id
    ORDER BY request_count DESC
    LIMIT 10
");
$stmt->execute($requestsFilterParams);
$topUsers = $stmt->fetchAll();

// Список заявок для экспорта
$exportRequests = [];
$stmt = $pdo->prepare("
    SELECT 
        r.*, 
        u.name as user_name, 
        u.email as user_email, 
        p.name as product_name,
        c.name as client_support_name
    FROM request r 
    LEFT JOIN user u ON r.user_id = u.id 
    LEFT JOIN products p ON r.product_id = p.id
    LEFT JOIN clientsupport c ON r.user_clientsupport_id = c.id
    $requestsWhere
    ORDER BY r.datetime DESC
");
$stmt->execute($requestsFilterParams);
$exportRequests = $stmt->fetchAll();

// ============================================
// ЭКСПОРТ В EXCEL (XLSX)
// ============================================
if (isset($_GET['export']) && $_GET['export'] === 'excel' && $activeTab === 'requests') {
    require_once 'vendor/autoload.php'; // Подключаем PhpSpreadsheet
    
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Fill;
    use PhpOffice\PhpSpreadsheet\Style\Color;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;
    use PhpOffice\PhpSpreadsheet\Style\Border;
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Заголовок отчета
    $sheet->setTitle('Заявки');
    
    // Информация о фильтрах
    $row = 1;
    $sheet->setCellValue('A' . $row, 'ОТЧЕТ ПО ЗАЯВКАМ');
    $sheet->mergeCells('A' . $row . ':J' . $row);
    $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;
    
    $sheet->setCellValue('A' . $row, 'Период:');
    $sheet->setCellValue('B' . $row, date('d.m.Y', strtotime($dateFrom)) . ' - ' . date('d.m.Y', strtotime($dateTo)));
    $sheet->mergeCells('B' . $row . ':J' . $row);
    $row++;
    
    if ($requestStatusFilter) {
        $statusMap = ['new' => 'Новые', 'processed' => 'В работе', 'closed' => 'Закрытые', 'cancelled' => 'Отклонённые'];
        $sheet->setCellValue('A' . $row, 'Статус:');
        $sheet->setCellValue('B' . $row, $statusMap[$requestStatusFilter] ?? $requestStatusFilter);
        $sheet->mergeCells('B' . $row . ':J' . $row);
        $row++;
    }
    
    if ($requestTypeFilter) {
        $typeMap = ['q' => 'Вопросы', 'r' => 'Заказы', 's' => 'Услуги'];
        $sheet->setCellValue('A' . $row, 'Тип:');
        $sheet->setCellValue('B' . $row, $typeMap[$requestTypeFilter] ?? $requestTypeFilter);
        $sheet->mergeCells('B' . $row . ':J' . $row);
        $row++;
    }
    
    $sheet->setCellValue('A' . $row, 'Дата выгрузки:');
    $sheet->setCellValue('B' . $row, date('d.m.Y H:i:s'));
    $sheet->mergeCells('B' . $row . ':J' . $row);
    $row += 2;
    
    // Заголовки таблицы
    $headers = [
        'A' => 'ID',
        'B' => 'Дата и время',
        'C' => 'Пользователь',
        'D' => 'Email',
        'E' => 'Тип',
        'F' => 'Статус',
        'G' => 'Продукт/Услуга',
        'H' => 'Сообщение',
        'I' => 'Обработчик',
        'J' => 'Координаты'
    ];
    
    foreach ($headers as $col => $header) {
        $sheet->setCellValue($col . $row, $header);
        $sheet->getStyle($col . $row)->getFont()->setBold(true);
        $sheet->getStyle($col . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle($col . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    $row++;
    
    // Данные
    $typeMap = ['q' => 'Вопрос', 'r' => 'Заказ', 's' => 'Услуга'];
    $statusMap = ['new' => 'Новая', 'processed' => 'В работе', 'closed' => 'Закрыта', 'cancelled' => 'Отклонена'];
    $statusColors = [
        'new' => 'FFFF0000',
        'processed' => 'FFFFA500',
        'closed' => 'FF00AA00',
        'cancelled' => 'FF808080'
    ];
    
    foreach ($exportRequests as $request) {
        // Декодируем JSON сообщение для заказов и услуг
        $message = $request['message'];
        $decoded = json_decode($message, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            if ($request['type'] === 'r') {
                $msgParts = [];
                if (isset($decoded['product_name'])) $msgParts[] = "Товар: " . $decoded['product_name'];
                if (isset($decoded['quantity'])) $msgParts[] = "Кол-во: " . $decoded['quantity'];
                if (isset($decoded['address'])) $msgParts[] = "Адрес: " . $decoded['address'];
                if (isset($decoded['total_price'])) $msgParts[] = "Итого: " . number_format($decoded['total_price'], 0, '', ' ') . " ₽";
                if (isset($decoded['configuration_name']) && $decoded['configuration_name']) $msgParts[] = "Конфигурация: " . $decoded['configuration_name'];
                $message = implode("\n", $msgParts);
            } elseif ($request['type'] === 's') {
                $msgParts = [];
                if (isset($decoded['service_name'])) $msgParts[] = "Услуга: " . $decoded['service_name'];
                if (isset($decoded['ordered_at'])) $msgParts[] = "Заказано: " . date('d.m.Y H:i', strtotime($decoded['ordered_at']));
                $message = implode("\n", $msgParts);
            }
        }
        
        $coordinates = '';
        if ($request['latitude'] && $request['longitude']) {
            $coordinates = $request['latitude'] . ', ' . $request['longitude'];
        }
        
        $sheet->setCellValue('A' . $row, $request['id']);
        $sheet->setCellValue('B' . $row, date('d.m.Y H:i:s', strtotime($request['datetime'])));
        $sheet->setCellValue('C' . $row, $request['user_name'] ?? 'Гость');
        $sheet->setCellValue('D' . $row, $request['user_email'] ?? '—');
        $sheet->setCellValue('E' . $row, $typeMap[$request['type']] ?? $request['type']);
        $sheet->setCellValue('F' . $row, $statusMap[$request['status']] ?? $request['status']);
        $sheet->setCellValue('G' . $row, $request['product_name'] ?? ($request['type'] === 's' ? 'Услуга' : '—'));
        $sheet->setCellValue('H' . $row, $message);
        $sheet->setCellValue('I' . $row, $request['client_support_name'] ?? '—');
        $sheet->setCellValue('J' . $row, $coordinates);
        
        // Цвет статуса
        $statusColor = $statusColors[$request['status']] ?? 'FFFFFFFF';
        $sheet->getStyle('F' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB($statusColor);
        $sheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        
        // Цвет типа
        if ($request['type'] === 'q') {
            $sheet->getStyle('E' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4');
        } elseif ($request['type'] === 'r') {
            $sheet->getStyle('E' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFED7D31');
        } else {
            $sheet->getStyle('E' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF70AD47');
        }
        $sheet->getStyle('E' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        
        $row++;
    }
    
    // Применяем границы ко всей таблице
    $lastRow = $row - 1;
    $lastCol = 'J';
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
    ];
    $sheet->getStyle('A' . ($row - count($exportRequests) - 1) . ':' . $lastCol . $lastRow)->applyFromArray($styleArray);
    
    // Автоматическая высота строк
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Настройка переноса текста и вертикального выравнивания
    $sheet->getStyle('H:' . $lastCol)->getAlignment()->setWrapText(true);
    $sheet->getStyle('A' . ($row - count($exportRequests) - 1) . ':' . $lastCol . $lastRow)
        ->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
    
    $sheet->getStyle('A' . ($row - count($exportRequests) - 1) . ':' . $lastCol . $lastRow)
        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    
    // Устанавливаем ширину колонки с сообщением
    $sheet->getColumnDimension('H')->setWidth(50);
    
    // Добавляем страницу со сводной статистикой
    $summarySheet = $spreadsheet->createSheet();
    $summarySheet->setTitle('Сводка');
    
    $sRow = 1;
    $summarySheet->setCellValue('A' . $sRow, 'СВОДНАЯ СТАТИСТИКА');
    $summarySheet->mergeCells('A' . $sRow . ':B' . $sRow);
    $summarySheet->getStyle('A' . $sRow)->getFont()->setBold(true)->setSize(14);
    $sRow += 2;
    
    $summarySheet->setCellValue('A' . $sRow, 'Показатель');
    $summarySheet->setCellValue('B' . $sRow, 'Значение');
    $summarySheet->getStyle('A' . $sRow . ':B' . $sRow)->getFont()->setBold(true);
    $sRow++;
    
    $stats = [
        ['Всего заявок', $requestsSummary['total'] ?? 0],
        ['Новые', $requestsSummary['new_count'] ?? 0],
        ['В работе', $requestsSummary['processed_count'] ?? 0],
        ['Закрытые', $requestsSummary['closed_count'] ?? 0],
        ['Отклонённые', $requestsSummary['cancelled_count'] ?? 0],
        [''],
        ['Вопросы', $requestsSummary['questions'] ?? 0],
        ['Заказы', $requestsSummary['orders'] ?? 0],
        ['Услуги', $requestsSummary['services'] ?? 0]
    ];
    
    foreach ($stats as $stat) {
        $summarySheet->setCellValue('A' . $sRow, $stat[0]);
        $summarySheet->setCellValue('B' . $sRow, $stat[1]);
        $sRow++;
    }
    
    $summarySheet->getColumnDimension('A')->setWidth(25);
    $summarySheet->getColumnDimension('B')->setWidth(20);
    
    // Отправляем файл
    $filename = 'requests_export_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// ============================================
// СКРИПТЫ ДЛЯ ГРАФИКОВ
// ============================================
$pageScript = '
const chartScript = document.createElement("script");
chartScript.src = "https://cdn.jsdelivr.net/npm/chart.js";
document.head.appendChild(chartScript);

chartScript.onload = function() {
    // График посещений
    const visitsCtx = document.getElementById("visitsLineChart");
    if (visitsCtx && ' . json_encode(!empty($chartData)) . ') {
        new Chart(visitsCtx, {
            type: "line",
            data: {
                labels: ' . json_encode(array_column($chartData, "d")) . ',
                datasets: [
                    {
                        label: "Всего посещений",
                        data: ' . json_encode(array_column($chartData, "c")) . ',
                        borderColor: "#2563eb",
                        backgroundColor: "rgba(37,99,235,0.1)",
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: "Уникальные",
                        data: ' . json_encode(array_column($chartData, "u")) . ',
                        borderColor: "#10b981",
                        borderDash: [6, 4],
                        fill: false,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: "top" } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // График заявок
    const requestsCtx = document.getElementById("requestsLineChart");
    if (requestsCtx && ' . json_encode(!empty($requestsChart)) . ') {
        new Chart(requestsCtx, {
            type: "line",
            data: {
                labels: ' . json_encode(array_column($requestsChart, "d")) . ',
                datasets: [
                    {
                        label: "Всего заявок",
                        data: ' . json_encode(array_column($requestsChart, "total")) . ',
                        borderColor: "#2563eb",
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: "Новые",
                        data: ' . json_encode(array_column($requestsChart, "new_count")) . ',
                        borderColor: "#ef4444",
                        fill: false,
                        tension: 0.4
                    },
                    {
                        label: "В работе",
                        data: ' . json_encode(array_column($requestsChart, "processed_count")) . ',
                        borderColor: "#f59e0b",
                        fill: false,
                        tension: 0.4
                    },
                    {
                        label: "Закрытые",
                        data: ' . json_encode(array_column($requestsChart, "closed_count")) . ',
                        borderColor: "#10b981",
                        fill: false,
                        tension: 0.4
                    },
                    {
                        label: "Отклонённые",
                        data: ' . json_encode(array_column($requestsChart, "cancelled_count")) . ',
                        borderColor: "#6b7280",
                        borderDash: [10, 5],
                        fill: false,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: "top" } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // Круговая диаграмма статусов
    const statusPieCtx = document.getElementById("statusPieChart");
    if (statusPieCtx && ' . json_encode(array_sum($statusDistribution) > 0) . ') {
        new Chart(statusPieCtx, {
            type: "doughnut",
            data: {
                labels: ' . json_encode(array_keys($statusDistribution)) . ',
                datasets: [{
                    data: ' . json_encode(array_values($statusDistribution)) . ',
                    backgroundColor: ["#ef4444", "#f59e0b", "#10b981", "#6b7280"],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: "bottom" } }
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
    /* Стили аналогичны предыдущей версии */
    .content-wrapper { background: #f8fafc; min-height: 100vh; }
    .content-header h1 { font-size: 1.5rem; font-weight: 600; color: #1e293b; }
    
    .analytics-tabs { border-bottom: 1px solid #e2e8f0; margin-bottom: 1.5rem; display: flex; gap: 0.5rem; }
    .analytics-tabs .tab-link { padding: 0.75rem 1.5rem; font-weight: 500; color: #64748b; border-bottom: 2px solid transparent; text-decoration: none; }
    .analytics-tabs .tab-link:hover { color: #334155; border-bottom-color: #cbd5e1; }
    .analytics-tabs .tab-link.active { color: #2563eb; border-bottom-color: #2563eb; }
    
    .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 1.25rem; }
    .card-header { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.25rem; }
    .card-header h3.card-title { font-size: 1rem; font-weight: 600; color: #334155; margin: 0; }
    .card-body { padding: 1.25rem; }
    
    .stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; }
    .stat-card .value { font-size: 1.75rem; font-weight: 700; color: #1e293b; }
    .stat-card .label { font-size: 0.875rem; color: #64748b; }
    
    .filter-card .form-control { border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.5rem 0.75rem; }
    .filter-card .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); outline: none; }
    
    .btn { border-radius: 8px; font-weight: 500; padding: 0.5rem 1rem; }
    .btn-primary { background: #2563eb; border-color: #2563eb; }
    .btn-primary:hover { background: #1d4ed8; }
    .btn-success { background: #fff; border: 1px solid #cbd5e1; color: #334155; }
    .btn-success:hover { background: #f1f5f9; }
    
    .badge { padding: 0.35rem 0.6rem; border-radius: 6px; font-size: 0.8rem; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-warning { background: #fed7aa; color: #92400e; }
    .badge-success { background: #dcfce7; color: #166534; }
    .badge-secondary { background: #e5e7eb; color: #374151; }
    .badge-info { background: #dbeafe; color: #1e40af; }
    
    .chart-container { position: relative; height: 350px; width: 100%; }
    .pie-chart-container { height: 250px; max-width: 350px; margin: 0 auto; }
    
    .empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.6; }
    
    @media (max-width: 768px) { .filter-card .row > div { margin-bottom: 0.75rem; } }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Аналитика</h1>
        </div>
    </div>
    
    <section class="content">
        <div class="container-fluid">
            <!-- Вкладки -->
            <div class="analytics-tabs">
                <a href="?tab=visits&from=<?= urlencode($dateFrom) ?>&to=<?= urlencode($dateTo) ?>" 
                   class="tab-link <?= $activeTab === 'visits' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Посещения
                </a>
                <a href="?tab=requests&from=<?= urlencode($dateFrom) ?>&to=<?= urlencode($dateTo) ?>" 
                   class="tab-link <?= $activeTab === 'requests' ? 'active' : '' ?>">
                    <i class="fas fa-ticket-alt"></i> Заявки
                </a>
            </div>
            
            <!-- Фильтры -->
            <div class="card filter-card">
                <div class="card-header">
                    <h3 class="card-title">Фильтры</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="tab" value="<?= e($activeTab) ?>">
                        <div class="col-md-3">
                            <label>С даты</label>
                            <input type="date" name="from" class="form-control" value="<?= e($dateFrom) ?>">
                        </div>
                        <div class="col-md-3">
                            <label>По дату</label>
                            <input type="date" name="to" class="form-control" value="<?= e($dateTo) ?>">
                        </div>
                        <?php if ($activeTab === 'requests'): ?>
                        <div class="col-md-2">
                            <label>Статус</label>
                            <select name="request_status" class="form-control">
                                <option value="">Все статусы</option>
                                <option value="new" <?= $requestStatusFilter === 'new' ? 'selected' : '' ?>>Новые</option>
                                <option value="processed" <?= $requestStatusFilter === 'processed' ? 'selected' : '' ?>>В работе</option>
                                <option value="closed" <?= $requestStatusFilter === 'closed' ? 'selected' : '' ?>>Закрытые</option>
                                <option value="cancelled" <?= $requestStatusFilter === 'cancelled' ? 'selected' : '' ?>>Отклонённые</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Тип</label>
                            <select name="request_type" class="form-control">
                                <option value="">Все типы</option>
                                <option value="q" <?= $requestTypeFilter === 'q' ? 'selected' : '' ?>>Вопросы</option>
                                <option value="r" <?= $requestTypeFilter === 'r' ? 'selected' : '' ?>>Заказы</option>
                                <option value="s" <?= $requestTypeFilter === 's' ? 'selected' : '' ?>>Услуги</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Применить
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Применить
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <?php if ($activeTab === 'visits'): ?>
            <!-- Вкладка посещений -->
            <?php if ($visitsTableExists && $summary): ?>
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="stat-card"><div class="value"><?= number_format($summary['total'] ?? 0) ?></div><div class="label">Всего посещений</div></div>
                </div>
                <div class="col-lg-6">
                    <div class="stat-card"><div class="value"><?= number_format($summary['unique_ips'] ?? 0) ?></div><div class="label">Уникальных посетителей</div></div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Посещения по дням</h3></div>
                        <div class="card-body p-0">
                            <div class="chart-container">
                                <?php if ($visitsTableExists && !empty($chartData)): ?>
                                    <canvas id="visitsLineChart"></canvas>
                                <?php else: ?>
                                    <div class="empty-state"><i class="fas fa-chart-line"></i><br>Нет данных</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Популярные страницы</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>Страница</th><th class="text-center">Посещения</th><th class="text-center">Уникальные</th></tr></thead>
                                <tbody>
                                    <?php foreach ($topPages as $p): ?>
                                    <tr><td><code><?= e($p['page_url']) ?></code></td><td class="text-center"><span class="badge badge-info"><?= $p['cnt'] ?></span></td><td class="text-center"><span class="badge badge-success"><?= $p['uniq'] ?></span></td></tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($topPages)): ?><tr><td colspan="3" class="text-center">Нет данных</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Вкладка заявок -->
            
            <!-- Сводная статистика -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="stat-card"><div class="value"><?= number_format($requestsSummary['total'] ?? 0) ?></div><div class="label">Всего заявок</div></div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-card" style="border-left: 3px solid #ef4444;"><div class="value"><?= number_format($requestsSummary['new_count'] ?? 0) ?></div><div class="label">Новые</div></div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-card" style="border-left: 3px solid #f59e0b;"><div class="value"><?= number_format($requestsSummary['processed_count'] ?? 0) ?></div><div class="label">В работе</div></div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-card" style="border-left: 3px solid #10b981;"><div class="value"><?= number_format($requestsSummary['closed_count'] ?? 0) ?></div><div class="label">Закрытые</div></div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="stat-card" style="border-left: 3px solid #6b7280;"><div class="value"><?= number_format($requestsSummary['cancelled_count'] ?? 0) ?></div><div class="label">Отклонённые</div></div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-card"><div class="value"><?= number_format($requestsSummary['questions'] ?? 0) ?></div><div class="label">Вопросы</div></div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-card"><div class="value"><?= number_format($requestsSummary['orders'] ?? 0) ?></div><div class="label">Заказы</div></div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="stat-card"><div class="value"><?= number_format($requestsSummary['services'] ?? 0) ?></div><div class="label">Услуги</div></div>
                </div>
            </div>
            
            <!-- Графики -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Динамика заявок по дням</h3></div>
                        <div class="card-body p-0">
                            <div class="chart-container">
                                <?php if (!empty($requestsChart)): ?>
                                    <canvas id="requestsLineChart"></canvas>
                                <?php else: ?>
                                    <div class="empty-state"><i class="fas fa-chart-line"></i><br>Нет данных</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Распределение по статусам</h3></div>
                        <div class="card-body">
                            <?php if (array_sum($statusDistribution) > 0): ?>
                                <div class="pie-chart-container"><canvas id="statusPieChart"></canvas></div>
                            <?php else: ?>
                                <div class="empty-state"><i class="fas fa-chart-pie"></i><br>Нет данных</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ТОП пользователей -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Активные пользователи</h3>
                            <div class="float-right">
                                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel', 'tab' => 'requests'])) ?>" class="btn btn-success">
                                    <i class="fas fa-file-excel"></i> Экспорт в Excel
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>Пользователь</th><th>Email</th><th class="text-center">Кол-во заявок</th></thead>
                                <tbody>
                                    <?php foreach ($topUsers as $u): ?>
                                    <tr><td><strong><?= e($u['user_name'] ?? 'Гость') ?></strong></td><td><?= e($u['user_email'] ?? '—') ?></td><td class="text-center"><span class="badge badge-primary"><?= $u['request_count'] ?></span></td></tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($topUsers)): ?><tr><td colspan="3" class="text-center">Нет данных</td><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php endif; ?>
            
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>