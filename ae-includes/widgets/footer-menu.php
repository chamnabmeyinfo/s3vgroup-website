<?php
/**
 * Footer Menu Widget
 * Renders menu items for footer location
 * 
 * Usage: include __DIR__ . '/widgets/footer-menu.php';
 */

if (!function_exists('getDB')) {
    require_once __DIR__ . '/../../config/database.php';
}

use App\Domain\Menus\MenuRepository;
use App\Application\Services\MenuService;

$db = getDB();
$repository = new MenuRepository($db);
$menuService = new MenuService($repository);

// Get footer menu
$location = 'footer';
$menu = $menuService->getMenuByLocation($location);

if ($menu) {
    $menuItems = $menuService->getMenuWithItems($menu['id'])['items'] ?? [];
} else {
    $menuItems = [];
}

/**
 * Render footer menu item recursively
 */
function renderFooterMenuItem($item, $level = 0) {
    $hasChildren = !empty($item['children']);
    $url = $item['url'];
    $title = htmlspecialchars($item['title']);
    $target = $item['target'] ?? '_self';
    
    if ($level === 0) {
        // Top level items - render as column
        ?>
        <div class="footer-menu-column">
            <?php if ($hasChildren): ?>
                <h3 class="font-semibold mb-4 text-white"><?php echo $title; ?></h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <?php foreach ($item['children'] as $child): ?>
                        <li>
                            <a href="<?php echo e($child['url']); ?>" 
                               target="<?php echo e($child['target'] ?? '_self'); ?>"
                               class="hover:text-white transition-colors">
                                <?php echo htmlspecialchars($child['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <h3 class="font-semibold mb-4 text-white"><?php echo $title; ?></h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li>
                        <a href="<?php echo e($url); ?>" 
                           target="<?php echo e($target); ?>"
                           class="hover:text-white transition-colors">
                            <?php echo $title; ?>
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>

<?php if (!empty($menuItems)): ?>
    <div class="footer-menu-wrapper grid md:grid-cols-4 gap-8">
        <?php foreach ($menuItems as $item): ?>
            <?php renderFooterMenuItem($item); ?>
        <?php endforeach; ?>
    </div>
    <style>
        .footer-menu-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .footer-menu-column h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: white;
        }
        
        .footer-menu-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-menu-column ul li {
            margin-bottom: 0.5rem;
        }
        
        .footer-menu-column a {
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer-menu-column a:hover {
            color: #f97316;
        }
        
        @media (max-width: 768px) {
            .footer-menu-wrapper {
                grid-template-columns: 1fr;
            }
        }
    </style>
<?php endif; ?>

