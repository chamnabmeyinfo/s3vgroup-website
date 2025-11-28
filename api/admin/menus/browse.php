<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap/app.php';

use App\Http\JsonResponse;
use App\Http\Request;

$db = getDB();
$type = Request::query('type', 'pages');
$search = Request::query('search', '');

try {
    $results = [];
    
    switch ($type) {
        case 'pages':
            $sql = "SELECT id, title, slug, status FROM pages WHERE 1=1";
            $params = [];
            if ($search) {
                $sql .= " AND (title LIKE ? OR slug LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = [$searchTerm, $searchTerm];
            }
            $sql .= " ORDER BY title ASC LIMIT 50";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as &$result) {
                $result['url'] = '/page.php?slug=' . $result['slug'];
                $result['type'] = 'page';
            }
            break;
            
        case 'posts':
            $sql = "SELECT id, title, slug, status FROM blog_posts WHERE 1=1";
            $params = [];
            if ($search) {
                $sql .= " AND (title LIKE ? OR slug LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = [$searchTerm, $searchTerm];
            }
            $sql .= " ORDER BY title ASC LIMIT 50";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as &$result) {
                $result['url'] = '/post.php?slug=' . $result['slug'];
                $result['type'] = 'post';
            }
            break;
            
        case 'categories':
            $sql = "SELECT id, name as title, slug FROM categories WHERE 1=1";
            $params = [];
            if ($search) {
                $sql .= " AND (name LIKE ? OR slug LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = [$searchTerm, $searchTerm];
            }
            $sql .= " ORDER BY name ASC LIMIT 50";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as &$result) {
                $result['url'] = '/products.php?category=' . $result['slug'];
                $result['type'] = 'category';
                $result['status'] = 'PUBLISHED';
            }
            break;
            
        case 'products':
            $sql = "SELECT id, name as title, slug, status FROM products WHERE status = 'PUBLISHED'";
            $params = [];
            if ($search) {
                $sql .= " AND (name LIKE ? OR slug LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = [$searchTerm, $searchTerm];
            }
            $sql .= " ORDER BY name ASC LIMIT 50";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as &$result) {
                $result['url'] = '/product.php?slug=' . $result['slug'];
                $result['type'] = 'product';
            }
            break;
    }
    
    JsonResponse::success(['items' => $results, 'type' => $type]);
} catch (\Exception $e) {
    JsonResponse::error($e->getMessage(), 500);
}

