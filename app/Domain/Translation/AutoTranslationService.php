<?php

declare(strict_types=1);

namespace App\Domain\Translation;

use Exception;

final class AutoTranslationService
{
    private const GOOGLE_TRANSLATE_API_URL = 'https://translation.googleapis.com/language/translate/v2';
    private const LIBRE_TRANSLATE_API_URL = 'https://libretranslate.com/translate';

    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly string $provider = 'google' // 'google' or 'libre'
    ) {
    }

    /**
     * Translate text using Google Translate API
     */
    public function translateWithGoogle(string $text, string $sourceLang, string $targetLang): ?string
    {
        if (!$this->apiKey) {
            return null;
        }

        try {
            $url = self::GOOGLE_TRANSLATE_API_URL . '?key=' . urlencode($this->apiKey);
            
            $data = [
                'q' => $text,
                'source' => $sourceLang,
                'target' => $targetLang,
                'format' => 'text',
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) {
                error_log("Google Translate API error: HTTP $httpCode - $response");
                return null;
            }

            $result = json_decode($response, true);
            
            if (isset($result['data']['translations'][0]['translatedText'])) {
                return $result['data']['translations'][0]['translatedText'];
            }

            return null;
        } catch (Exception $e) {
            error_log("Google Translate API exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Translate text using LibreTranslate (free, open-source)
     */
    public function translateWithLibre(string $text, string $sourceLang, string $targetLang): ?string
    {
        try {
            $data = [
                'q' => $text,
                'source' => $sourceLang,
                'target' => $targetLang,
                'format' => 'text',
            ];

            $ch = curl_init(self::LIBRE_TRANSLATE_API_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) {
                error_log("LibreTranslate API error: HTTP $httpCode - $response");
                return null;
            }

            $result = json_decode($response, true);
            
            if (isset($result['translatedText'])) {
                return $result['translatedText'];
            }

            return null;
        } catch (Exception $e) {
            error_log("LibreTranslate API exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Translate text using the configured provider
     */
    public function translate(string $text, string $sourceLang, string $targetLang): ?string
    {
        if ($this->provider === 'google' && $this->apiKey) {
            return $this->translateWithGoogle($text, $sourceLang, $targetLang);
        }

        // Fallback to LibreTranslate (free, no API key needed)
        return $this->translateWithLibre($text, $sourceLang, $targetLang);
    }

    /**
     * Translate multiple texts in batch
     */
    public function translateBatch(array $texts, string $sourceLang, string $targetLang): array
    {
        $results = [];
        
        foreach ($texts as $text) {
            $translated = $this->translate($text, $sourceLang, $targetLang);
            $results[] = $translated ?? $text;
            
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second
        }

        return $results;
    }
}

