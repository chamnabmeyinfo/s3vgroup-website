<?php
/**
 * Theme Loader - Centralized theme loading and CSS variable injection
 * 
 * This class handles all theme loading logic in one place for better
 * performance, maintainability, and reliability.
 */

class ThemeLoader {
    private static $themeCache = null;
    private static $configCache = null;
    
    /**
     * Get the active theme for backend admin
     * Uses caching to avoid multiple database queries
     */
    public static function getActiveTheme($db = null) {
        // Return cached theme if available
        if (self::$themeCache !== null) {
            return self::$themeCache;
        }
        
        // Default theme values (fallback)
        $defaultTheme = [
            'id' => 'theme_ant_elite_default',
            'slug' => 'ant-elite-default',
            'name' => 'Ant Elite Default',
            'config' => self::getDefaultConfig()
        ];
        
        // If no database connection, return default
        if (!$db) {
            self::$themeCache = $defaultTheme;
            return $defaultTheme;
        }
        
        try {
            $themeRepo = new \App\Domain\Theme\ThemeRepository($db);
            // Safely get user ID from session (session should already be started by header.php)
            $userId = 'admin_default';
            if (session_status() === PHP_SESSION_ACTIVE) {
                $userId = $_SESSION['admin_user_id'] ?? $_SESSION['user_id'] ?? 'admin_default';
            }
            
            // Try to get user preference
            $theme = null;
            try {
                $preferenceRepo = new \App\Domain\Theme\UserThemePreferenceRepository($db);
                $theme = $preferenceRepo->getThemeForUser($userId, 'backend_admin');
            } catch (\Throwable $e) {
                error_log('Theme preference error: ' . $e->getMessage());
            }
            
            // Fallback to default theme
            if (!$theme) {
                try {
                    $theme = $themeRepo->getDefault();
                } catch (\Throwable $e) {
                    error_log('Get default theme error: ' . $e->getMessage());
                }
            }
            
            // Fallback to first active theme
            if (!$theme) {
                try {
                    $activeThemes = $themeRepo->all(['is_active' => true]);
                    $theme = $activeThemes[0] ?? null;
                } catch (\Throwable $e) {
                    error_log('Get active themes error: ' . $e->getMessage());
                }
            }
            
            // Fallback to default slug
            if (!$theme) {
                try {
                    $theme = $themeRepo->findBySlug('ant-elite-default');
                } catch (\Throwable $e) {
                    error_log('Find by slug error: ' . $e->getMessage());
                }
            }
            
            // Use default if still no theme
            if (!$theme) {
                $theme = $defaultTheme;
            }
            
            // Cache the result
            self::$themeCache = $theme;
            return $theme;
            
        } catch (\Throwable $e) {
            error_log('Theme loading error: ' . $e->getMessage());
            self::$themeCache = $defaultTheme;
            return $defaultTheme;
        }
    }
    
    /**
     * Get parsed theme configuration
     */
    public static function getThemeConfig($theme = null) {
        // Return cached config if available
        if (self::$configCache !== null && $theme === null) {
            return self::$configCache;
        }
        
        if (!$theme) {
            // Get database connection
            $db = null;
            try {
                if (file_exists(__DIR__ . '/../../config/database.php')) {
                    require_once __DIR__ . '/../../config/database.php';
                    $db = getDB();
                }
            } catch (\Throwable $e) {
                error_log('Database connection error: ' . $e->getMessage());
            }
            
            $theme = self::getActiveTheme($db);
        }
        
        // Parse config
        $config = is_string($theme['config'] ?? null) 
            ? json_decode($theme['config'], true) 
            : ($theme['config'] ?? []);
        
        if (!is_array($config)) {
            $config = self::getDefaultConfig();
        }
        
        // Merge with defaults to ensure all keys exist
        $defaultConfig = self::getDefaultConfig();
        $mergedConfig = [
            'colors' => array_merge($defaultConfig['colors'], $config['colors'] ?? []),
            'typography' => array_merge($defaultConfig['typography'], $config['typography'] ?? []),
            'radius' => array_merge($defaultConfig['radius'], $config['radius'] ?? []),
            'shadows' => array_merge($defaultConfig['shadows'], $config['shadows'] ?? [])
        ];
        
        // Cache the result
        if ($theme === null) {
            self::$configCache = $mergedConfig;
        }
        
        return $mergedConfig;
    }
    
