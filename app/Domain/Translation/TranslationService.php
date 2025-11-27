<?php

declare(strict_types=1);

namespace App\Domain\Translation;

use InvalidArgumentException;

final class TranslationService
{
    public function __construct(
        private readonly TranslationRepository $repository
    ) {
    }

    public function getLanguages(bool $activeOnly = false): array
    {
        return $this->repository->getLanguages($activeOnly);
    }

    public function getDefaultLanguage(): ?array
    {
        return $this->repository->getDefaultLanguage();
    }

    public function getLanguageByCode(string $code): ?array
    {
        return $this->repository->getLanguageByCode($code);
    }

    public function translate(string $key, string $languageCode, string $namespace = 'general', ?string $default = null): string
    {
        $translation = $this->repository->getTranslation($key, $languageCode, $namespace);
        
        if ($translation !== null) {
            return $translation;
        }

        // Fallback to default language if translation not found
        $defaultLang = $this->repository->getDefaultLanguage();
        if ($defaultLang && $defaultLang['code'] !== $languageCode) {
            $translation = $this->repository->getTranslation($key, $defaultLang['code'], $namespace);
            if ($translation !== null) {
                return $translation;
            }
        }

        return $default ?? $key;
    }

    public function getTranslations(string $languageCode, ?string $namespace = null): array
    {
        return $this->repository->getTranslations($languageCode, $namespace);
    }

    public function setTranslation(
        string $key,
        string $languageCode,
        string $value,
        string $namespace = 'general',
        bool $isAutoTranslated = false,
        bool $needsReview = false
    ): void {
        if (empty($key)) {
            throw new InvalidArgumentException('Translation key cannot be empty.');
        }

        if (empty($languageCode)) {
            throw new InvalidArgumentException('Language code cannot be empty.');
        }

        $this->repository->setTranslation($key, $languageCode, $value, $namespace, $isAutoTranslated, $needsReview);
    }

    public function bulkSetTranslations(array $translations): void
    {
        $this->repository->bulkSetTranslations($translations);
    }

    public function deleteTranslation(string $key, string $languageCode, string $namespace = 'general'): void
    {
        $this->repository->deleteTranslation($key, $languageCode, $namespace);
    }

    public function getMissingTranslations(string $sourceLanguage, string $targetLanguage, ?string $namespace = null): array
    {
        return $this->repository->getMissingTranslations($sourceLanguage, $targetLanguage, $namespace);
    }

    public function autoTranslate(string $text, string $sourceLanguage, string $targetLanguage): ?string
    {
        // This will be implemented with Google Translate API or similar
        // For now, return null to indicate translation needed
        return null;
    }

    public function updateLanguage(array $attributes): array
    {
        return $this->repository->updateLanguage($attributes);
    }
}

