<?php

declare(strict_types=1);

namespace App\Domain\Theme;

use App\Domain\Exceptions\ValidationException;
use InvalidArgumentException;

final class UserThemePreferenceService
{
    public function __construct(
        private readonly UserThemePreferenceRepository $preferenceRepository,
        private readonly ThemeRepository $themeRepository
    ) {
    }

    /**
     * Set user theme preference
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function setPreference(array $payload): array
    {
        $this->validate($payload);

        $userId = (string) $payload['user_id'];
        $themeId = (string) $payload['theme_id'];
        $scope = $payload['scope'] ?? 'public_frontend';

        // Verify theme exists and is active
        $theme = $this->themeRepository->findById($themeId);
        if (!$theme) {
            throw new InvalidArgumentException('Theme not found.');
        }

        if (!$theme['is_active']) {
            throw new InvalidArgumentException('Cannot set preference to an inactive theme.');
        }

        return $this->preferenceRepository->upsert([
            'user_id' => $userId,
            'theme_id' => $themeId,
            'scope' => $scope,
        ]);
    }

    /**
     * Get user theme preference (returns theme data)
     *
     * @return array<string, mixed>|null
     */
    public function getPreference(string $userId, string $scope = 'public_frontend'): ?array
    {
        return $this->preferenceRepository->getThemeForUser($userId, $scope);
    }

    /**
     * Remove user theme preference
     */
    public function removePreference(string $userId, string $scope = 'public_frontend'): void
    {
        $this->preferenceRepository->delete($userId, $scope);
    }

    /**
     * Validate preference data
     *
     * @param array<string, mixed> $payload
     */
    private function validate(array $payload): void
    {
        if (empty($payload['user_id'])) {
            throw new ValidationException('User ID is required.');
        }

        if (empty($payload['theme_id'])) {
            throw new ValidationException('Theme ID is required.');
        }
    }
}

