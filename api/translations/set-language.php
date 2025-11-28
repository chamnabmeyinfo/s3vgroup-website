<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/translation.php';

use App\Http\JsonResponse;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    JsonResponse::error('Method not allowed.', 405);
}

$language = $_POST['language'] ?? null;

if (!$language) {
    JsonResponse::error('Language is required.', 422);
}

$languages = getAvailableLanguages();
$codes = array_column($languages, 'code');

if (!in_array($language, $codes, true)) {
    JsonResponse::error('Language not available.', 404);
}

setCurrentLanguage($language);

JsonResponse::success([
    'message' => 'Language updated',
    'language' => $language,
]);

