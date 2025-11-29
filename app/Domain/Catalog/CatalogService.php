<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

final class CatalogService
{
    /** @var CategoryRepository */
    private $categories;

    /** @var ProductRepository */
    private $products;

    public function __construct(
        CategoryRepository $categories,
        ProductRepository $products
    ) {
        $this->categories = $categories;
        $this->products = $products;
    }

    public function featured(int $limit = 6): array
    {
        return [
            'categories' => $this->categories->featured($limit),
            'products'   => $this->products->featured($limit),
        ];
    }

    public function categories(): array
    {
        return $this->categories->all();
    }

    public function products(?string $categorySlug = null, int $limit = 50, int $offset = 0): array
    {
        return $this->products->paginate($categorySlug, $limit, $offset);
    }

    public function productBySlug(string $slug): ?array
    {
        return $this->products->findBySlug($slug);
    }
}

