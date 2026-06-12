<?php
// portal/search_global.php - поиск по всему порталу
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$results = [];

if (strlen($query) >= 2) {
    
    // ========== ПОИСК В МИП ==========
    // Товары
    $stmt = $pdo->prepare("
        SELECT id, name, short_description as content, 'product' as type, 'mip' as section, base_price as price, '' as section_url
        FROM products 
        WHERE (name LIKE ? OR short_description LIKE ?) AND status = 'active'
        LIMIT 5
    ");
    $stmt->execute(["%$query%", "%$query%"]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['url'] = '/mip/product.php?id=' . $row['id'];
        $row['section_url'] = '/mip/';
        $results[] = $row;
    }
    
    // Услуги МИП
    $stmt = $pdo->prepare("
        SELECT id, name, short_description as content, 'service' as type, 'mip' as section, price, '' as section_url
        FROM services 
        WHERE (name LIKE ? OR short_description LIKE ?) AND is_active = 1
        LIMIT 5
    ");
    $stmt->execute(["%$query%", "%$query%"]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['url'] = '/mip/services_catalog.php?id=' . $row['id'];
        $row['section_url'] = '/mip/';
        $results[] = $row;
    }
    
    // Новости МИП
    $stmt = $pdo->prepare("
        SELECT id, title, content, 'news' as type, 'mip' as section, '' as price, '' as section_url
        FROM news 
        WHERE title LIKE ? OR content LIKE ?
        LIMIT 3
    ");
    $stmt->execute(["%$query%", "%$query%"]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['name'] = $row['title'];
        $row['url'] = '/mip/news_view.php?id=' . $row['id'];
        $row['section_url'] = '/mip/';
        $results[] = $row;
    }
    
    // ========== ПОИСК В ЛАБОРАТОРИИ ==========
    // Проекты
    $stmt = $pdo->prepare("
        SELECT id, name, short_description as content, 'project' as type, 'lab' as section, '' as price, '' as section_url
        FROM lab_projects 
        WHERE name LIKE ? OR short_description LIKE ?
        LIMIT 5
    ");
    $stmt->execute(["%$query%", "%$query%"]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['url'] = '/lab/projects.php?id=' . $row['id'];
        $row['section_url'] = '/lab/';
        $results[] = $row;
    }
    
    // Услуги лаборатории
    $stmt = $pdo->prepare("
        SELECT id, name, description as content, 'service' as type, 'lab' as section, '' as price, '' as section_url
        FROM lab_services 
        WHERE name LIKE ? OR description LIKE ?
        LIMIT 5
    ");
    $stmt->execute(["%$query%", "%$query%"]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['url'] = '/lab/services.php?id=' . $row['id'];
        $row['section_url'] = '/lab/';
        $results[] = $row;
    }
    
    // Новости лаборатории (те же новости, что и в МИП, но можно добавить отдельную таблицу)
    // Если есть отдельные новости для лаборатории, добавьте их здесь
}

echo json_encode([
    'success' => true,
    'query' => $query,
    'count' => count($results),
    'results' => $results
]);