<?php
/**
 * Secondary Menu Widget
 * Renders menu items for secondary location (top bar, sidebar, etc.)
 * 
 * Usage: include __DIR__ . '/widgets/secondary-menu.php';
 */

if (!function_exists('getDB')) {
    require_once __DIR__ . '/../../config/database.php';
}

use App\Domain\Menus\MenuRepository;
use App\Application\Services\MenuService;

$db = getDB();
$repository = new MenuRepository($db);
$menuService = new MenuService($repository);

// Get secondary menu
$location = 'secondary';
$menu = $menuService->getMenuByLocation($location);

if ($menu) {
    $menuItems = $menuService->getMenuWithItems($menu['id'])['items'] ?? [];
} else {
    $menuItems = [];
}
?>

<?php if (!empty($menuItems)): ?>
    <nav class="secondary-menu" data-location="secondary">
        <ul class="secondary-menu-list flex items-center gap-4">
            <?php foreach ($menuItems as $item): ?>
                <li class="secondary-menu-item">
                    <a href="<?php echo e($item['url']); ?>" 
                       target="<?php echo e($item['target'] ?? '_self'); ?>"
                       class="text-sm theme-nav-link hover:text-orange-500 transition-colors">
                        <?php if (!empty($item['icon'])): ?>
                            <span class="menu-icon"><?php echo e($item['icon']); ?></span>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($item['title']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php endif; ?>

