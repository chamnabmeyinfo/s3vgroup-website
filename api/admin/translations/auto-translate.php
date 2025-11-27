<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Translation\AutoTranslationService;
use App\Domain\Translation\TranslationRepository;
use App\Domain\Translation\TranslationService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

if (Request::method() !== 'POST') {
    JsonResponse::error('Method not allowed.', 405);
}

$payload = Request::json() ?? $_POST;

if (!is_array($payload)) {
    JsonResponse::error('Invalid payload.', 400);
}

$sourceLang = $payload['source_lang'] ?? 'en';
$targetLang = $payload['target_lang'] ?? null;
$namespace = $payload['namespace'] ?? null;

if (!$targetLang) {
    JsonResponse::error('Target language is required.', 422);
}

$db = getDB();
$repository = new TranslationRepository($db);
$service = new TranslationService($repository);

// Get API key from site options (if configured)
$apiKey = null;
try {
    $siteOptionRepo = new \App\Domain\Settings\SiteOptionRepository($db);
    $apiKey = $siteOptionRepo->get('google_translate_api_key');
} catch (\Exception $e) {
    // API key not configured, will use LibreTranslate
}

$autoService = new AutoTranslationService($apiKey, $apiKey ? 'google' : 'libre');

// Get missing translations
$missing = $service->getMissingTranslations($sourceLang, $targetLang, $namespace);

if (empty($missing)) {
    JsonResponse::success([
        'message' => 'No missing translations found.',
        'count' => 0,
    ]);
}

$translated = 0;
$errors = [];

foreach ($missing as $item) {
    $text = $item['source_value'] ?? '';
    
    if (empty($text)) {
        continue;
    }

    $translatedText = $autoService->translate($text, $sourceLang, $targetLang);
    
    if ($translatedText) {
        try {
            $service->setTranslation(
                $item['key_name'],
                $targetLang,
                $translatedText,
                $item['namespace'] ?? 'general',
                true, // is_auto_translated
                true  // needs_review
            );
            $translated++;
        } catch (\Exception $e) {
            $errors[] = "Failed to save translation for key: {$item['key_name']}";
        }
    } else {
        $errors[] = "Failed to translate key: {$item['key_name']}";
    }

    // Small delay to avoid rate limiting
    usleep(200000); // 0.2 seconds
}

JsonResponse::success([
    'message' => "Translated {$translated} items successfully.",
    'count' => $translated,
    'errors' => $errors,
]);

