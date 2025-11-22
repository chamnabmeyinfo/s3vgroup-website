<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

use App\Domain\Content\HomepageSectionRepository;
use App\Http\AdminGuard;
use App\Http\JsonResponse;
use App\Http\Request;

AdminGuard::requireAuth();

$repository = new HomepageSectionRepository(getDB());

switch (Request::method()) {
    case 'GET':
        // Get page_id from query parameter
        $pageId = Request::query('page_id');
        $sections = $repository->all($pageId);
        JsonResponse::success(['sections' => $sections]);
        break;

    case 'POST':
        $payload = Request::json() ?? $_POST;

        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }

        try {
            // Allow page_id to be null for homepage
            $payload['page_id'] = $payload['page_id'] ?? null;
            $section = $repository->create($payload);
            JsonResponse::success(['section' => $section], 201);
        } catch (\InvalidArgumentException | \RuntimeException $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    case 'PUT':
        // Bulk sync sections: create, update, delete, reorder
        $payload = Request::json() ?? $_POST;
        
        if (!is_array($payload)) {
            JsonResponse::error('Invalid payload.', 400);
        }
        
        try {
            $pageId = $payload['page_id'] ?? Request::query('page_id') ?? null;
            
            // Get all existing sections for this page
            $existingSections = $repository->all($pageId);
            $existingIds = array_column($existingSections, 'id');
            
            // Process sections to save
            $sectionsToSave = $payload['sections'] ?? [];
            $newIds = [];
            
            $pdo = getDB();
            $pdo->beginTransaction();
            
            try {
                // Update or create sections
                foreach ($sectionsToSave as $index => $sectionData) {
                    $sectionId = $sectionData['id'] ?? null;
                    
                    // If temp ID or doesn't exist, create new
                    if (!$sectionId || ($sectionId && strpos($sectionId, 'temp_') === 0) || ($sectionId && !in_array($sectionId, $existingIds))) {
                        $sectionData['page_id'] = $pageId;
                        $sectionData['order_index'] = $index;
                        $section = $repository->create($sectionData);
                        $newIds[] = $section['id'];
                    } else {
                        // Update existing
                        $sectionData['page_id'] = $pageId;
                        $sectionData['order_index'] = $index;
                        $section = $repository->update($sectionId, $sectionData);
                        $newIds[] = $section['id'];
                    }
                }
                
                // Delete sections that were removed
                $idsToKeep = array_filter($newIds, function($id) {
                    return $id && strpos($id, 'temp_') !== 0;
                });
                $idsToDelete = array_diff($existingIds, $idsToKeep);
                
                foreach ($idsToDelete as $idToDelete) {
                    $repository->delete($idToDelete);
                }
                
                $pdo->commit();
                
                // Return updated sections list
                $updatedSections = $repository->all($pageId);
                JsonResponse::success([
                    'message' => 'Sections synced successfully.',
                    'sections' => $updatedSections
                ]);
            } catch (\Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } catch (\RuntimeException | \Exception $exception) {
            JsonResponse::error($exception->getMessage(), 422);
        }
        break;

    default:
        JsonResponse::error('Method not allowed.', 405);
}

