<?php
// portal/search_global.php - поиск по всему порталу
session_start();
require_once 'config.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$results = [];

if (strlen($query) >= 2) {
    
    // ============================================
    // ПОИСК В МИП
    // ============================================
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
    
    // ============================================
    // ПОИСК В ЛАБОРАТОРИИ
    // ============================================
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
        
        // ============================================
        // ПОИСК ПО СОТРУДНИКАМ (team)
        // ============================================
        $stmt = $pdo->prepare("
            SELECT id, name, position, bio as content, 'team' as type, 'lab' as section, '' as price,
                   photo_url, email, phone, education
            FROM team 
            WHERE name LIKE ? OR position LIKE ? OR bio LIKE ? OR education LIKE ?
            LIMIT 10
        ");
        $stmt->execute(["%$query%", "%$query%", "%$query%", "%$query%"]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Формируем краткое описание из должности
            $content = $row['position'];
            if (!empty($row['education'])) {
                $content .= ' | ' . $row['education'];
            }
            $row['content'] = $content;
            $row['url'] = '/lab/about.php#team-' . $row['id'];
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
], JSON_UNESCAPED_UNICODE);