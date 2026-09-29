<?php
/**
 * Limpieza puntual etapa 9.3. Ejecutar una sola vez: php documentos/_run-cleanup-9.3.php
 */
$_SERVER['REQUEST_SCHEME'] = 'https';
$_SERVER['HTTP_HOST'] = 'distribuidoralunic.com.ar.dev';
require __DIR__ . '/../wp-load.php';

if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}

global $wpdb;

function autoload_bytes(): int {
    global $wpdb;
    return (int) $wpdb->get_var("SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'");
}

$report = [
    'autoload_before' => autoload_bytes(),
    'revisions_deleted' => 0,
    'tables_dropped' => [],
    'options_deleted' => 0,
];

$revs = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'");
$wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'");
$report['revisions_deleted'] = $revs;

$wpdb->query(
    "DELETE pm FROM {$wpdb->postmeta} pm
    LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
    WHERE p.ID IS NULL"
);

$tables = $wpdb->get_col('SHOW TABLES');
foreach ($tables as $table) {
    if (!preg_match('/(wpf|woof|yith_wcps|wpforms_tasks|is_inverted_index)/i', $table)) {
        continue;
    }
    $wpdb->query('DROP TABLE IF EXISTS `' . str_replace('`', '``', $table) . '`');
    $report['tables_dropped'][] = $table;
}

$like = [
    'jetpack%',
    'elementskit%',
    'woof%',
    'wpf_%',
    'betterdocs%',
    'yith_%',
    'ultimatemember%',
    'um_%',
    'jet-%',
    'jet_%',
    'is_index',
    'is_settings',
    'elementor_pro%',
    '_elementor_pro_%',
    'wpforms%',
    'ivory%',
    'widget_wpf%',
    'widget_woof%',
    'plugin_activation_elementor-pro/%',
];
$deleted = 0;
foreach ($like as $pattern) {
    $names = $wpdb->get_col($wpdb->prepare(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
        $pattern
    ));
    foreach ($names as $name) {
        if (strpos($name, 'mainwp') !== false) {
            continue;
        }
        delete_option($name);
        $deleted++;
    }
}
$report['options_deleted'] = $deleted;

wp_cache_flush();
if (class_exists(\Distribuidora_Lunic\Modules\CatalogFilter\Category_Tree::class)) {
    \Distribuidora_Lunic\Modules\CatalogFilter\Category_Tree::flush();
}

$report['autoload_after'] = autoload_bytes();
$report['revisions_remaining'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'");

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