    /**
     * Get default theme configuration
     */
    private static function getDefaultConfig() {
        return [
            'colors' => [
                'background' => '#FAFBFC',
                'surface' => '#FFFFFF',
                'primary' => '#2563EB',
                'primaryText' => '#FFFFFF',
                'text' => '#1F2937',
                'mutedText' => '#6B7280',
                'border' => '#E5E7EB',
                'error' => '#DC2626',
                'success' => '#059669',
                'warning' => '#D97706',
                'accent' => '#7C3AED',
                'secondary' => '#10B981',
                'tertiary' => '#F59E0B'
            ],
            'typography' => [
                'fontFamily' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
                'headingScale' => 1.25,
                'bodySize' => 15,
                'lineHeight' => 1.6,
                'fontWeightNormal' => 400,
                'fontWeightMedium' => 500,
                'fontWeightSemibold' => 600,
                'fontWeightBold' => 700,
                'letterSpacing' => 'normal'
            ],
            'radius' => [
                'small' => 6,
                'medium' => 10,
                'large' => 16,
                'pill' => 9999
            ],
            'shadows' => [
                'card' => '0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06)',
                'elevated' => '0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)',
                'subtle' => '0 1px 2px rgba(0,0,0,0.05)',
                'button' => '0 1px 3px rgba(0,0,0,0.1)',
                'buttonHover' => '0 2px 6px rgba(0,0,0,0.15)'
            ]
        ];
    }
    
