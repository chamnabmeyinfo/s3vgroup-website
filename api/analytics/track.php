<?php
/**
 * Analytics Tracking API
 * Tracks page views, product views, and other events
 */

require_once __DIR__ . '/../../bootstrap/app.php';
require_once __DIR__ . '/../../config/database.php';

use App\Database\Connection;
use App\Support\Id;

header('Content-Type: application/json');

$db = Connection::getInstance();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['event_type']) || !isset($input['event_name'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

try {
    $id = Id::prefixed('event');
    $eventType = $input['event_type'];
    $eventName = $input['event_name'];
    $userIp = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $referrer = $_SERVER['HTTP_REFERER'] ?? null;
    $pageUrl = $input['page_url'] ?? null;
    $productId = $input['product_id'] ?? null;
    $categoryId = $input['category_id'] ?? null;
    $sessionId = $input['session_id'] ?? session_id();
    $metadata = json_encode($input['metadata'] ?? []);

    $stmt = $db->prepare("
        INSERT INTO analytics_events (
            id, event_type, event_name, user_ip, user_agent, referrer, 
            page_url, product_id, category_id, session_id, metadata
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $id, $eventType, $eventName, $userIp, $userAgent, $referrer,
        $pageUrl, $productId, $categoryId, $sessionId, $metadata
    ]);

    echo json_encode(['status' => 'success', 'id' => $id]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

