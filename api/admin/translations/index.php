<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Translation\TranslationRepository;
use App\Domain\Translation\TranslationService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$db = getDB();
$repository = new TranslationRepository($db);
$service = new TranslationService($repository);

switch (Request::method()) {
    case 'GET':
        $languageCode = Request::query('lang');
        $namespace = Request::query('namespace', 'general');

        if (!$languageCode) {
            JsonResponse::error('Language code is required.', 422);
        }

        $allTranslations = $repository->getAllTranslations($namespace);
        $translations = array_filter($allTranslations, function($t) use ($languageCode) {
            return $t['language_code'] === $languageCode;
        });

        JsonResponse::success([
            'translations' => array_values($translations),
            'language' => $languageCode,
            'namespace' => $namespace,
        ]);
        break;

    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        $key = $payload['key'] ?? null;
        $languageCode = $payload['language_code'] ?? null;
        $value = $payload['value'] ?? null;
        $namespace = $payload['namespace'] ?? 'general';
        $isAutoTranslated = !empty($payload['is_auto_translated']);
        $needsReview = !empty($payload['needs_review']);

        if (!$key || !$languageCode || $value === null) {
            JsonResponse::error('Key, language code, and value are required.', 422);
        }

        try {
            $service->setTranslation($key, $languageCode, $value, $namespace, $isAutoTranslated, $needsReview);
            JsonResponse::success(['message' => 'Translation saved successfully.']);
        } catch (\InvalidArgumentException $e) {
            JsonResponse::error($e->getMessage(), 422);
        }
        break;

    case 'DELETE':
        $key = Request::query('key');
        $languageCode = Request::query('lang');
        $namespace = Request::query('namespace', 'general');

        if (!$key || !$languageCode) {
            JsonResponse::error('Key and language code are required.', 422);
        }

        try {
            $service->deleteTranslation($key, $languageCode, $namespace);
            JsonResponse::success(['message' => 'Translation deleted successfully.']);
        } catch (\Exception $e) {
            JsonResponse::error($e->getMessage(), 500);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

