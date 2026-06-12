<?php
// portal/search_global.php - поиск по всему порталу
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$results = [];

if (strlen($query) >= 2) {
    
    // Поиск в МИП
    try {
        // Товары
        $stmt = $pdo->prepare("
            SELECT id, name, short_description as content, 'product' as type, 'mip' as section, base_price as price
            FROM products 
            WHERE (name LIKE ? OR short_description LIKE ?) AND status = 'active'
            LIMIT 5
        ");
        $stmt->execute(["%$query%", "%$query%"]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['url'] = '/mip/product.php?id=' . $row['id'];
            $results[] = $row;
        }
        
        // Услуги МИП
        $stmt = $pdo->prepare("
            SELECT id, name, short_description as content, 'service' as type, 'mip' as section, price
            FROM services 
            WHERE (name LIKE ? OR short_description LIKE ?) AND is_active = 1
            LIMIT 5
        ");
        $stmt->execute(["%$query%", "%$query%"]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['url'] = '/mip/service_view.php?id=' . $row['id'];
            $results[] = $row;
        }
        
        // Новости МИП
        $stmt = $pdo->prepare("
            SELECT id, title as name, content, 'news' as type, 'mip' as section, '' as price
            FROM news 
            WHERE (title LIKE ? OR content LIKE ?) AND section = 'mip'
            LIMIT 3
        ");
        $stmt->execute(["%$query%", "%$query%"]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['url'] = '/mip/news_view.php?id=' . $row['id'];
            $results[] = $row;
        }
    } catch (Exception $e) {
        error_log("MIP search error: " . $e->getMessage());
    }
    
    // Поиск в лаборатории
    try {
        // Проекты лаборатории
        $stmt = $pdo->prepare("
            SELECT id, name, short_description as content, 'project' as type, 'lab' as section, '' as price
            FROM lab_projects 
            WHERE name LIKE ? OR short_description LIKE ? OR full_description LIKE ?
            LIMIT 5
        ");
        $stmt->execute(["%$query%", "%$query%", "%$query%"]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['url'] = '/lab/project_detail.php?id=' . $row['id'];
            $results[] = $row;
        }
        
        // Новости лаборатории
        $stmt = $pdo->prepare("
            SELECT id, title as name, content, 'news' as type, 'lab' as section, '' as price
            FROM news 
            WHERE (title LIKE ? OR content LIKE ?) AND section = 'lab'
            LIMIT 3
        ");
        $stmt->execute(["%$query%", "%$query%"]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['url'] = '/lab/news_view.php?id=' . $row['id'];
            $results[] = $row;
        }
        
        // Направления работы лаборатории
        $stmt = $pdo->prepare("
            SELECT id, name, description as content, 'direction' as type, 'lab' as section, '' as price
            FROM directions 
            WHERE name LIKE ? OR description LIKE ?
            LIMIT 3
        ");
        $stmt->execute(["%$query%", "%$query%"]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['url'] = '/lab/about.php#directions';
            $results[] = $row;
        }
        
        // Образовательные программы
        $stmt = $pdo->prepare("
            SELECT id, title as name, description as content, 'education' as type, 'lab' as section, '' as price
            FROM education 
            WHERE title LIKE ? OR description LIKE ?
            LIMIT 3
        ");
        $stmt->execute(["%$query%", "%$query%"]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['url'] = '/lab/education.php?id=' . $row['id'];
            $results[] = $row;
        }
        
        // Оборудование
        $stmt = $pdo->prepare("
            SELECT id, name, description as content, 'equipment' as type, 'lab' as section, '' as price
            FROM equipment 
            WHERE name LIKE ? OR description LIKE ?
            LIMIT 3
        ");
        $stmt->execute(["%$query%", "%$query%"]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['url'] = '/lab/equipment.php?id=' . $row['id'];
            $results[] = $row;
        }
        
    } catch (Exception $e) {
        error_log("Lab search error: " . $e->getMessage());
    }
}

echo json_encode([
    'success' => true,
    'query' => $query,
    'count' => count($results),
    'results' => $results
]);