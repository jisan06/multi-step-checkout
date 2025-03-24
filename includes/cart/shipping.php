<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$packages = WC()->shipping()->get_packages();
$active_shipping_methods = [];
foreach ($packages as $package) {
    $rates = $package['rates'];
    foreach ($rates as $method) {
        $active_shipping_methods[] = $method;
    }
}
?>

<div class="shipping-methods" style="display: none">
    <?php
        $location_json = MSC_PLUGIN_PATH . 'lib/locations.json';
        $lang = 'english';
        $location_data = json_decode(file_get_contents($location_json), true)[$lang];
        $regions = $location_data['regions'];

        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
        $default_shipping_method = !empty($chosen_shipping_methods) ? $chosen_shipping_methods[0] : '';

        if (!empty($active_shipping_methods)) {
            foreach ($active_shipping_methods as $method) {
                $method_id = esc_attr($method->id);
                $checked = ($method_id === $default_shipping_method || strpos($default_shipping_method, $method_id) === 0) ? 'checked' : '';
                ?>
                <label for="<?php echo $method_id; ?>">
                    <div class="shipping-method" data-method-id="<?php echo $method_id; ?>">
                        <input
                            type="radio"
                            name="shipping_method"
                            value="<?php echo $method_id; ?>"
                            id="<?php echo $method_id; ?>" <?php /*echo $checked; */?>
                            data-cost="<?php echo esc_attr($method->cost); ?>"
                            data-title="<?php echo esc_attr($method->label); ?>"
                            data-slug="<?php echo esc_attr($method->method_id); ?>"
                        >
                        <?php echo esc_html($method->label); ?> - <?php echo wc_price($method->cost); ?>
                        <span class="arrow">→</span> <!-- Right arrow -->
                    </div>
                </label>
                <?php
            }
        }
    ?>

<!--    <button class="next-step disabled">Next Step</button>-->
</div>

<div class="shipping-methods-details" style="display: none">
    <?php
    if (!empty($active_shipping_methods)) {
        foreach ($active_shipping_methods as $method) {
            ?>
            <div class="shipping-fields" data-method-id="<?php echo esc_attr($method->id); ?>" style="display: none;">
                <?php
                if( $method->method_id == 'local_pickup' ) {
                    $ship_regions = [
                        0 => 'HK',
                        2 => 'TW',
                        3 => 'TH',
                    ];
                    $shipany_region = SHIPANY()->get_shipping_shipany_settings()['shipany_region'];
                    $region_short = $ship_regions[$shipany_region];
                ?>
                    <input type="hidden" name="shipping_country" id="shipping_country" value="<?php echo $region_short; ?>">
                    <p class="form-row form-row-wide" id="shipping_region_wrap">
                        <label for="shipping_region"><?php esc_html_e( 'Region', 'woocommerce' ); ?></label>
                        <select name="shipping_region" id="shipping_region" class="shipping_region select2">
                            <option value="">Select Region</option>
                            <?php
                                foreach ($regions as $region) {
                            ?>
                                    <option value="<?php echo $region['name'] ?>"><?php echo $region['name'] ?></option>
                            <?php } ?>
                        </select>
                    </p>
                    <p class="form-row form-row-wide" id="shipping_district_wrap">
                        <label for="shipping_district"><?php esc_html_e( 'District', 'woocommerce' ); ?></label>
                        <select name="shipping_district" id="shipping_district" class="shipping_district select2">
                            <option value="">Select District</option>
                        </select>
                    </p>
                    <p class="form-row form-row-wide" id="shipping_address_wrap">
                        <label for="shipping_address"><?php esc_html_e( 'Address', 'woocommerce' ); ?></label>
                        <select name="shipping_address" id="shipping_address" class="shipping_address select2">
                            <option value="">Select Address</option>
                        </select>
                    </p>

                <?php }else { ?>
                    <p class="delivery-address">
                        <label for="shipping_address">
                            收貨地址
                        </label>
                        <input type="text" placeholder="請輸入收貨地址" id="shipping_address" class="shipping-address" />
                    </p>
                    <p class="contact-number">
                        <label for="shipping_number">
                            聯絡電話
                        </label>
                        <input type="number" placeholder="請輸入聯絡電話" id="shipping_number" class="shipping-number" />
                    </p>
                    <p class="contact-person">
                        <label for="shipping_person">
                            聯絡人
                        </label>
                        <input type="text" placeholder="請輸入聯絡電話" id="shipping_person" class="shipping-person" />
                    </p>
                    <p class="contact-note">
                        <label for="delivery_note">
                            送货单
                        </label>
                        <textarea name="deliver-note" id="delivery_note" class="delivery-note" cols="30" rows="5"></textarea>
                    </p>
                <?php } ?>
            </div>
        <?php } } ?>
    <button class="confirm-data backButton">確認</button>
</div>
