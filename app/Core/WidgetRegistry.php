<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Widget Registry - WordPress-like Widgets
 */
final class WidgetRegistry
{
    private static array $widgets = [];
    private static array $sidebars = [];

    /**
     * Register a widget
     * WordPress: register_widget($widget_class)
     */
    public static function register(string $id, string $name, callable $callback, array $options = []): void
    {
        $defaults = [
            'description' => '',
            'classname' => '',
        ];

        self::$widgets[$id] = array_merge($defaults, $options, [
            'id' => $id,
            'name' => $name,
            'callback' => $callback,
        ]);

        HookSystem::doAction('widgets_init');
    }

    /**
     * Register a sidebar
     * WordPress: register_sidebar($args)
     */
    public static function registerSidebar(array $args = []): string
    {
        $defaults = [
            'name' => 'Sidebar',
            'id' => 'sidebar-' . (count(self::$sidebars) + 1),
            'description' => '',
            'class' => '',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h2 class="widgettitle">',
            'after_title' => '</h2>',
        ];

        $sidebar = array_merge($defaults, $args);
        self::$sidebars[$sidebar['id']] = $sidebar;

        HookSystem::doAction('register_sidebar', $sidebar);

        return $sidebar['id'];
    }

    /**
     * Get registered widgets
     */
    public static function getWidgets(): array
    {
        return self::$widgets;
    }

    /**
     * Get registered sidebars
     */
    public static function getSidebars(): array
    {
        return self::$sidebars;
    }

    /**
     * Render a widget
     */
    public static function renderWidget(string $id, array $instance = []): string
    {
        if (!isset(self::$widgets[$id])) {
            return '';
        }

        $widget = self::$widgets[$id];
        ob_start();
        call_user_func($widget['callback'], $instance);
        return ob_get_clean();
    }

    /**
     * Render sidebar
     */
    public static function renderSidebar(string $sidebarId): string
    {
        if (!isset(self::$sidebars[$sidebarId])) {
            return '';
        }

        $sidebar = self::$sidebars[$sidebarId];
        // In real implementation, would load widget instances from database
        // For now, just return sidebar structure
        return '';
    }
}

