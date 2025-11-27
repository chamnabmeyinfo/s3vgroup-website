<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/translation.php';

use App\Domain\Translation\TranslationRepository;
use App\Domain\Translation\TranslationService;
use App\Http\JsonResponse;
use App\Http\Request;

if (Request::method() !== 'GET') {
    JsonResponse::error('Method not allowed.', 405);
}

$languageCode = Request::query('lang', getCurrentLanguage());
$namespace = Request::query('namespace');

try {
    $db = getDB();
    $repository = new TranslationRepository($db);
    $service = new TranslationService($repository);
    
    $translations = $service->getTranslations($languageCode, $namespace);
    
    JsonResponse::success([
        'translations' => $translations,
        'language' => $languageCode,
    ]);
} catch (\Exception $e) {
    JsonResponse::error('Failed to load translations.', 500);
}

