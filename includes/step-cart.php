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
            $method_cost = ! empty( $method->cost ) ? $method->cost : 0; // Default cost, you might want to calculate this based on cart

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
            $wc_coupon = new \WC_Coupon($coupon_code);
            $type = $wc_coupon->get_discount_type();
            if( $type == 'percent' ) {
                $discount_total = '%' . $wc_coupon->get_amount();
            }else {
                $discount_total = wc_price(WC()->cart->get_coupon_discount_amount($coupon_code));
            }
        }
    }
?>
<div class="cart-items-wrap">
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
    <button id="toggleShipping"><span>運送方式</span><span class="shipping-sub">請選擇運送方式</span></button>
        <div id="selectedShippingMethod"></div>
    </div>

    <!-- Coupon Toggle Section -->
    <div class="coupon-section">
        <button id="toggleCoupon" class="toggle-button"><span>兌換</span><span class="shipping-sub">請輸入優惠碼或選擇購物現金券</span></button>
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
            <span class="cart-icon"><img src="/wp-content/uploads/2025/02/shopping_basket.svg"></span> <span class="cart-count"><?php echo $cart_count; ?></span>
        </div>
        <div class="msc-nav-total">
            <?php echo WC()->cart->get_total(); ?>
        </div>
    </div>
    <button id="placeOrderButton" class="disabled">立即結帳</button>
</div>
<?php } ?>