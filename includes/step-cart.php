<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $woocommerce;

// Get WooCommerce cart contents
$cart = WC()->cart->get_cart();
$cart_count = WC()->cart->get_cart_contents_count();
$shipping_zones = WC_Shipping_Zones::get_zones();
$active_shipping_methods = [];

foreach ($shipping_zones as $zone) {
    // Get the shipping methods for the current zone
    $zone_methods = $zone['shipping_methods'];

    foreach ($zone_methods as $method) {
        // Check if the method is enabled
        if ($method->enabled === 'yes') {
            // Get method title and cost (if applicable)
            $method_title = $method->get_method_title();
            $method_cost = $method->cost; // Default cost, you might want to calculate this based on cart

            // Store the method information
            $active_shipping_methods[] = [
                'id' => $method->id,
                'title' => $method_title,
                'cost' => $method_cost,
            ];
        }
    }
}

    $applied_coupons = WC()->cart->get_applied_coupons();
    $discount_total = 0;

    if (!empty($applied_coupons)) {
        foreach ($applied_coupons as $coupon_code) {
            $discount_total += WC()->cart->get_coupon_discount_amount($coupon_code);
        }
    }
?>
<div class="cart-items-wrap">
    <div class="cart-items">
        <div>Your Order</div>
        <?php if ( !empty($cart) ) : ?>
            <?php foreach ($cart as $cart_item_key => $cart_item) :
                $item_data = [];
                $cart_item_data = apply_filters( 'woocommerce_get_item_data', $item_data, $cart_item );
                $product = $cart_item['data'];
                $quantity = $cart_item['quantity'];
                $subtotal = $product->get_price() * $quantity;
                $product_image = wp_get_attachment_image($product->get_image_id(), 'thumbnail'); // Get product image
                ?>
                <div class="cart-item">
                    <div class="cart-item-body">
                        <div class="cart-item-img">
                            <?php echo $product_image; ?>
                        </div>
                        <div class="cart-item-name-wrap">
                            <div>
                                <?php echo esc_html($product->get_name()); ?>
                            </div>
                            <div>
                                <?php echo !empty($cart_item_data[0]['value']) ? $cart_item_data[0]['value'] : ''; ?>
                            </div>
                        </div>
                        <div class="cart-item-qty">
                            X <?php echo esc_html($quantity); ?>
                        </div>
                    </div>
                    <div class="cart-item-footer">
                        <div class="cart-item-left">
                            <?php
                                echo wc_price($product->get_price());
                                $tags = get_the_terms($product->get_id(), 'product_tag');
                                if ($tags && !is_wp_error($tags)) {
                                    echo '<div class="woosb-tags">';
                                    foreach ($tags as $tag) {
                                        $tag_bg_color = get_field('tag_bg_color', 'product_tag_' . $tag->term_id); // Fetch ACF color
                                        $tag_style    = $tag_bg_color ? 'background-color:' . esc_attr($tag_bg_color) . '; color: #fff; padding: 5px 10px; margin: 2px; border-radius: 5px; display: inline-block;' : '';

                                        echo '<span class="woosb-tag" style="' . $tag_style . '">' . esc_html($tag->name) . '</span>';
                                    }
                                    echo '</div>';
                                }
                            ?>
                        </div>
                        <div class="cart-item-right">
                            <?php echo wc_price($subtotal); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>Your cart is currently empty.</p>
        <?php endif; ?>
    </div>

    <!-- Shipping Methods Section -->
    <div class="toggleShipWrap">
        <button id="toggleShipping">Shipping Methods</button>
        <div id="selectedShippingMethod"></div>
    </div>

    <!-- Coupon Toggle Section -->
    <div class="coupon-section">
        <button id="toggleCoupon" class="toggle-button">Coupon</button>
        <div id="appliedCoupon" <?php if(!$discount_total) { ?>style="display:none" <?php } ?>>
            1 coupon used =
            <span class="coupon-amount"><?php echo $discount_total; ?></span>
        </div>
    </div>
</div>

<?php include MSC_PLUGIN_PATH . 'includes/cart/shipping.php'; ?>

<?php include MSC_PLUGIN_PATH . 'includes/cart/coupon.php'; ?>

<?php if( $cart_count > 0 ){ ?>
<div class="msc-nav">
    <div class="msc-nav-left">
        <div class="msc-nav-qty">
            <span class="dashicons dashicons-cart"></span> <?php echo $cart_count; ?>
        </div>
        <div class="msc-nav-total">
            <?php echo WC()->cart->get_total(); ?>
        </div>
    </div>
    <button id="placeOrderButton" class="disabled">Checkout</button>
</div>
<?php } ?>



