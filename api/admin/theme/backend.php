<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Theme\ThemeRepository;
use App\Domain\Theme\ThemeService;
use App\Domain\Theme\UserThemePreferenceRepository;
use App\Domain\Theme\UserThemePreferenceService;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$db = getDB();
$repository = new ThemeRepository($db);
$service = new ThemeService($repository);

// Get current admin user ID from session
$userId = $_SESSION['admin_user_id'] ?? $_SESSION['user_id'] ?? 'admin_default';

// Initialize user preference service
$preferenceRepository = new UserThemePreferenceRepository($db);
$preferenceService = new UserThemePreferenceService($preferenceRepository, $repository);

$scope = 'backend_admin';

switch (Request::method()) {
    case 'GET':
        // Get current backend theme preference
        try {
            $preference = $preferenceService->getPreference($userId, $scope);
            if ($preference) {
                JsonResponse::success(['theme' => $preference]);
            } else {
                // Fallback to default theme
                $theme = $service->getEffectiveTheme(null, $scope);
                JsonResponse::success(['theme' => $theme]);
            }
        } catch (\InvalidArgumentException $e) {
            JsonResponse::error($e->getMessage(), 404);
        }
        break;

    case 'POST':
    case 'PUT':
        // Set backend theme preference
        $payload = Request::json() ?? $_POST;

        if (!isset($payload['theme_id'])) {
            JsonResponse::error('Theme ID is required.', 400);
        }

        try {
            // Verify theme exists first
            $theme = $repository->findById($payload['theme_id']);
            if (!$theme) {
                JsonResponse::error('Theme not found. Please refresh the page and try again.', 404);
            }
            
            if (!$theme['is_active']) {
                JsonResponse::error('Cannot activate an inactive theme.', 400);
            }
            
            $preference = $preferenceService->setPreference([
                'user_id' => $userId,
                'theme_id' => $payload['theme_id'],
                'scope' => $scope,
            ]);

            // Clear theme cache
            require_once __DIR__ . '/../../../ae-admin/includes/theme-loader.php';
            ThemeLoader::clearCache();

            // Get full theme data
            $theme = $repository->findById($preference['theme_id']);
            JsonResponse::success(['theme' => $theme]);
        } catch (\InvalidArgumentException $e) {
            error_log('Theme activation error: ' . $e->getMessage());
            JsonResponse::error($e->getMessage(), 400);
        } catch (\App\Domain\Exceptions\ValidationException $e) {
            error_log('Theme validation error: ' . $e->getMessage());
            JsonResponse::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log('Unexpected theme activation error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            JsonResponse::error('An error occurred while activating the theme: ' . $e->getMessage(), 500);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

