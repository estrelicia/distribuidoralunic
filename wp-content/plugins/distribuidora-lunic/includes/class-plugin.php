<?php

namespace Distribuidora_Lunic;

defined('ABSPATH') || exit;

final class Plugin {

    private static ?Plugin $instance = null;

    /** @var array<string, object> */
    private array $modules = [];

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void {
        $this->maybe_preview_theme();
        $this->load_includes();
        $this->load_modules();

        if (is_admin()) {
            Settings::instance()->init();
        }
    }

    private function maybe_preview_theme(): void {
        if (!isset($_GET['lunic_preview']) || $_GET['lunic_preview'] !== '1') {
            return;
        }
        add_filter('pre_option_template', static function () {
            return 'lunic';
        });
        add_filter('pre_option_stylesheet', static function () {
            return 'lunic';
        });
        add_action('wp', static function () {
            if (!class_exists('\ElementorPro\Modules\ThemeBuilder\Module')) {
                return;
            }
            $manager = \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_locations_manager();
            remove_filter('template_include', [$manager, 'template_include'], 11);
        }, 0);
    }

    private function load_includes(): void {
        require_once DISTRIBUIDORA_LUNIC_PATH . 'includes/class-settings.php';
    }

    private function load_modules(): void {
        $modules_dir = DISTRIBUIDORA_LUNIC_PATH . 'includes/modules';
        if (!is_dir($modules_dir)) {
            return;
        }

        $entries = scandir($modules_dir);
        if ($entries === false) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $module_path = $modules_dir . '/' . $entry;
            if (!is_dir($module_path)) {
                continue;
            }

            $bootstrap = $module_path . '/module.php';
            if (!is_readable($bootstrap)) {
                continue;
            }

            require_once $bootstrap;

            $class = $this->module_class_name($entry);
            if (!class_exists($class)) {
                continue;
            }

            $module = new $class();
            if (method_exists($module, 'register')) {
                $module->register();
            }
            $this->modules[$entry] = $module;
        }
    }

    private function module_class_name(string $slug): string {
        $parts = array_filter(explode('-', $slug));
        $studly = implode('', array_map('ucfirst', $parts));
        return __NAMESPACE__ . '\\Modules\\' . $studly . '\\Module';
    }

    /** @return array<string, object> */
    public function modules(): array {
        return $this->modules;
    }
}
