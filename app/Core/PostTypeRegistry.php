<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Post Type Registry - WordPress-like Custom Post Types
 */
final class PostTypeRegistry
{
    private static array $postTypes = [];

    /**
     * Register a post type
     * WordPress: register_post_type($post_type, $args)
     */
    public static function register(string $postType, array $args = []): void
    {
        $defaults = [
            'label' => ucfirst($postType),
            'labels' => [],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => $postType],
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'menu_icon' => null,
            'supports' => ['title', 'editor'],
            'show_in_rest' => false,
        ];

        $args = array_merge($defaults, $args);
        
        // Generate labels
        if (empty($args['labels'])) {
            $args['labels'] = self::generateLabels($postType, $args['label']);
        }

        self::$postTypes[$postType] = $args;
        
        // Trigger registration hook
        HookSystem::doAction('register_post_type', $postType, $args);
    }

    /**
     * Get registered post types
     */
    public static function getPostTypes(): array
    {
        return self::$postTypes;
    }

    /**
     * Get post type
     */
    public static function getPostType(string $postType): ?array
    {
        return self::$postTypes[$postType] ?? null;
    }

    /**
     * Check if post type exists
     */
    public static function postTypeExists(string $postType): bool
    {
        return isset(self::$postTypes[$postType]);
    }

    /**
     * Generate labels for post type
     */
    private static function generateLabels(string $postType, string $singular): array
    {
        $plural = $singular . 's';
        
        return [
            'name' => $plural,
            'singular_name' => $singular,
            'add_new' => 'Add New',
            'add_new_item' => 'Add New ' . $singular,
            'edit_item' => 'Edit ' . $singular,
            'new_item' => 'New ' . $singular,
            'view_item' => 'View ' . $singular,
            'view_items' => 'View ' . $plural,
            'search_items' => 'Search ' . $plural,
            'not_found' => 'No ' . strtolower($plural) . ' found',
            'not_found_in_trash' => 'No ' . strtolower($plural) . ' found in trash',
            'all_items' => 'All ' . $plural,
            'archives' => $singular . ' Archives',
            'attributes' => $singular . ' Attributes',
            'insert_into_item' => 'Insert into ' . strtolower($singular),
            'uploaded_to_this_item' => 'Uploaded to this ' . strtolower($singular),
        ];
    }
}

