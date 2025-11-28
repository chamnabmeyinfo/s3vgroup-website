<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Hook System - WordPress-like Actions and Filters
 * 
 * Actions: Do something at a specific point
 * Filters: Modify data before it's used
 */
final class HookSystem
{
    private static array $actions = [];
    private static array $filters = [];

    /**
     * Add an action hook
     * 
     * @param string $hook The hook name
     * @param callable $callback The callback function
     * @param int $priority Priority (lower = earlier execution)
     * @param int $acceptedArgs Number of arguments to accept
     */
    public static function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        if (!isset(self::$actions[$hook])) {
            self::$actions[$hook] = [];
        }

        self::$actions[$hook][] = [
            'callback' => $callback,
            'priority' => $priority,
            'acceptedArgs' => $acceptedArgs,
        ];

        // Sort by priority
        usort(self::$actions[$hook], fn($a, $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Execute an action hook
     * 
     * @param string $hook The hook name
     * @param mixed ...$args Arguments to pass to callbacks
     */
    public static function doAction(string $hook, ...$args): void
    {
        if (!isset(self::$actions[$hook])) {
            return;
        }

        foreach (self::$actions[$hook] as $action) {
            $callback = $action['callback'];
            $acceptedArgs = $action['acceptedArgs'];
            
            if ($acceptedArgs === 0) {
                $callback();
            } elseif ($acceptedArgs === 1) {
                $callback($args[0] ?? null);
            } else {
                $callback(...array_slice($args, 0, $acceptedArgs));
            }
        }
    }

    /**
     * Remove an action hook
     */
    public static function removeAction(string $hook, callable $callback, int $priority = 10): bool
    {
        if (!isset(self::$actions[$hook])) {
            return false;
        }

        foreach (self::$actions[$hook] as $key => $action) {
            if ($action['callback'] === $callback && $action['priority'] === $priority) {
                unset(self::$actions[$hook][$key]);
                return true;
            }
        }

        return false;
    }

    /**
     * Add a filter hook
     * 
     * @param string $hook The hook name
     * @param callable $callback The callback function
     * @param int $priority Priority (lower = earlier execution)
     * @param int $acceptedArgs Number of arguments to accept
     */
    public static function addFilter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        if (!isset(self::$filters[$hook])) {
            self::$filters[$hook] = [];
        }

        self::$filters[$hook][] = [
            'callback' => $callback,
            'priority' => $priority,
            'acceptedArgs' => $acceptedArgs,
        ];

        // Sort by priority
        usort(self::$filters[$hook], fn($a, $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Apply a filter hook
     * 
     * @param string $hook The hook name
     * @param mixed $value The value to filter
     * @param mixed ...$args Additional arguments
     * @return mixed The filtered value
     */
    public static function applyFilters(string $hook, $value, ...$args)
    {
        if (!isset(self::$filters[$hook])) {
            return $value;
        }

        foreach (self::$filters[$hook] as $filter) {
            $callback = $filter['callback'];
            $acceptedArgs = $filter['acceptedArgs'];
            
            if ($acceptedArgs === 1) {
                $value = $callback($value);
            } else {
                $value = $callback($value, ...array_slice($args, 0, $acceptedArgs - 1));
            }
        }

        return $value;
    }

    /**
     * Remove a filter hook
     */
    public static function removeFilter(string $hook, callable $callback, int $priority = 10): bool
    {
        if (!isset(self::$filters[$hook])) {
            return false;
        }

        foreach (self::$filters[$hook] as $key => $filter) {
            if ($filter['callback'] === $callback && $filter['priority'] === $priority) {
                unset(self::$filters[$hook][$key]);
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a hook has any callbacks
     */
    public static function hasAction(string $hook): bool
    {
        return isset(self::$actions[$hook]) && count(self::$actions[$hook]) > 0;
    }

    /**
     * Check if a filter has any callbacks
     */
    public static function hasFilter(string $hook): bool
    {
        return isset(self::$filters[$hook]) && count(self::$filters[$hook]) > 0;
    }

    /**
     * Get all registered hooks (for debugging)
     */
    public static function getHooks(): array
    {
        return [
            'actions' => array_keys(self::$actions),
            'filters' => array_keys(self::$filters),
        ];
    }
}

