<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $woocommerce;

// Get WooCommerce cart contents
$cart = WC()->cart->get_cart();
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
?>
<div class="cart-items-wrap">
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
                        <div class="cart-item-name-wrap">
                            <div>
                                <?php echo esc_html($product->get_name()); ?>
                            </div>
                            <div>
                                <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                            </div>
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
        if (!empty($active_shipping_methods)) {
            foreach ($active_shipping_methods  as $method) {
                ?>
                <div class="shipping-method" data-method-id="<?php echo esc_attr($method['id']); ?>">
                    <input type="radio" name="shipping_method" value="<?php echo esc_attr($method['id']); ?>" id="<?php echo esc_attr($method['id']); ?>">
                    <label for="<?php echo esc_attr($method['id']); ?>"><?php echo esc_html($method['title']); ?> - <?php echo wc_price($method['cost']); ?></label>
                    <span class="arrow">→</span> <!-- Right arrow -->
                </div>


                <?php
            }
        }
        ?>
    </div>

    <!-- Coupon Toggle Section -->
    <div class="coupon-section">
        <button id="toggleCoupon" class="toggle-button">Coupon</button>
    </div>
</div>

<div class="shipping-methods-details">
    <?php
        if (!empty($active_shipping_methods)) {
        foreach ($active_shipping_methods  as $method) {
    ?>
        <div class="shipping-fields" data-method-id="<?php echo esc_attr($method['id']); ?>" style="display: none;">
        <?php
        if( $method['id'] == 'local_pickup' ) {
            $ship_regions = [
                0 => 'HK',
                2 => 'TW',
                3 => 'TH',
            ];
            $shipany_region = SHIPANY()->get_shipping_shipany_settings()['shipany_region'];
            $region_short = $ship_regions[$shipany_region];
            $states = WC()->countries->get_states( $region_short );
            ?>
            <p class="form-row form-row-wide" id="calc_shipping_country_field">
                <label for="calc_shipping_country"><?php esc_html_e( 'Area', 'woocommerce' ); ?></label>
                <select name="calc_shipping_state" class="state_select" id="calc_shipping_state" data-placeholder="<?php esc_attr_e( 'State', 'woocommerce' ); ?>">
                    <option value=""><?php esc_html_e( 'Select an option&hellip;', 'woocommerce' ); ?></option>
                    <?php
                    foreach ( $states as $ckey => $cvalue ) {
                        echo '<option value="' . esc_attr( $ckey ) . '" ' . selected( $current_r, $ckey, false ) . '>' . esc_html( $cvalue ) . '</option>';
                    }
                    ?>
                </select>
            </p>
            <p>
                <label>
                    Pickup Point Address
                </label>
                <input type="text" placeholder="Pickup Point Address" class="shipping-address" />
            </p>
            <p>
                <label>
                    Pickup Point Number
                </label>
                <input type="number" placeholder="Pickup Point Number" class="shipping-number" />
            </p>
        <?php }else { ?>
            <p class="form-row form-row-wide" id="calc_shipping_country_field">
                <label for="calc_shipping_country"><?php esc_html_e( 'Country / region:', 'woocommerce' ); ?></label>
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
						<label for="calc_shipping_state"><?php esc_html_e( 'State / County:', 'woocommerce' ); ?></label>
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
                    <label for="calc_shipping_state"><?php esc_html_e( 'State / County:', 'woocommerce' ); ?></label>
                    <input type="text" class="input-text" value="<?php echo esc_attr( $current_r ); ?>" placeholder="<?php esc_attr_e( 'State / County', 'woocommerce' ); ?>" name="calc_shipping_state" id="calc_shipping_state" />
                    <?php
                }
                ?>
            </p>
            <p>
                <label>
                    Delivery Address
                </label>
                <input type="text" placeholder="Delivery Address" class="shipping-address" />
            </p>
            <p>
                <label>
                    Date of Receipt
                </label>
                <input type="datetime-local" placeholder="Date of Receipt" class="shipping-receipt-date date-time" />
            </p>
            <p>
                <label>
                    Delivery Time
                </label>
                <input type="datetime-local" placeholder="Delivery Time" class="shipping-time date-time" />
            </p>
            <p>
                <label>
                    Contact Number
                </label>
                <input type="number" placeholder="Contact Number" class="shipping-number" />
            </p>
        <?php } ?>
    </div>
    <?php } } ?>
</div>

<div id="coupon_wrap" style="display: none;">
    <input type="text" id="couponCode" placeholder="Enter your coupon code">
    <button id="applyCoupon" class="apply-button">Apply</button>
    <button id="removeCoupon" class="remove-button" style="display: none;">Remove</button>
    <div id="discountMessage" style="display: none;">
        <p>You saved: <span id="discountAmount">$0.00</span></p>
    </div>
</div>

<div class="msc-nav">
    <div class="msc-nav-left">
        <div class="msc-nav-qty">
            <span class="dashicons dashicons-cart"></span> <?php echo WC()->cart->get_cart_contents_count(); ?>
        </div>
        <div class="msc-nav-total">
            <?php echo WC()->cart->get_total(); ?>
        </div>
    </div>
    <button id="nextStep">Checkout</button>
</div>



