<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = getDB();
$products = $db->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.categoryId = c.id 
    ORDER BY p.updatedAt DESC 
    LIMIT 25
")->fetchAll();

$pageTitle = 'Products';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Catalog</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Products</h1>
            <p class="text-sm text-gray-600">Manage published systems and drafts</p>
        </div>
        <a href="/admin/products/new.php" class="inline-flex items-center rounded-full bg-[#0b3a63] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1a5a8a]">
            + New product
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-6 py-3 font-medium"></th>
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Category</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Updated</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <?php if ($product['heroImage']): ?>
                                <img src="<?php echo e($product['heroImage']); ?>" alt="<?php echo e($product['name']); ?>" class="h-10 w-10 rounded-lg object-cover">
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 font-semibold"><?php echo e($product['name']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo e($product['category_name'] ?? '—'); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?php 
                                echo $product['status'] === 'PUBLISHED' ? 'bg-green-100 text-green-800' : 
                                    ($product['status'] === 'DRAFT' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800'); 
                            ?>">
                                <?php echo e($product['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($product['updatedAt'])); ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="/admin/products/edit.php?id=<?php echo urlencode($product['id']); ?>" class="text-sm font-medium text-[#0b3a63] hover:underline">
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
