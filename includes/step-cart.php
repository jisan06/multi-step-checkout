<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

WC()->cart->calculate_totals();
// Get WooCommerce cart contents
$cart = WC()->cart->get_cart();
$cart_count = WC()->cart->get_cart_contents_count();

$chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
$default_shipping_method = !empty($chosen_shipping_methods) ? $chosen_shipping_methods[0] : '';
$default_shipping_title = '請選擇運送方式';

$packages = WC()->shipping()->get_packages();
$active_shipping_methods = [];

$free_shipping_id = '';
foreach ($packages as $package) {
    $rates = $package['rates'];
    foreach ($rates as $method) {
        if( $method->id == 'advanced_free_shipping' ) {
            $free_shipping_id = $method->id;
        }elseif (strpos($default_shipping_method, $method->id) !== false) {
            $default_shipping_title = $method->label;
        }
        $active_shipping_methods[] = $method;
    }
}
//    $shipping_zones = WC_Shipping_Zones::get_zones();
//    $active_shipping_methods = [];
//
//    foreach ($shipping_zones as $zone) {
//        // Get the shipping methods for the current zone
//        $zone_methods = $zone['shipping_methods'];
//        foreach ($zone_methods as $method) {
//            // Check if the method is enabled
//            if ($method->enabled === 'yes') {
//                // Get method title and cost (if applicable)
//                $method_title = $method->get_title();
//                $method_cost = ! empty( $method->cost ) ? $method->cost : 0; // Default cost, you might want to calculate this based on cart
//
//                // Store the method information
//                $active_shipping_methods[] = [
//                    'name' => $method->id,
//                    'id' => $method->id . ':' . $method->instance_id,
//                    'title' => $method_title,
//                    'cost' => $method_cost,
//                ];
//            }
//        }
//    }

$applied_coupons = WC()->cart->get_applied_coupons();
$discount_amount_percent = 0;
$discount_total = 0;
$current_coupon = '';
//$total_cart = WC()->cart->get_subtotal();
if (!empty($applied_coupons)) {
    foreach ($applied_coupons as $coupon_code) {
        $wc_coupon = new \WC_Coupon($coupon_code);
        $type = $wc_coupon->get_discount_type();
        $current_coupon = $wc_coupon->get_code();
        if( $type == 'percent' ) {
            $discount_amount_percent = '%' . $wc_coupon->get_amount();
        }
        $discount_total = WC()->cart->get_coupon_discount_amount($coupon_code);
    }
}
//    $total_cart = $total_cart - $discount_total;
$total_cart = wc()->cart->get_total();
?>
    <div class="cart-items-wrap">
        <a class="catalog-back" href="/catalog"><span class="dashicons dashicons-arrow-left-alt2"></span></a>
        <div class="cart-items">
            <div class="main-head">你的訂單</div>
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
                                <div class="main-title">
                                    <?php echo esc_html($product->get_name()); ?>
                                </div>
                                <div class="product-sub-items">
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
                                if (!empty($tags[0]->name)) {
                                    echo $tags[0]->name;
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
            <button id="toggleShipping"><span>運送方式</span>
                <span class="shipping-sub" id="selectedShippingMethod">
                    <?php echo $default_shipping_title; ?>
                </span>
            </button>
        </div>

        <!-- Coupon Toggle Section -->
        <div class="coupon-section">
            <button
                    id="toggleCoupon"
                    class="toggle-button"
            >
                <span>兌換</span>
                <span class="shipping-sub">請輸入優惠碼或選擇購物現金券</span>
                <div id="appliedCoupon" <?php if(!$discount_total) { ?>style="display:none" <?php } ?>>
                    已使用 1 張優惠券 -
                    <span class="coupon-amount">
                        <?php echo $discount_amount_percent ? $discount_amount_percent : $discount_total; ?>
                    </span>
                </div>
            </button>

        </div>
    </div>

<?php include MSC_PLUGIN_PATH . 'includes/cart/shipping.php'; ?>

<?php include MSC_PLUGIN_PATH . 'includes/cart/coupon.php'; ?>

<?php if( $cart_count > 0 ){ ?>
    <div class="msc-nav">
        <div class="msc-nav-left">
            <div class="msc-nav-qty">
                <span class="cart-icon"><img src="/wp-content/uploads/2025/02/shopping_basket.svg"></span> <span class="cart-count"><?php echo $cart_count; ?></span>
            </div>
            <div class="msc-nav-total">
                <?php echo $total_cart; ?>
            </div>
        </div>
        <button id="placeOrderButton" class="disabled">立即結帳</button>
    </div>
<?php } ?>