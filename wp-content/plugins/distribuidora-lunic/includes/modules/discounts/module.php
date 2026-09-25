<?php

namespace Distribuidora_Lunic\Modules\Discounts;

defined('ABSPATH') || exit;

class Module {

    public function register(): void {
        if (class_exists('WCD_Price_Calculator')) {
            return;
        }
        require_once __DIR__ . '/class-calculator.php';
        Calculator::init();
        add_action('elementor/widgets/register', [$this, 'register_legacy_widget']);
    }

    public function register_legacy_widget($widgets_manager): void {
        if (!class_exists('\Elementor\Widget_Base') || class_exists('\Elementor_WCD_Category_Discount_Widget')) {
            return;
        }
        require_once __DIR__ . '/class-elementor-widget.php';
        $widgets_manager->register(new Elementor_Widget());
    }
}
