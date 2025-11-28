<?php

declare(strict_types=1);

namespace App\Domain\Theme;

use App\Domain\Exceptions\ConflictException;
use App\Domain\Exceptions\ValidationException;
use App\Support\Str;
use InvalidArgumentException;

final class ThemeService
{
    public function __construct(private readonly ThemeRepository $repository)
    {
    }

    /**
     * Create a new theme
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $data = $this->validate($payload);

        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Check slug uniqueness
        if ($this->repository->slugExists($data['slug'])) {
            throw new ConflictException("A theme with slug '{$data['slug']}' already exists.");
        }

        // Validate config structure
        $this->validateConfig($data['config']);

        $theme = $this->repository->create($data);

        // Set as default if requested (after creation so we have the ID)
        if ($data['is_default']) {
            $this->repository->setAsDefault($theme['id']);
            $theme['is_default'] = true;
        }

        return $theme;
    }

    /**
     * Update a theme
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function update(string $id, array $payload): array
    {
        $existing = $this->repository->findById($id);

        if (!$existing) {
            throw new InvalidArgumentException('Theme not found.');
        }

        $data = $this->validate($payload, true);

        // Generate slug if name changed and slug not provided
        if (isset($data['name']) && !isset($data['slug']) && $data['name'] !== $existing['name']) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Check slug uniqueness (excluding current theme)
        if (isset($data['slug']) && $this->repository->slugExists($data['slug'], $id)) {
            throw new ConflictException("A theme with slug '{$data['slug']}' already exists.");
        }

        // Validate config structure if provided (only for updates, merge with existing)
        if (isset($data['config'])) {
            // For updates, merge with existing config to allow partial updates
            $existingConfig = is_string($existing['config']) 
                ? json_decode($existing['config'], true) 
                : ($existing['config'] ?? []);
            
            if (!is_array($existingConfig)) {
                $existingConfig = [];
            }
            
            if (!is_array($data['config'])) {
                throw new ValidationException('Config must be an array.');
            }
            
            // Merge configs - use array_merge for top level, then merge nested arrays properly
            $mergedConfig = array_merge($existingConfig, $data['config']);
            
            // Fix nested arrays - merge each section properly
            foreach (['colors', 'typography', 'radius', 'shadows'] as $key) {
                if (isset($data['config'][$key]) && is_array($data['config'][$key])) {
                    $mergedConfig[$key] = array_merge(
                        $existingConfig[$key] ?? [],
                        $data['config'][$key]
                    );
                } elseif (!isset($mergedConfig[$key]) && isset($existingConfig[$key])) {
                    $mergedConfig[$key] = $existingConfig[$key];
                }
            }
            
            // Ensure required sections exist
            if (!isset($mergedConfig['colors'])) {
                $mergedConfig['colors'] = $existingConfig['colors'] ?? [];
            }
            if (!isset($mergedConfig['typography'])) {
                $mergedConfig['typography'] = $existingConfig['typography'] ?? [];
            }
            if (!isset($mergedConfig['radius'])) {
                $mergedConfig['radius'] = $existingConfig['radius'] ?? [];
            }
            
            // Validate the merged config
            $this->validateConfig($mergedConfig);
            $data['config'] = $mergedConfig;
        }

        // Prevent deactivating the last active theme
        if (isset($data['is_active']) && !$data['is_active']) {
            $activeCount = $this->repository->countActive();
            if ($activeCount <= 1 && $existing['is_active']) {
                throw new ConflictException('Cannot deactivate the last active theme.');
            }
        }

        // Prevent deleting the last default theme
        if (isset($data['is_default']) && !$data['is_default'] && $existing['is_default']) {
            $defaultCount = $this->repository->countDefault();
            if ($defaultCount <= 1) {
                throw new ConflictException('Cannot remove default status from the last default theme.');
            }
        }

        // If setting as default, unset other defaults
        if (isset($data['is_default']) && $data['is_default'] && !$existing['is_default']) {
            $this->repository->setAsDefault($id);
            // Don't pass is_default to update since we already handled it
            unset($data['is_default']);
        }

        return $this->repository->update($id, $data);
    }

    /**
     * Set a theme as default
     */
    public function setAsDefault(string $id): void
    {
        $theme = $this->repository->findById($id);

        if (!$theme) {
            throw new InvalidArgumentException('Theme not found.');
        }

        if (!$theme['is_active']) {
            throw new ConflictException('Cannot set an inactive theme as default.');
        }

        $this->repository->setAsDefault($id);
    }

