<?php

declare(strict_types=1);

namespace App\Domain\Translation;

use App\Support\Id;
use PDO;
use RuntimeException;

final class TranslationRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function getLanguages(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM languages';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = TRUE';
        }
        $sql .= ' ORDER BY sort_order ASC, name ASC';
        
        $statement = $this->pdo->query($sql);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDefaultLanguage(): ?array
    {
        $statement = $this->pdo->query("SELECT * FROM languages WHERE is_default = TRUE LIMIT 1");
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getLanguageByCode(string $code): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM languages WHERE code = :code LIMIT 1');
        $statement->execute([':code' => $code]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getTranslation(string $key, string $languageCode, string $namespace = 'general'): ?string
    {
        $sql = <<<SQL
SELECT value FROM translations
WHERE key_name = :key AND language_code = :lang AND namespace = :namespace
LIMIT 1
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':key' => $key,
            ':lang' => $languageCode,
            ':namespace' => $namespace,
        ]);

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['value'] : null;
    }

    public function getTranslations(string $languageCode, ?string $namespace = null): array
    {
        $sql = <<<SQL
SELECT key_name, namespace, value, is_auto_translated, needs_review
FROM translations
WHERE language_code = :lang
SQL;

        $params = [':lang' => $languageCode];

        if ($namespace) {
            $sql .= ' AND namespace = :namespace';
            $params[':namespace'] = $namespace;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        $translations = [];

        foreach ($results as $row) {
            $ns = $row['namespace'] ?? 'general';
            if (!isset($translations[$ns])) {
                $translations[$ns] = [];
            }
            $translations[$ns][$row['key_name']] = [
                'value' => $row['value'],
                'is_auto_translated' => (bool) $row['is_auto_translated'],
                'needs_review' => (bool) $row['needs_review'],
            ];
        }

        return $translations;
    }

    public function getAllTranslations(?string $namespace = null): array
    {
        $sql = <<<SQL
SELECT t.*, l.name AS language_name, l.native_name
FROM translations t
JOIN languages l ON t.language_code = l.code
SQL;

        $params = [];
        if ($namespace) {
            $sql .= ' WHERE t.namespace = :namespace';
            $params[':namespace'] = $namespace;
        }

        $sql .= ' ORDER BY t.namespace, t.key_name, l.sort_order';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setTranslation(
        string $key,
        string $languageCode,
        string $value,
        string $namespace = 'general',
        bool $isAutoTranslated = false,
        bool $needsReview = false
    ): void {
        $sql = <<<SQL
INSERT INTO translations (id, language_code, key_name, namespace, value, is_auto_translated, needs_review, createdAt, updatedAt)
VALUES (:id, :lang, :key, :namespace, :value, :auto, :review, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    value = :value,
    is_auto_translated = :auto,
    needs_review = :review,
    updatedAt = NOW()
SQL;

        $id = Id::prefixed('trn');
        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':id' => $id,
            ':lang' => $languageCode,
            ':key' => $key,
            ':namespace' => $namespace,
            ':value' => $value,
            ':auto' => $isAutoTranslated ? 1 : 0,
            ':review' => $needsReview ? 1 : 0,
        ]);
    }

    public function bulkSetTranslations(array $translations): void
    {
        $this->pdo->beginTransaction();

        try {
            foreach ($translations as $translation) {
                $this->setTranslation(
                    $translation['key'],
                    $translation['language_code'],
                    $translation['value'],
                    $translation['namespace'] ?? 'general',
                    $translation['is_auto_translated'] ?? false,
                    $translation['needs_review'] ?? false
                );
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function deleteTranslation(string $key, string $languageCode, string $namespace = 'general'): void
    {
        $sql = <<<SQL
DELETE FROM translations
WHERE key_name = :key AND language_code = :lang AND namespace = :namespace
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':key' => $key,
            ':lang' => $languageCode,
            ':namespace' => $namespace,
        ]);
    }

    public function getMissingTranslations(string $sourceLanguage, string $targetLanguage, ?string $namespace = null): array
    {
        $sql = <<<SQL
SELECT DISTINCT s.key_name, s.namespace, s.value AS source_value
FROM translations s
LEFT JOIN translations t ON s.key_name = t.key_name 
    AND s.namespace = t.namespace 
    AND t.language_code = :target
WHERE s.language_code = :source
    AND t.id IS NULL
SQL;

        $params = [
            ':source' => $sourceLanguage,
            ':target' => $targetLanguage,
        ];

        if ($namespace) {
            $sql .= ' AND s.namespace = :namespace';
            $params[':namespace'] = $namespace;
        }

        $sql .= ' ORDER BY s.namespace, s.key_name';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateLanguage(array $attributes): array
    {
        $code = $attributes['code'] ?? null;
        if (!$code) {
            throw new RuntimeException('Language code is required.');
        }

        $existing = $this->getLanguageByCode($code);
        if (!$existing) {
            throw new RuntimeException('Language not found.');
        }

        $sql = <<<SQL
UPDATE languages SET
    name = :name,
    native_name = :native_name,
    flag = :flag,
    is_default = :is_default,
    is_active = :is_active,
    sort_order = :sort_order,
    updatedAt = NOW()
WHERE code = :code
SQL;

        // If setting this as default, unset others
        if (!empty($attributes['is_default'])) {
            $this->pdo->exec("UPDATE languages SET is_default = FALSE WHERE code != '{$code}'");
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':code' => $code,
            ':name' => $attributes['name'] ?? $existing['name'],
            ':native_name' => $attributes['native_name'] ?? $existing['native_name'],
            ':flag' => $attributes['flag'] ?? $existing['flag'],
            ':is_default' => isset($attributes['is_default']) ? ($attributes['is_default'] ? 1 : 0) : $existing['is_default'],
            ':is_active' => isset($attributes['is_active']) ? ($attributes['is_active'] ? 1 : 0) : $existing['is_active'],
            ':sort_order' => $attributes['sort_order'] ?? $existing['sort_order'],
        ]);

        return $this->getLanguageByCode($code) ?? $existing;
    }
}

