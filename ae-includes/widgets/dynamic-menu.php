<?php
/**
 * Dynamic Menu Widget with Mega Menu Support
 * 
 * Usage: include this file in your header
 * <?php include __DIR__ . '/ae-includes/widgets/dynamic-menu.php'; ?>
 */

if (!function_exists('getDB')) {
    require_once __DIR__ . '/../../config/database.php';
}

use App\Domain\Menus\MenuRepository;
use App\Application\Services\MenuService;

$db = getDB();
$repository = new MenuRepository($db);
$menuService = new MenuService($repository);

// Get menu by location (default: primary)
// Location can be: primary, secondary, footer, mobile
$location = $location ?? 'primary';
$menu = $menuService->getMenuByLocation($location);

if (!$menu) {
    // Fallback to default menu if location not found
    $menu = $menuService->getMenuByLocation('primary');
}

if ($menu) {
    $menuItems = $menuService->getMenuWithItems($menu['id'])['items'] ?? [];
} else {
    $menuItems = [];
}

/**
 * Render menu item recursively
 */
function renderMenuItem($item, $level = 0) {
    $hasChildren = !empty($item['children']);
    $isMega = !empty($item['is_mega_menu']);
    $url = $item['url'];
    $title = htmlspecialchars($item['title']);
    $icon = $item['icon'] ?? '';
    $target = $item['target'] ?? '_self';
    $cssClasses = $item['css_classes'] ?? '';
    
    // Build classes
    $classes = ['menu-item'];
    if ($hasChildren) {
        $classes[] = 'has-children';
    }
    if ($isMega) {
        $classes[] = 'mega-menu-item';
    }
    if ($cssClasses) {
        $classes[] = $cssClasses;
    }
    
    ?>
    <li class="<?php echo implode(' ', $classes); ?>" data-level="<?php echo $level; ?>">
        <a 
            href="<?php echo e($url); ?>" 
            target="<?php echo e($target); ?>"
            class="menu-link <?php echo $isMega ? 'mega-menu-trigger' : ''; ?>"
            <?php if ($isMega): ?>
                data-mega-columns="<?php echo e($item['mega_menu_columns'] ?? 3); ?>"
                data-mega-image="<?php echo e($item['mega_menu_image'] ?? ''); ?>"
                data-mega-content="<?php echo htmlspecialchars($item['mega_menu_content'] ?? ''); ?>"
            <?php endif; ?>
        >
            <?php if ($icon): ?>
                <span class="menu-icon"><?php echo e($icon); ?></span>
            <?php endif; ?>
            <span class="menu-text"><?php echo $title; ?></span>
            <?php if ($hasChildren && !$isMega): ?>
                <svg class="menu-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            <?php endif; ?>
        </a>
        
        <?php if ($hasChildren): ?>
            <?php if ($isMega): ?>
                <!-- Mega Menu -->
                <div class="mega-menu-dropdown">
                    <div class="mega-menu-container">
                        <?php if (!empty($item['mega_menu_image'])): ?>
                            <div class="mega-menu-image">
                                <img src="<?php echo e($item['mega_menu_image']); ?>" alt="<?php echo $title; ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="mega-menu-content" style="grid-template-columns: repeat(<?php echo e($item['mega_menu_columns'] ?? 3); ?>, 1fr);">
                            <?php foreach ($item['children'] as $child): ?>
                                <div class="mega-menu-column">
                                    <?php renderMenuItem($child, $level + 1); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (!empty($item['mega_menu_content'])): ?>
                            <div class="mega-menu-custom">
                                <?php echo $item['mega_menu_content']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Regular Dropdown -->
                <ul class="submenu">
                    <?php foreach ($item['children'] as $child): ?>
                        <?php renderMenuItem($child, $level + 1); ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    </li>
    <?php
}
?>

