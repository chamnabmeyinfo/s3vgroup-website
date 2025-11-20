<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = getDB();
$categories = $db->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.categoryId 
    GROUP BY c.id 
    ORDER BY c.priority DESC, c.name ASC
")->fetchAll();

$pageTitle = 'Categories';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Catalog</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Categories</h1>
            <p class="text-sm text-gray-600">Manage product categories and their priority</p>
        </div>
        <a href="/admin/categories/new.php" class="inline-flex items-center rounded-full bg-[#0b3a63] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1a5a8a]">
            + New category
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Slug</th>
                    <th class="px-6 py-3 font-medium">Priority</th>
                    <th class="px-6 py-3 font-medium">Products</th>
                    <th class="px-6 py-3 font-medium">Updated</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td class="px-6 py-4 font-semibold"><?php echo e($category['name']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo e($category['slug']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo e($category['priority']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo e($category['product_count']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($category['updatedAt'])); ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="/admin/categories/edit.php?id=<?php echo urlencode($category['id']); ?>" class="text-sm font-medium text-[#0b3a63] hover:underline">
                                Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
