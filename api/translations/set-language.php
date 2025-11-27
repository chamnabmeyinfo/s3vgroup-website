<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/translation.php';

use App\Domain\Translation\TranslationRepository;
use App\Domain\Translation\TranslationService;
use App\Http\JsonResponse;
use App\Http\Request;

if (Request::method() !== 'POST') {
    JsonResponse::error('Method not allowed.', 405);
}

$payload = Request::json() ?? $_POST;
$languageCode = $payload['language'] ?? null;

if (!$languageCode) {
    JsonResponse::error('Language code is required.', 422);
}

try {
    $db = getDB();
    $repository = new TranslationRepository($db);
    $service = new TranslationService($repository);
    
    // Verify language exists
    $language = $service->getLanguageByCode($languageCode);
    if (!$language || !$language['is_active']) {
        JsonResponse::error('Language not found or inactive.', 404);
    }
    
    setCurrentLanguage($languageCode);
    
    JsonResponse::success([
        'message' => 'Language changed successfully.',
        'language' => $languageCode,
    ]);
} catch (\Exception $e) {
    JsonResponse::error('Failed to set language.', 500);
}