    /**
     * Generate CSS variables for theme
     */
    public static function generateCSSVariables($theme = null) {
        if (!$theme) {
            // Get database connection if not provided
            $db = null;
            try {
                if (file_exists(__DIR__ . '/../../config/database.php')) {
                    require_once __DIR__ . '/../../config/database.php';
                    $db = getDB();
                }
            } catch (\Throwable $e) {
                error_log('Database connection error in generateCSSVariables: ' . $e->getMessage());
            }
            $theme = self::getActiveTheme($db);
        }
        
        $config = self::getThemeConfig($theme);
        
        $colors = $config['colors'];
        $typography = $config['typography'];
        $radius = $config['radius'];
        $shadows = $config['shadows'];
        
        // Helper to convert hex to RGB
        $hexToRgb = function($hex) {
            if (empty($hex) || !is_string($hex)) {
                return '0, 122, 255';
            }
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) == 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
            }
            if (strlen($hex) != 6 || !preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
                return '0, 122, 255';
            }
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return "$r, $g, $b";
        };
        
        // Get theme slug
        $themeSlug = $theme['slug'] ?? 'ant-elite-default';
        if (empty($themeSlug) && isset($theme['id'])) {
            $id = $theme['id'];
            if (strpos($id, 'theme_') === 0) {
                $themeSlug = substr($id, 6);
            } else {
                $themeSlug = $id;
            }
        }
        
        // Generate CSS
        ob_start();
        ?>
        <style id="theme-variables">
        body[data-theme="<?php echo htmlspecialchars($themeSlug); ?>"] {
            /* Theme-specific body class */
        }
        
        :root {
            /* Theme Colors */
            --theme-bg: <?php echo htmlspecialchars($colors['background']); ?>;
            --theme-surface: <?php echo htmlspecialchars($colors['surface']); ?>;
            --theme-primary: <?php echo htmlspecialchars($colors['primary']); ?>;
            --theme-primary-text: <?php echo htmlspecialchars($colors['primaryText']); ?>;
            --theme-text: <?php echo htmlspecialchars($colors['text']); ?>;
            --theme-text-muted: <?php echo htmlspecialchars($colors['mutedText']); ?>;
            --theme-border: <?php echo htmlspecialchars($colors['border']); ?>;
            --theme-error: <?php echo htmlspecialchars($colors['error']); ?>;
            --theme-success: <?php echo htmlspecialchars($colors['success']); ?>;
            --theme-warning: <?php echo htmlspecialchars($colors['warning']); ?>;
            --theme-accent: <?php echo htmlspecialchars($colors['accent'] ?? '#7C3AED'); ?>;
            --theme-secondary: <?php echo htmlspecialchars($colors['secondary'] ?? '#10B981'); ?>;
            --theme-tertiary: <?php echo htmlspecialchars($colors['tertiary'] ?? '#F59E0B'); ?>;
            
            /* RGB values for rgba() usage */
            --theme-primary-rgb: <?php echo $hexToRgb($colors['primary']); ?>;
            --theme-success-rgb: <?php echo $hexToRgb($colors['success']); ?>;
            --theme-error-rgb: <?php echo $hexToRgb($colors['error']); ?>;
            --theme-warning-rgb: <?php echo $hexToRgb($colors['warning']); ?>;
            --theme-accent-rgb: <?php echo $hexToRgb($colors['accent'] ?? '#7C3AED'); ?>;
            --theme-secondary-rgb: <?php echo $hexToRgb($colors['secondary'] ?? '#10B981'); ?>;
            --theme-tertiary-rgb: <?php echo $hexToRgb($colors['tertiary'] ?? '#F59E0B'); ?>;
            
            /* Typography */
            --theme-font-family: <?php echo htmlspecialchars($typography['fontFamily']); ?>;
            --theme-body-size: <?php echo htmlspecialchars($typography['bodySize']); ?>px;
            --theme-line-height: <?php echo htmlspecialchars($typography['lineHeight']); ?>;
            --theme-heading-scale: <?php echo htmlspecialchars($typography['headingScale']); ?>;
            --theme-font-weight-normal: <?php echo htmlspecialchars($typography['fontWeightNormal']); ?>;
            --theme-font-weight-medium: <?php echo htmlspecialchars($typography['fontWeightMedium']); ?>;
            --theme-font-weight-semibold: <?php echo htmlspecialchars($typography['fontWeightSemibold']); ?>;
            --theme-font-weight-bold: <?php echo htmlspecialchars($typography['fontWeightBold']); ?>;
            --theme-letter-spacing: <?php echo htmlspecialchars($typography['letterSpacing']); ?>;
            
            /* Radius */
            --theme-radius-sm: <?php echo htmlspecialchars($radius['small']); ?>px;
            --theme-radius-md: <?php echo htmlspecialchars($radius['medium']); ?>px;
            --theme-radius-lg: <?php echo htmlspecialchars($radius['large']); ?>px;
            --theme-radius-pill: <?php echo htmlspecialchars($radius['pill']); ?>px;
            
            /* Shadows */
            --theme-shadow-card: <?php echo htmlspecialchars($shadows['card']); ?>;
            --theme-shadow-elevated: <?php echo htmlspecialchars($shadows['elevated']); ?>;
            --theme-shadow-subtle: <?php echo htmlspecialchars($shadows['subtle']); ?>;
            --theme-shadow-button: <?php echo htmlspecialchars($shadows['button']); ?>;
            --theme-shadow-button-hover: <?php echo htmlspecialchars($shadows['buttonHover']); ?>;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get theme slug for body data attribute
     */
    public static function getThemeSlug($theme = null) {
        if (!$theme) {
            // Get database connection if not provided
            $db = null;
            try {
                if (file_exists(__DIR__ . '/../../config/database.php')) {
                    require_once __DIR__ . '/../../config/database.php';
                    $db = getDB();
                }
            } catch (\Throwable $e) {
                error_log('Database connection error in getThemeSlug: ' . $e->getMessage());
            }
            $theme = self::getActiveTheme($db);
        }
        
        $themeSlug = $theme['slug'] ?? 'ant-elite-default';
        if (empty($themeSlug) && isset($theme['id'])) {
            $id = $theme['id'];
            if (strpos($id, 'theme_') === 0) {
                $themeSlug = substr($id, 6);
            } else {
                $themeSlug = $id;
            }
        }
        
        return $themeSlug;
    }
    
    /**
     * Clear cache (useful after theme changes)
     */
    public static function clearCache() {
        self::$themeCache = null;
        self::$configCache = null;
    }
}

