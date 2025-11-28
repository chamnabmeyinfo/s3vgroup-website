<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Services\ProductService;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Exceptions\NotFoundException;
use App\Http\Request;
use App\Infrastructure\Logging\Logger;
use PDO;

/**
 * Product controller
 * 
 * Handles HTTP requests for product operations
 */
final class ProductController extends Controller
{
    private ProductService $service;

    public function __construct()
    {
        $db = $this->getDatabase();
        $repository = new \App\Domain\Catalog\ProductRepository($db);
        $this->service = new ProductService($repository);
    }

    /**
     * Get product by ID
     */
    public function show(string $id): void
    {
        $this->handle(function () use ($id) {
            $this->requireAuth();

            $product = $this->service->findById($id);

            if (!$product) {
                throw new NotFoundException('Product not found.');
            }

            $this->success(['product' => $product]);
        });
    }

    /**
     * Update product
     */
    public function update(string $id): void
    {
        $this->handle(function () use ($id) {
            $this->requireAuth();

            $validated = $this->validate(\App\Http\Requests\UpdateProductRequest::class);
            $product = $this->service->update($id, $validated);

            Logger::info('Product updated', [
                'product_id' => $id,
                'user_id' => \App\Http\Middleware\Authenticate::userId(),
            ]);

            $this->success(['product' => $product]);
        });
    }

    /**
     * Delete product
     */
    public function delete(string $id): void
    {
        $this->handle(function () use ($id) {
            $this->requireAuth();

            $this->service->delete($id);

            Logger::info('Product deleted', [
                'product_id' => $id,
                'user_id' => \App\Http\Middleware\Authenticate::userId(),
            ]);

            $this->success(['deleted' => true]);
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

