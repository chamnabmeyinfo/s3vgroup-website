<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Theme\ThemeRepository;
use App\Domain\Theme\ThemeService;
use App\Domain\Theme\UserThemePreferenceService;
use App\Http\Requests\CreateThemeRequest;
use App\Http\Requests\UpdateThemeRequest;

final class ThemeController extends Controller
{
    /** @var ThemeRepository */
    private $repository;

    /** @var ThemeService */
    private $service;

    /** @var UserThemePreferenceService|null */
    private $preferenceService;

    public function __construct(
        ThemeRepository $repository,
        ThemeService $service,
        ?UserThemePreferenceService $preferenceService = null
    ) {
        $this->repository = $repository;
        $this->service = $service;
        $this->preferenceService = $preferenceService;
    }

    /**
     * List all themes
     */
    public function index(): void
    {
        $this->handle(function () {
            $filters = [
                'is_active' => $this->request()->query('is_active') !== null
                    ? filter_var($this->request()->query('is_active'), FILTER_VALIDATE_BOOLEAN)
                    : null,
                'is_default' => $this->request()->query('is_default') !== null
                    ? filter_var($this->request()->query('is_default'), FILTER_VALIDATE_BOOLEAN)
                    : null,
            ];

            $themes = $this->repository->all($filters);

            $this->success(['themes' => $themes]);
        });
    }

    /**
     * Get single theme by ID or slug
     */
    public function show(string $identifier): void
    {
        $this->handle(function () use ($identifier) {
            $theme = $this->repository->findById($identifier)
                ?? $this->repository->findBySlug($identifier);

            if (!$theme) {
                $this->error('Theme not found.', 404);
                return;
            }

            $this->success(['theme' => $theme]);
        });
    }

    /**
     * Create new theme
     */
    public function store(): void
    {
        $this->handle(function () {
            $this->requireAuth();

            $data = $this->validate(CreateThemeRequest::class);

            try {
                $theme = $this->service->create($data);
                $this->success(['theme' => $theme], 201);
            } catch (\App\Domain\Exceptions\ConflictException $e) {
                $this->error($e->getMessage(), 409);
            } catch (\App\Domain\Exceptions\ValidationException $e) {
                $this->error($e->getMessage(), 422, 'VALIDATION_ERROR', $e->hasErrors() ? ['fields' => $e->getErrors()] : []);
            }
        });
    }

    /**
     * Update theme
     */
    public function update(string $identifier): void
    {
        $this->handle(function () use ($identifier) {
            $this->requireAuth();

            $data = $this->validate(UpdateThemeRequest::class);

            try {
                $theme = $this->repository->findById($identifier)
                    ?? $this->repository->findBySlug($identifier);

                if (!$theme) {
                    $this->error('Theme not found.', 404);
                    return;
                }

                $updated = $this->service->update($theme['id'], $data);
                $this->success(['theme' => $updated]);
            } catch (\App\Domain\Exceptions\ConflictException $e) {
                $this->error($e->getMessage(), 409);
            } catch (\App\Domain\Exceptions\ValidationException $e) {
                $this->error($e->getMessage(), 422, 'VALIDATION_ERROR', $e->hasErrors() ? ['fields' => $e->getErrors()] : []);
            } catch (\InvalidArgumentException $e) {
                $this->error($e->getMessage(), 400);
            }
        });
    }

    /**
     * Delete theme (soft delete)
     */
    public function destroy(string $identifier): void
    {
        $this->handle(function () use ($identifier) {
            $this->requireAuth();

            $theme = $this->repository->findById($identifier)
                ?? $this->repository->findBySlug($identifier);

            if (!$theme) {
                $this->error('Theme not found.', 404);
                return;
            }

            try {
                $this->service->delete($theme['id']);
                $this->success(['message' => 'Theme deleted successfully.']);
            } catch (\App\Domain\Exceptions\ConflictException $e) {
                $this->error($e->getMessage(), 409);
            } catch (\InvalidArgumentException $e) {
                $this->error($e->getMessage(), 400);
            }
        });
    }

    /**
     * Set theme as default
     */
    public function setDefault(string $identifier): void
    {
        $this->handle(function () use ($identifier) {
            $this->requireAuth();

            $theme = $this->repository->findById($identifier)
                ?? $this->repository->findBySlug($identifier);

            if (!$theme) {
                $this->error('Theme not found.', 404);
                return;
            }

            try {
                $this->service->setAsDefault($theme['id']);
                $updated = $this->repository->findById($theme['id']);
                $this->success(['theme' => $updated]);
            } catch (\App\Domain\Exceptions\ConflictException $e) {
                $this->error($e->getMessage(), 409);
            } catch (\InvalidArgumentException $e) {
                $this->error($e->getMessage(), 400);
            }
        });
    }

    /**
     * Get active theme for current user/context
     */
    public function active(): void
    {
        $this->handle(function () {
            // TODO: Get user ID from session/auth if available
            $userId = $this->getUserId();
            $scope = $this->request()->query('scope', 'public_frontend');

            try {
                // If user preference service is available and user is logged in, try to get user preference
                if ($this->preferenceService && $userId) {
                    $preference = $this->preferenceService->getPreference($userId, $scope);
                    if ($preference) {
                        $this->success(['theme' => $preference]);
                        return;
                    }
                }

                // Fallback to default theme
                $theme = $this->service->getEffectiveTheme($userId, $scope);
                $this->success(['theme' => $theme]);
            } catch (\InvalidArgumentException $e) {
                $this->error($e->getMessage(), 404);
            }
        });
    }

    /**
     * List public active themes
     */
    public function public(): void
    {
        $this->handle(function () {
            $themes = $this->repository->active();
            $this->success(['themes' => $themes]);
        });
    }

    /**
     * Get request instance
     */
    private function request(): \App\Http\Request
    {
        return new \App\Http\Request();
    }

    /**
     * Get current user ID (if authenticated)
     *
     * @return string|null
     */
    private function getUserId(): ?string
    {
        // TODO: Implement based on your authentication system
        // For now, return null (no user-specific themes)
        return null;
    }
}

