<?php
/**
 * Description tab
 *
 * Product copy only. Do not call the_content(): Elementor Theme Builder would
 * re-render the single/footer documents inside this panel.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Lunic
 * @version 0.3.59
 */

defined('ABSPATH') || exit;

if (function_exists('lunic_print_product_description_tab')) {
    lunic_print_product_description_tab();
    return;
}