<?php if (!empty($menuItems)): ?>
    <nav class="dynamic-menu dynamic-menu-<?php echo e($location); ?>" data-location="<?php echo e($location); ?>">
        <ul class="menu-list menu-list-<?php echo e($location); ?>">
            <?php foreach ($menuItems as $item): ?>
                <?php renderMenuItem($item); ?>
            <?php endforeach; ?>
        </ul>
    </nav>

    <style>
        /* Dynamic Menu Styles */
        .dynamic-menu {
            position: relative;
        }

        .menu-list {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 0.5rem;
        }

        .menu-item {
            position: relative;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
            border-radius: 0.375rem;
        }

        .theme-dark .menu-link {
            color: #f9fafb;
        }

        .theme-light .menu-link {
            color: #374151;
        }

        .menu-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .theme-dark .menu-link:hover {
            color: #f97316;
        }

        .theme-light .menu-link:hover {
            color: #f97316;
        }

        .menu-icon {
            font-size: 1.125rem;
        }

        .menu-arrow {
            width: 1rem;
            height: 1rem;
            margin-left: 0.25rem;
            transition: transform 0.2s;
        }

        .menu-item:hover > .menu-link .menu-arrow {
            transform: rotate(180deg);
        }

        /* Submenu Styles */
        .submenu {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 200px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            list-style: none;
            margin: 0;
            padding: 0.5rem 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .theme-dark .submenu {
            background: #1f2937;
            border-color: #374151;
        }

        .theme-light .submenu {
            background: #ffffff;
            border-color: #e5e7eb;
        }

        .menu-item:hover > .submenu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .submenu .menu-link {
            padding: 0.75rem 1.5rem;
            color: var(--text-primary);
        }

        .theme-dark .submenu .menu-link {
            color: #f9fafb;
        }

        .theme-light .submenu .menu-link {
            color: #374151;
        }

        .submenu .menu-link:hover {
            background-color: var(--hover-bg);
        }

        .theme-dark .submenu .menu-link:hover {
            background-color: #374151;
            color: #f97316;
        }

        .theme-light .submenu .menu-link:hover {
            background-color: #f3f4f6;
            color: #f97316;
        }

        /* Mega Menu Styles */
        .mega-menu-dropdown {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 1200px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateX(-50%) translateY(-20px);
            transition: all 0.3s ease;
            z-index: 1000;
            padding: 2rem;
            margin-top: 0.5rem;
        }

        .theme-dark .mega-menu-dropdown {
            background: #1f2937;
            border-color: #374151;
        }

        .theme-light .mega-menu-dropdown {
            background: #ffffff;
            border-color: #e5e7eb;
        }

        .mega-menu-item:hover > .mega-menu-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        .mega-menu-container {
            display: grid;
            gap: 2rem;
        }

        .mega-menu-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .mega-menu-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mega-menu-content {
            display: grid;
            gap: 2rem;
        }

        .mega-menu-column {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .mega-menu-column .menu-link {
            padding: 0.75rem 1rem;
            color: #374151;
            font-weight: 500;
        }

        .mega-menu-column .menu-link:hover {
            background-color: #f3f4f6;
            color: #111827;
        }

        .mega-menu-custom {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        /* Footer Menu Styles */
        .dynamic-menu-footer .menu-list {
            flex-direction: column;
            gap: 0.5rem;
        }

        .dynamic-menu-footer .menu-link {
            color: rgba(255, 255, 255, 0.7);
            padding: 0.5rem 0;
        }

        .dynamic-menu-footer .menu-link:hover {
            color: white;
            background: transparent;
        }

        /* Secondary Menu Styles */
        .dynamic-menu-secondary .menu-list {
            gap: 1rem;
        }

        /* Mobile Menu Styles */
        @media (max-width: 768px) {
            .menu-list {
                flex-direction: column;
                gap: 0;
            }

            .submenu,
            .mega-menu-dropdown {
                position: static;
                transform: none;
                box-shadow: none;
                opacity: 1;
                visibility: visible;
                margin-left: 1rem;
                margin-top: 0.5rem;
                padding: 0.5rem 0;
                background: transparent;
            }

            .mega-menu-content {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <script>
        // Mega menu JavaScript enhancements
        document.addEventListener('DOMContentLoaded', function() {
            const megaMenuItems = document.querySelectorAll('.mega-menu-item');
            
            megaMenuItems.forEach(item => {
                const trigger = item.querySelector('.mega-menu-trigger');
                const dropdown = item.querySelector('.mega-menu-dropdown');
                
                if (trigger && dropdown) {
                    // Close on outside click
                    document.addEventListener('click', (e) => {
                        if (!item.contains(e.target)) {
                            dropdown.style.opacity = '0';
                            dropdown.style.visibility = 'hidden';
                        }
                    });
                }
            });
        });
    </script>
<?php endif; ?>