    /**
     * Delete a theme (soft delete)
     */
    public function delete(string $id): void
    {
        $theme = $this->repository->findById($id);

        if (!$theme) {
            throw new InvalidArgumentException('Theme not found.');
        }

        // Prevent deleting the last active theme
        $activeCount = $this->repository->countActive();
        if ($activeCount <= 1 && $theme['is_active']) {
            throw new ConflictException('Cannot delete the last active theme.');
        }

        // Prevent deleting the last default theme
        if ($theme['is_default']) {
            $defaultCount = $this->repository->countDefault();
            if ($defaultCount <= 1) {
                throw new ConflictException('Cannot delete the last default theme.');
            }
        }

        $this->repository->delete($id);
    }

    /**
     * Get the effective theme for a user/context
     *
     * @param string|null $userId
     * @param string $scope
     * @return array<string, mixed>
     */
    public function getEffectiveTheme(?string $userId = null, string $scope = 'public_frontend'): array
    {
        // TODO: If user theme preferences are implemented, check here first
        // For now, return the default theme

        $default = $this->repository->getDefault();

        if (!$default) {
            // Fallback: get first active theme
            $active = $this->repository->all(['is_active' => true]);
            if (empty($active)) {
                throw new InvalidArgumentException('No active theme found.');
            }
            return $active[0];
        }

        return $default;
    }

    /**
     * Validate theme data
     *
     * @param array<string, mixed> $payload
     * @param bool $partial
     * @return array<string, mixed>
     */
    private function validate(array $payload, bool $partial = false): array
    {
        if (!$partial) {
            $this->assertRequired($payload, ['name']);
        }

        $data = [];

        if (isset($payload['name'])) {
            $name = trim((string) $payload['name']);
            if (empty($name)) {
                throw new ValidationException('Theme name cannot be empty.');
            }
            $data['name'] = $name;
        }

        if (isset($payload['slug'])) {
            $slug = Str::slug((string) $payload['slug']);
            if (empty($slug)) {
                throw new ValidationException('Theme slug cannot be empty.');
            }
            $data['slug'] = $slug;
        }

        if (isset($payload['description'])) {
            $data['description'] = $payload['description'] !== null ? (string) $payload['description'] : null;
        }

        if (isset($payload['is_default'])) {
            $data['is_default'] = (bool) $payload['is_default'];
        }

        if (isset($payload['is_active'])) {
            $data['is_active'] = (bool) $payload['is_active'];
        }

        if (isset($payload['config'])) {
            if (!is_array($payload['config'])) {
                throw new ValidationException('Theme config must be an object/array.');
            }
            $data['config'] = $payload['config'];
        }

        return $data;
    }

    /**
     * Validate theme config structure
     *
     * @param array<string, mixed> $config
     */
    private function validateConfig(array $config): void
    {
        $required = ['colors', 'typography', 'radius'];

        foreach ($required as $key) {
            if (!isset($config[$key]) || !is_array($config[$key])) {
                throw new ValidationException("Theme config must include '{$key}' object.");
            }
        }

        // Validate colors
        $requiredColors = ['background', 'surface', 'primary', 'text'];
        foreach ($requiredColors as $color) {
            if (!isset($config['colors'][$color])) {
                throw new ValidationException("Theme config colors must include '{$color}'.");
            }
        }
    }

    /**
     * Assert required fields are present
     *
     * @param array<string, mixed> $payload
     * @param array<int, string> $fields
     */
    private function assertRequired(array $payload, array $fields): void
    {
        foreach ($fields as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                throw new ValidationException("Field '{$field}' is required.");
            }
        }
    }
}

