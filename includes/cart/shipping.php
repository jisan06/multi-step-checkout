<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
?>

<div class="shipping-methods-details">
    <?php
    if (!empty($active_shipping_methods)) {
        foreach ($active_shipping_methods as $method) {
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
