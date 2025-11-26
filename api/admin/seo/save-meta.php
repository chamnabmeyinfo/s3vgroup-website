<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireAdmin();

use App\Database\Connection;
use App\Domain\Settings\SiteOptionRepository;

$db = Connection::getInstance();
$repository = new SiteOptionRepository($db);

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

try {
    if (isset($input['seo_title'])) {
        $repository->set('seo_title', $input['seo_title']);
    }
    if (isset($input['seo_description'])) {
        $repository->set('seo_description', $input['seo_description']);
    }
    if (isset($input['seo_keywords'])) {
        $repository->set('seo_keywords', $input['seo_keywords']);
    }
    
    echo json_encode(['status' => 'success', 'message' => 'SEO settings saved']);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

