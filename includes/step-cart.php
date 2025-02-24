<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get WooCommerce cart contents
$cart = WC()->cart->get_cart();
wp_enqueue_script( 'wc-country-select' );
?>

<div class="cart-items">
    <div>Your Order</div>
    <?php if ( !empty($cart) ) : ?>
        <?php foreach ($cart as $cart_item_key => $cart_item) :
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
                    <div class="cart-item-name">
                        <?php echo esc_html($product->get_name()); ?>
                    </div>
                    <div class="cart-item-qty">
                        X <?php echo esc_html($quantity); ?>
                    </div>
                </div>
                <div class="cart-item-footer">
                    <div class="cart-item-left">
                        <?php echo wc_price($product->get_price()); ?>
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
<div class="shipping-methods">
    <h3>Shipping Methods</h3>
    <?php
    global $woocommerce;
    $shipping_methods = array();
    // Get available shipping methods
    $packages = $woocommerce->cart->get_shipping_packages();
    foreach ($packages as $bh_package_key => $bh_package) {
        $shipping_methods[$bh_package_key] = $woocommerce->shipping->calculate_shipping_for_package($bh_package, $bh_package_key);
    }
    $shippingArr = $shipping_methods[0]['rates'];
    if (!empty($shippingArr)) {
        foreach ($shippingArr as $method) {
            ?>
            <div class="shipping-method" data-method-id="<?php echo esc_attr($method->id); ?>">
                <input type="radio" name="shipping_method" value="<?php echo esc_attr($method->id); ?>" id="<?php echo esc_attr($method->id); ?>">
                <label for="<?php echo esc_attr($method->id); ?>"><?php echo esc_html($method->label); ?> - <?php echo wc_price($method->cost); ?></label>
                <span class="arrow">→</span> <!-- Right arrow -->
            </div>

            <div class="shipping-fields" style="display:none">
                <p class="form-row form-row-wide" id="calc_shipping_country_field">
                    <label for="calc_shipping_country" class="screen-reader-text"><?php esc_html_e( 'Country / region:', 'woocommerce' ); ?></label>
                    <select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state country_select" rel="calc_shipping_state">
                        <option value="default"><?php esc_html_e( 'Select a country / region&hellip;', 'woocommerce' ); ?></option>
                        <?php
                        foreach ( WC()->countries->get_shipping_countries() as $key => $value ) {
                            echo '<option value="' . esc_attr( $key ) . '"' . selected( WC()->customer->get_shipping_country(), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
                        }
                        ?>
                    </select>
                </p>
                <p class="form-row form-row-wide" id="calc_shipping_state_field">
                    <?php
                    $current_cc = WC()->customer->get_shipping_country();
                    $current_r  = WC()->customer->get_shipping_state();
                    $states     = WC()->countries->get_states( $current_cc );

                    if ( is_array( $states ) && empty( $states ) ) {
                        ?>
                        <input type="hidden" name="calc_shipping_state" id="calc_shipping_state" placeholder="<?php esc_attr_e( 'State / County', 'woocommerce' ); ?>" />
                        <?php
                    } elseif ( is_array( $states ) ) {
                        ?>
                        <span>
						<label for="calc_shipping_state" class="screen-reader-text"><?php esc_html_e( 'State / County:', 'woocommerce' ); ?></label>
						<select name="calc_shipping_state" class="state_select" id="calc_shipping_state" data-placeholder="<?php esc_attr_e( 'State / County', 'woocommerce' ); ?>">
							<option value=""><?php esc_html_e( 'Select an option&hellip;', 'woocommerce' ); ?></option>
							<?php
                            foreach ( $states as $ckey => $cvalue ) {
                                echo '<option value="' . esc_attr( $ckey ) . '" ' . selected( $current_r, $ckey, false ) . '>' . esc_html( $cvalue ) . '</option>';
                            }
                            ?>
						</select>
					</span>
                        <?php
                    } else {
                        ?>
                        <label for="calc_shipping_state" class="screen-reader-text"><?php esc_html_e( 'State / County:', 'woocommerce' ); ?></label>
                        <input type="text" class="input-text" value="<?php echo esc_attr( $current_r ); ?>" placeholder="<?php esc_attr_e( 'State / County', 'woocommerce' ); ?>" name="calc_shipping_state" id="calc_shipping_state" />
                        <?php
                    }
                    ?>
                </p>
                <p>
                    <label>
                        <input type="text" placeholder="Delivery Address" class="shipping-address" />
                    </label>
                </p>
                <p>
                    <label>
                        <input type="datetime-local" placeholder="Date of Receipt" class="shipping-time" />
                    </label>
                </p>
                <p>
                    <label>
                        <input type="number" placeholder="Contact Number" class="shipping-number" />
                    </label>
                </p>
            </div>
            <?php
        }
    }
    ?>
</div>

<!-- Coupon Toggle Section -->
<div class="coupon-section">
    <button id="toggleCoupon" class="toggle-button">Apply Coupon</button>
    <div id="couponContent" style="display:none;">
        <input type="text" id="couponCode" placeholder="Enter your coupon code">
        <button id="applyCoupon" class="apply-button">Apply</button>
        <div id="discountMessage" style="display:none;">
            <p>You saved: <span id="discountAmount">$0.00</span></p>
        </div>
    </div>
</div>


