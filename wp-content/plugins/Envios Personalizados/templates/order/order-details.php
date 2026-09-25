<?php
/**
 * Order details
 *
 * Modified version for Custom Shipping plugin
 *
 * @version 4.6.0
 */

defined('ABSPATH') || exit;

$order = wc_get_order($order_id);

if (!$order) {
    return;
}

$order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$show_purchase_note = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', array('completed', 'processing')));
$downloads = $order->get_downloadable_items();
$show_downloads = $order->has_downloadable_item() && $order->is_download_permitted();

if ($show_downloads) {
    wc_get_template('order/order-downloads.php', array('downloads' => $downloads, 'show_title' => true));
}
?>
<section class="woocommerce-order-details">
    <?php do_action('woocommerce_order_details_before_order_table', $order); ?>
    
    <h2 class="woocommerce-order-details__title"><?php esc_html_e('Order details', 'woocommerce'); ?></h2>
    
    <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
        <thead>
            <tr>
                <th class="woocommerce-table__product-name product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                <th class="woocommerce-table__product-table product-total"><?php esc_html_e('Total', 'woocommerce'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            do_action('woocommerce_order_details_before_order_table_items', $order);
            
            foreach ($order_items as $item_id => $item) {
                $product = $item->get_product();
                
                wc_get_template('order/order-details-item.php', array(
                    'order' => $order,
                    'item_id' => $item_id,
                    'item' => $item,
                    'product' => $product,
                    'show_purchase_note' => $show_purchase_note,
                    'purchase_note' => $product ? $product->get_purchase_note() : '',
                    'downloads' => $downloads,
                    'show_downloads' => $show_downloads,
                ));
            }
            
            do_action('woocommerce_order_details_after_order_table_items', $order);
            ?>
        </tbody>
        <tfoot>
            <?php
            foreach ($order->get_order_item_totals() as $key => $total) {
                // Omitir la línea de envío del total tradicional
                if ($key === 'shipping') {
                    continue;
                }
                ?>
                <tr>
                    <th scope="row"><?php echo esc_html($total['label']); ?></th>
                    <td><?php echo wp_kses_post($total['value']); ?></td>
                </tr>
                <?php
            }
            ?>
            
            <!-- Mostrar envío después del total -->
            <?php if ($order->get_shipping_method()) : ?>
                <tr class="shipping-after-total">
                    <th><?php esc_html_e('Shipping:', 'woocommerce'); ?></th>
                    <td>
                        <?php echo wc_price($order->get_shipping_total()); ?>
                        <?php if ($order->get_shipping_tax() > 0) : ?>
                            <small class="tax_label"><?php echo wp_kses_post($order->get_shipping_tax()); ?></small>
                        <?php endif; ?>
                        - <?php echo esc_html($order->get_shipping_method()); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tfoot>
    </table>
    
    <?php do_action('woocommerce_order_details_after_order_table', $order); ?>
</section>