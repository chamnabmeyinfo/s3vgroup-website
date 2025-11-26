<?php
/**
 * Diagnose WordPress Database Structure
 * 
 * This script helps identify what's in the WordPress database
 * 
 * Run: php database/diagnose-wordpress-db.php
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';

echo "🔍 Diagnosing WordPress Database...\n\n";

// WordPress database credentials
$host = 'localhost';
$database = 'kdmedsco_wp768';
$username = 'kdmedsco_wp768';
$password = '3p)P246Z.S';
$prefix = 'kdmedsco_';

try {
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $wpDb = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Connected to database: {$database}\n\n";
    
    // List all tables
    echo "📊 All Tables in Database:\n";
    echo str_repeat("=", 50) . "\n";
    $tables = $wpDb->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "   ⚠️  No tables found in database!\n";
        echo "   This database might be empty or newly created.\n";
    } else {
        foreach ($tables as $table) {
            echo "   - {$table}\n";
        }
    }
    echo "\n";
    
    // Check for WordPress tables with different prefixes
    echo "🔍 Checking for WordPress Tables:\n";
    echo str_repeat("=", 50) . "\n";
    
    $commonPrefixes = ['wp_', 'kdmedsco_', 'wordpress_', 'wp1_', 'wp2_'];
    $foundTables = [];
    
    foreach ($commonPrefixes as $pref) {
        $postsTable = $pref . 'posts';
        $optionsTable = $pref . 'options';
        
        if (in_array($postsTable, $tables)) {
            echo "   ✅ Found posts table: {$postsTable}\n";
            $foundTables[$pref] = true;
            
            // Count posts
            try {
                $count = $wpDb->query("SELECT COUNT(*) FROM {$postsTable}")->fetchColumn();
                echo "      Posts count: {$count}\n";
                
                // Check post types
                $types = $wpDb->query("SELECT DISTINCT post_type, COUNT(*) as count FROM {$postsTable} GROUP BY post_type LIMIT 10")->fetchAll();
                if (!empty($types)) {
                    echo "      Post types found:\n";
                    foreach ($types as $type) {
                        echo "         - {$type['post_type']}: {$type['count']} posts\n";
                    }
                }
            } catch (Exception $e) {
                echo "      Error counting: " . $e->getMessage() . "\n";
            }
        }
        
        if (in_array($optionsTable, $tables)) {
            echo "   ✅ Found options table: {$optionsTable}\n";
            $foundTables[$pref] = true;
            
            // Get WordPress version
            try {
                $version = $wpDb->query("SELECT option_value FROM {$optionsTable} WHERE option_name = 'version' LIMIT 1")->fetchColumn();
                if ($version) {
                    echo "      WordPress version: {$version}\n";
                }
            } catch (Exception $e) {
                // Ignore
            }
        }
    }
    
    echo "\n";
    
    // Check for WooCommerce tables
    echo "🛒 Checking for WooCommerce:\n";
    echo str_repeat("=", 50) . "\n";
    
    $wooTables = ['wc_product_meta_lookup', 'woocommerce_sessions', 'woocommerce_api_keys'];
    foreach ($wooTables as $wooTable) {
        if (in_array($wooTable, $tables)) {
            echo "   ✅ Found WooCommerce table: {$wooTable}\n";
        }
    }
    
    // Check for terms/categories
    echo "\n📁 Checking for Categories/Terms:\n";
    echo str_repeat("=", 50) . "\n";
    
    foreach ($commonPrefixes as $pref) {
        $termsTable = $pref . 'terms';
        $termTaxonomyTable = $pref . 'term_taxonomy';
        
        if (in_array($termsTable, $tables) && in_array($termTaxonomyTable, $tables)) {
            echo "   ✅ Found terms tables with prefix: {$pref}\n";
            
            try {
                // Count all terms
                $termCount = $wpDb->query("SELECT COUNT(*) FROM {$termsTable}")->fetchColumn();
                echo "      Total terms: {$termCount}\n";
                
                // Check taxonomies
                $taxonomies = $wpDb->query("
                    SELECT tt.taxonomy, COUNT(*) as count 
                    FROM {$termsTable} t
                    INNER JOIN {$termTaxonomyTable} tt ON t.term_id = tt.term_id
                    GROUP BY tt.taxonomy
                    LIMIT 10
                ")->fetchAll();
                
                if (!empty($taxonomies)) {
                    echo "      Taxonomies found:\n";
                    foreach ($taxonomies as $tax) {
                        echo "         - {$tax['taxonomy']}: {$tax['count']} terms\n";
                    }
                }
            } catch (Exception $e) {
                echo "      Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n";
    
    // Recommendations
    echo "💡 Recommendations:\n";
    echo str_repeat("=", 50) . "\n";
    
    if (empty($tables)) {
        echo "   ⚠️  Database appears to be empty.\n";
        echo "   - This might be a new/empty WordPress installation\n";
        echo "   - Or you might be connecting to the wrong database\n";
        echo "   - Check if WordPress is actually installed in this database\n";
    } else {
        if (empty($foundTables)) {
            echo "   ⚠️  No standard WordPress tables found with common prefixes.\n";
            echo "   - Check the table list above to identify the correct prefix\n";
            echo "   - Update the prefix in the import form\n";
        } else {
            echo "   ✅ WordPress tables found!\n";
            echo "   - Use the prefix shown above in the import form\n";
            echo "   - If products = 0, the database might not have WooCommerce products yet\n";
        }
    }
    
    echo "\n✨ Diagnosis complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

