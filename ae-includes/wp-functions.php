<?php

/**
 * WordPress-Like Functions
 * Complete WordPress API implementation
 */

use App\Domain\Settings\SiteOptionRepository;
use App\Database\Connection;

// ============================================
// OPTIONS API (WordPress get_option, etc.)
// ============================================

/**
 * Get option value
 * WordPress: get_option($option, $default = false)
 */
function get_option(string $option, $default = false)
{
    static $repository = null;
    
    if ($repository === null) {
        $repository = new SiteOptionRepository(Connection::getInstance());
    }
    
    return $repository->get($option, $default);
}

/**
 * Update option value
 * WordPress: update_option($option, $value)
 */
function update_option(string $option, $value): bool
{
    static $repository = null;
    
    if ($repository === null) {
        $repository = new SiteOptionRepository(Connection::getInstance());
    }
    
    try {
        $repository->set($option, $value);
        return true;
    } catch (\Exception $e) {
        error_log("Failed to update option {$option}: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete option
 * WordPress: delete_option($option)
 */
function delete_option(string $option): bool
{
    static $repository = null;
    static $pdo = null;
    
    if ($repository === null) {
        $repository = new SiteOptionRepository(Connection::getInstance());
        $pdo = Connection::getInstance();
    }
    
    try {
        $stmt = $pdo->prepare('DELETE FROM site_options WHERE key_name = :key');
        return $stmt->execute([':key' => $option]);
    } catch (\Exception $e) {
        error_log("Failed to delete option {$option}: " . $e->getMessage());
        return false;
    }
}

/**
 * Add option (only if doesn't exist)
 * WordPress: add_option($option, $value)
 */
function add_option(string $option, $value): bool
{
    if (get_option($option) !== false) {
        return false; // Option already exists
    }
    
    return update_option($option, $value);
}

// ============================================
// ASSET MANAGEMENT (wp_enqueue_script, etc.)
// ============================================

global $wp_scripts, $wp_styles;
$wp_scripts = [];
$wp_styles = [];

/**
 * Enqueue a script
 * WordPress: wp_enqueue_script($handle, $src, $deps, $version, $in_footer)
 */
function wp_enqueue_script(string $handle, string $src = '', array $deps = [], $version = false, bool $in_footer = false): void
{
    global $wp_scripts;
    
    $wp_scripts[$handle] = [
        'handle' => $handle,
        'src' => $src,
        'deps' => $deps,
        'version' => $version,
        'in_footer' => $in_footer,
    ];
    
    do_action('wp_enqueue_scripts', $handle);
}

/**
 * Enqueue a style
 * WordPress: wp_enqueue_style($handle, $src, $deps, $version, $media)
 */
function wp_enqueue_style(string $handle, string $src = '', array $deps = [], $version = false, string $media = 'all'): void
{
    global $wp_styles;
    
    $wp_styles[$handle] = [
        'handle' => $handle,
        'src' => $src,
        'deps' => $deps,
        'version' => $version,
        'media' => $media,
    ];
    
    do_action('wp_enqueue_scripts', $handle);
}

/**
 * Get enqueued scripts
 */
function wp_get_scripts(): array
{
    global $wp_scripts;
    return $wp_scripts ?? [];
}

/**
 * Get enqueued styles
 */
function wp_get_styles(): array
{
    global $wp_styles;
    return $wp_styles ?? [];
}

/**
 * Print enqueued scripts
 */
function wp_print_scripts(): void
{
    global $wp_scripts;
    
    foreach ($wp_scripts as $script) {
        $src = $script['src'];
        $version = $script['version'] ? '?ver=' . $script['version'] : '';
        $defer = $script['in_footer'] ? ' defer' : '';
        
        echo "<script src=\"{$src}{$version}\"{$defer}></script>\n";
    }
}

/**
 * Print enqueued styles
 */
function wp_print_styles(): void
{
    global $wp_styles;
    
    foreach ($wp_styles as $style) {
        $src = $style['src'];
        $version = $style['version'] ? '?ver=' . $style['version'] : '';
        $media = $style['media'] ?? 'all';
        
        echo "<link rel=\"stylesheet\" href=\"{$src}{$version}\" media=\"{$media}\">\n";
    }
}

// ============================================
// SHORTCODES
// ============================================

global $shortcode_tags;
$shortcode_tags = [];

/**
 * Register a shortcode
 * WordPress: add_shortcode($tag, $callback)
 */
function add_shortcode(string $tag, callable $callback): void
{
    global $shortcode_tags;
    $shortcode_tags[$tag] = $callback;
}

/**
 * Remove a shortcode
 * WordPress: remove_shortcode($tag)
 */
function remove_shortcode(string $tag): void
{
    global $shortcode_tags;
    unset($shortcode_tags[$tag]);
}

/**
 * Process shortcodes in content
 * WordPress: do_shortcode($content)
 */
function do_shortcode(string $content): string
{
    global $shortcode_tags;
    
    if (empty($shortcode_tags)) {
        return $content;
    }
    
    $pattern = get_shortcode_regex();
    
    return preg_replace_callback("/$pattern/", 'do_shortcode_tag', $content);
}

/**
 * Get shortcode regex pattern
 */
function get_shortcode_regex(): string
{
    global $shortcode_tags;
    $tagnames = array_keys($shortcode_tags);
    $tagregexp = join('|', array_map('preg_quote', $tagnames));
    
    return '\\[(\\[?)(' . $tagregexp . ')(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)';
}

/**
 * Process single shortcode tag
 */
function do_shortcode_tag(array $m): string
{
    global $shortcode_tags;
    
    if ($m[1] === '[' && $m[6] === ']') {
        return substr($m[0], 1, -1);
    }
    
    $tag = $m[2];
    $attr = shortcode_parse_atts($m[3]);
    $content = $m[5] ?? '';
    
    if (isset($shortcode_tags[$tag])) {
        return call_user_func($shortcode_tags[$tag], $attr, $content, $tag);
    }
    
    return $m[0];
}

/**
 * Parse shortcode attributes
 */
function shortcode_parse_atts(string $text): array
{
    $atts = [];
    $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
    
    if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
        foreach ($match as $m) {
            if (!empty($m[1])) {
                $atts[strtolower($m[1])] = stripcslashes($m[2]);
            } elseif (!empty($m[3])) {
                $atts[strtolower($m[3])] = stripcslashes($m[4]);
            } elseif (!empty($m[5])) {
                $atts[strtolower($m[5])] = stripcslashes($m[6]);
            } elseif (isset($m[7]) && strlen($m[7])) {
                $atts[] = stripcslashes($m[7]);
            } elseif (isset($m[8])) {
                $atts[] = stripcslashes($m[8]);
            }
        }
    }
    
    return $atts;
}

// ============================================
// ADMIN NOTICES
// ============================================

global $wp_admin_notices;
$wp_admin_notices = [];

/**
 * Add admin notice
 * WordPress: add_action('admin_notices', $callback)
 */
function add_admin_notice(string $message, string $type = 'info', bool $dismissible = false): void
{
    global $wp_admin_notices;
    
    $wp_admin_notices[] = [
        'message' => $message,
        'type' => $type, // info, success, warning, error
        'dismissible' => $dismissible,
    ];
}

/**
 * Get admin notices
 */
function get_admin_notices(): array
{
    global $wp_admin_notices;
    return $wp_admin_notices ?? [];
}

/**
 * Print admin notices
 */
function wp_admin_notices(): void
{
    $notices = get_admin_notices();
    
    foreach ($notices as $notice) {
        $type = $notice['type'];
        $dismissible = $notice['dismissible'] ? 'is-dismissible' : '';
        $message = $notice['message'];
        
        echo "<div class=\"notice notice-{$type} {$dismissible}\"><p>{$message}</p></div>\n";
    }
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get site URL
 * WordPress: site_url($path = '')
 */
function site_url(string $path = ''): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim($protocol . '://' . $host, '/');
    
    if ($path) {
        $path = '/' . ltrim($path, '/');
    }
    
    return $base . $path;
}

/**
 * Get admin URL
 * WordPress: admin_url($path = '')
 */
function admin_url(string $path = ''): string
{
    return site_url('/admin' . ($path ? '/' . ltrim($path, '/') : ''));
}

/**
 * Get plugin URL
 * WordPress: plugins_url($path, $file)
 */
function plugins_url(string $path = '', string $file = ''): string
{
    $pluginsDir = '/plugins';
    
    if ($file) {
        $pluginDir = dirname(plugin_basename($file));
        $pluginsDir .= '/' . $pluginDir;
    }
    
    return site_url($pluginsDir . ($path ? '/' . ltrim($path, '/') : ''));
}

/**
 * Get plugin basename
 * WordPress: plugin_basename($file)
 */
function plugin_basename(string $file): string
{
    $file = str_replace('\\', '/', $file);
    $file = preg_replace('|/+|', '/', $file);
    $pluginDir = str_replace('\\', '/', base_path('plugins'));
    $file = preg_replace('#^' . preg_quote($pluginDir, '#') . '/#', '', $file);
    
    return $file;
}

/**
 * Check if in admin
 * WordPress: is_admin()
 */
function is_admin(): bool
{
    return str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin');
}

/**
 * Escaping functions
 * WordPress: esc_html(), esc_attr(), esc_url(), etc.
 */
function esc_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_attr(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_url(string $url): string
{
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

function esc_js(string $text): string
{
    return htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
}

/**
 * Sanitize functions
 * WordPress: sanitize_text_field(), sanitize_email(), etc.
 */
function sanitize_text_field(string $str): string
{
    $str = trim($str);
    $str = stripslashes($str);
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function sanitize_email(string $email): string
{
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

function sanitize_url(string $url): string
{
    return filter_var($url, FILTER_SANITIZE_URL);
}

