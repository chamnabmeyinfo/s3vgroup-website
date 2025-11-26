<?php
require_once __DIR__ . '/../../../bootstrap/app.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$content = $_POST['content'] ?? '';

if (empty($content)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Content is required']);
    exit;
}

try {
    $robotsFile = __DIR__ . '/../../../robots.txt';
    file_put_contents($robotsFile, $content);
    
    echo json_encode(['status' => 'success', 'message' => 'Robots.txt saved']);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

