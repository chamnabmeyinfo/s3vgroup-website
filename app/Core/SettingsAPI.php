<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Settings API - WordPress-like Settings API
 */
final class SettingsAPI
{
    private static array $settings = [];
    private static array $sections = [];
    private static array $fields = [];

    /**
     * Register a setting
     * WordPress: register_setting($option_group, $option_name, $args)
     */
    public static function registerSetting(string $optionGroup, string $optionName, array $args = []): void
    {
        $defaults = [
            'type' => 'string',
            'description' => '',
            'sanitize_callback' => null,
            'default' => '',
        ];

        if (!isset(self::$settings[$optionGroup])) {
            self::$settings[$optionGroup] = [];
        }

        self::$settings[$optionGroup][$optionName] = array_merge($defaults, $args);
    }

    /**
     * Add settings section
     * WordPress: add_settings_section($id, $title, $callback, $page)
     */
    public static function addSettingsSection(string $id, string $title, ?callable $callback, string $page): void
    {
        if (!isset(self::$sections[$page])) {
            self::$sections[$page] = [];
        }

        self::$sections[$page][$id] = [
            'id' => $id,
            'title' => $title,
            'callback' => $callback,
        ];
    }

    /**
     * Add settings field
     * WordPress: add_settings_field($id, $title, $callback, $page, $section, $args)
     */
    public static function addSettingsField(string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = []): void
    {
        if (!isset(self::$fields[$page])) {
            self::$fields[$page] = [];
        }
        if (!isset(self::$fields[$page][$section])) {
            self::$fields[$page][$section] = [];
        }

        self::$fields[$page][$section][$id] = [
            'id' => $id,
            'title' => $title,
            'callback' => $callback,
            'args' => $args,
        ];
    }

    /**
     * Get settings
     */
    public static function getSettings(): array
    {
        return self::$settings;
    }

    /**
     * Get sections for page
     */
    public static function getSections(string $page): array
    {
        return self::$sections[$page] ?? [];
    }

    /**
     * Get fields for page/section
     */
    public static function getFields(string $page, string $section = 'default'): array
    {
        return self::$fields[$page][$section] ?? [];
    }

    /**
     * Render settings fields
     * WordPress: do_settings_fields($page, $section)
     */
    public static function doSettingsFields(string $page, string $section): void
    {
        $fields = self::getFields($page, $section);

        foreach ($fields as $field) {
            echo '<tr>';
            echo '<th scope="row">' . esc_html($field['title']) . '</th>';
            echo '<td>';
            call_user_func($field['callback'], $field['args']);
            echo '</td>';
            echo '</tr>';
        }
    }

    /**
     * Render settings sections
     * WordPress: do_settings_sections($page)
     */
    public static function doSettingsSections(string $page): void
    {
        $sections = self::getSections($page);

        foreach ($sections as $section) {
            echo '<h2>' . esc_html($section['title']) . '</h2>';
            
            if ($section['callback']) {
                call_user_func($section['callback'], $section);
            }

            echo '<table class="form-table" role="presentation">';
            self::doSettingsFields($page, $section['id']);
            echo '</table>';
        }
    }
}

