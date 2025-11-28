<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Services\SiteOptionService;
use App\Domain\Exceptions\NotFoundException;
use App\Domain\Settings\SiteOptionRepository;
use App\Http\Request;
use App\Infrastructure\Logging\Logger;
use PDO;

/**
 * Site option controller
 * 
 * Handles HTTP requests for site option operations
 */
final class SiteOptionController extends Controller
{
    private SiteOptionService $service;

    public function __construct()
    {
        $db = $this->getDatabase();
        $repository = new SiteOptionRepository($db);
        $this->service = new SiteOptionService($repository);
    }

    /**
     * Get all site options (grouped)
     */
    public function index(): void
    {
        $this->handle(function () {
            $this->requireAuth();

            $group = Request::query('group');
            
            if ($group) {
                $options = $this->service->getByGroup((string) $group);
                $this->success(['options' => $options, 'group' => $group]);
            } else {
                $grouped = $this->service->getGrouped();
                $this->success(['options' => $grouped]);
            }
        });
    }

    /**
     * Get single site option by ID
     */
    public function show(string $id): void
    {
        $this->handle(function () use ($id) {
            $this->requireAuth();

            $option = $this->service->findById($id);

            if (!$option) {
                throw new NotFoundException('Site option not found.');
            }

            $this->success(['option' => $option]);
        });
    }

    /**
     * Update site option
     */
    public function update(string $id): void
    {
        $this->handle(function () use ($id) {
            $this->requireAuth();

            $validated = $this->validate(\App\Http\Requests\UpdateSiteOptionRequest::class);
            $option = $this->service->update($id, $validated);

            Logger::info('Site option updated', [
                'option_id' => $id,
                'key' => $option['key_name'] ?? null,
                'user_id' => \App\Http\Middleware\Authenticate::userId(),
            ]);

            $this->success(['option' => $option]);
        });
    }

    /**
     * Bulk update site options
     */
    public function bulkUpdate(): void
    {
        $this->handle(function () {
            $this->requireAuth();

            $request = new \App\Http\Requests\BulkUpdateSiteOptionsRequest();
            $request->validated(); // Validate structure
            
            $bulkOptions = $request->getBulkOptions();
            $this->service->bulkUpdate($bulkOptions);

            Logger::info('Site options bulk updated', [
                'count' => count($bulkOptions),
                'user_id' => \App\Http\Middleware\Authenticate::userId(),
            ]);

            $this->success([
                'message' => 'Options updated successfully.',
                'count' => count($bulkOptions),
            ]);
        });
    }

    /**
     * Get database connection
     */
    private function getDatabase(): PDO
    {
        if (function_exists('getDB')) {
            return getDB();
        }

        return \App\Database\Connection::getInstance();
    }
}

