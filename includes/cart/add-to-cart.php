<?php
add_shortcode('multi_add_to_cart', 'woosb_multi_add_to_cart_shortcode');
function woosb_multi_add_to_cart_shortcode() {
    $cart_count = 0;
    if (function_exists('WC') && WC()->cart && method_exists(WC()->cart, 'get_cart_contents_count')) {
        $cart_count = WC()->cart->get_cart_contents_count();
    }
    $redirect_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() :'';
    ob_start();
    $btn_class =  $cart_count  > 0 ? '' : 'disabled';
    ?>
    <button
        id="woosb-multi-add-to-cart"
        class="button add-to-cart-button <?php echo $btn_class; ?>"
        data-redirect="<?php echo $redirect_url; ?>"
        data-cart-count="<?php echo $cart_count; ?>"
    >
        立即下單
    </button>
    <div>6餐起送貨</div>
    <?php
    return ob_get_clean();
}


add_action('wp_ajax_woosb_multi_add_to_cart', 'woosb_multi_add_to_cart');
add_action('wp_ajax_nopriv_woosb_multi_add_to_cart', 'woosb_multi_add_to_cart');

function woosb_multi_add_to_cart() {
    if (!isset($_POST['bundles']) || empty($_POST['bundles'])) {
        wp_send_json_error();
    }

    foreach ($_POST['bundles'] as $bundle) {
        $bundle_id = (int) $bundle['id'];
        $quantity = (int) $bundle['qty'];

        $product = wc_get_product($bundle_id);

        // Add only the bundle product, without increasing sub-product quantities
        WC()->cart->add_to_cart($bundle_id, $quantity, 0, array(), array('woosb_no_sync' => true));

    }

    wp_send_json_success();
}