<?php
/**
 * Translation Helper Functions
 */

use App\Domain\Translation\TranslationRepository;
use App\Domain\Translation\TranslationService;

/**
 * Get current language from session or cookie, fallback to default
 */
function getCurrentLanguage(): string
{
    // Check session first
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }

    // Check cookie
    if (isset($_COOKIE['language'])) {
        return $_COOKIE['language'];
    }

    // Check GET parameter
    if (isset($_GET['lang'])) {
        return $_GET['lang'];
    }

    // Fallback to default
    try {
        $db = getDB();
        $repository = new TranslationRepository($db);
        $service = new TranslationService($repository);
        $default = $service->getDefaultLanguage();
        return $default ? $default['code'] : 'en';
    } catch (\Exception $e) {
        return 'en';
    }
}

/**
 * Set current language
 */
function setCurrentLanguage(string $languageCode): void
{
    $_SESSION['language'] = $languageCode;
    setcookie('language', $languageCode, time() + (365 * 24 * 60 * 60), '/'); // 1 year
}

/**
 * Translate a key
 */
function __(string $key, ?string $namespace = 'general', ?string $default = null): string
{
    try {
        $db = getDB();
        $repository = new TranslationRepository($db);
        $service = new TranslationService($repository);
        $languageCode = getCurrentLanguage();
        
        return $service->translate($key, $languageCode, $namespace, $default);
    } catch (\Exception $e) {
        return $default ?? $key;
    }
}

/**
 * Get all translations for current language
 */
function getTranslations(?string $namespace = null): array
{
    try {
        $db = getDB();
        $repository = new TranslationRepository($db);
        $service = new TranslationService($repository);
        $languageCode = getCurrentLanguage();
        
        return $service->getTranslations($languageCode, $namespace);
    } catch (\Exception $e) {
        return [];
    }
}

/**
 * Get available languages
 */
function getAvailableLanguages(bool $activeOnly = true): array
{
    try {
        $db = getDB();
        $repository = new TranslationRepository($db);
        $service = new TranslationService($repository);
        
        return $service->getLanguages($activeOnly);
    } catch (\Exception $e) {
        return [];
    }
}

