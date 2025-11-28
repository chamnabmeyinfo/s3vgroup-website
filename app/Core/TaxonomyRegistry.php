<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Taxonomy Registry - WordPress-like Taxonomies
 */
final class TaxonomyRegistry
{
    private static array $taxonomies = [];

    /**
     * Register a taxonomy
     * WordPress: register_taxonomy($taxonomy, $object_type, $args)
     */
    public static function register(string $taxonomy, $objectType, array $args = []): void
    {
        $defaults = [
            'label' => ucfirst($taxonomy),
            'labels' => [],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_quick_edit' => true,
            'show_admin_column' => false,
            'hierarchical' => false, // false = tags, true = categories
            'query_var' => $taxonomy,
            'rewrite' => ['slug' => $taxonomy],
            'capabilities' => [],
            'sort' => false,
        ];

        $args = array_merge($defaults, $args);
        
        // Generate labels
        if (empty($args['labels'])) {
            $args['labels'] = self::generateLabels($taxonomy, $args['label']);
        }

        // Support multiple object types
        $objectTypes = is_array($objectType) ? $objectType : [$objectType];
        
        foreach ($objectTypes as $type) {
            if (!isset(self::$taxonomies[$type])) {
                self::$taxonomies[$type] = [];
            }
            self::$taxonomies[$type][$taxonomy] = $args;
        }
        
        // Trigger registration hook
        HookSystem::doAction('register_taxonomy', $taxonomy, $objectTypes, $args);
    }

    /**
     * Get taxonomies for object type
     */
    public static function getObjectTaxonomies(string $objectType): array
    {
        return self::$taxonomies[$objectType] ?? [];
    }

    /**
     * Get all taxonomies
     */
    public static function getTaxonomies(): array
    {
        return self::$taxonomies;
    }

    /**
     * Check if taxonomy exists
     */
    public static function taxonomyExists(string $taxonomy, string $objectType): bool
    {
        return isset(self::$taxonomies[$objectType][$taxonomy]);
    }

    /**
     * Generate labels for taxonomy
     */
    private static function generateLabels(string $taxonomy, string $singular): array
    {
        $plural = $singular . 's';
        
        return [
            'name' => $plural,
            'singular_name' => $singular,
            'menu_name' => $plural,
            'all_items' => 'All ' . $plural,
            'edit_item' => 'Edit ' . $singular,
            'view_item' => 'View ' . $singular,
            'update_item' => 'Update ' . $singular,
            'add_new_item' => 'Add New ' . $singular,
            'new_item_name' => 'New ' . $singular . ' Name',
            'parent_item' => 'Parent ' . $singular,
            'parent_item_colon' => 'Parent ' . $singular . ':',
            'search_items' => 'Search ' . $plural,
            'popular_items' => 'Popular ' . $plural,
            'separate_items_with_commas' => 'Separate ' . strtolower($plural) . ' with commas',
            'add_or_remove_items' => 'Add or remove ' . strtolower($plural),
            'choose_from_most_used' => 'Choose from the most used ' . strtolower($plural),
            'not_found' => 'No ' . strtolower($plural) . ' found',
            'no_terms' => 'No ' . strtolower($plural),
            'items_list_navigation' => $plural . ' list navigation',
            'items_list' => $plural . ' list',
        ];
    }
}

