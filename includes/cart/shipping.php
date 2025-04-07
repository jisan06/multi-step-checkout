<?php

if (!defined('ABSPATH')) {

    exit; // Exit if accessed directly

}
    $areas = [
        '港島 – 中西區' ,
        '港島 – 灣仔',
        '港島 – 東區',
        '港島 – 南區',
        '九龍 – 油尖旺',
        '九龍 – 深水埗',
        '九龍 – 九龍城',
        '九龍 – 黃大仙',
        '九龍 – 觀塘',
        '新界 – 葵青',
        '新界 – 荃灣',
        '新界 – 屯門',
        '新界 – 元朗',
        '新界 – 北區',
        '新界 – 大埔',
        '新界 – 沙田',
        '新界 – 西貢' ,
        '新界 – 離島'
    ];
    $delivery_times = [
        '9:00 – 12:00' ,
        '13:00 – 17:00' ,
        '18:00 – 21:00' ,
    ];
?>


<div class="shipping-methods" style="display: none">

    <?php

    $location_json = MSC_PLUGIN_PATH . 'lib/locations.json';

    $lang = 'chinese';

    $location_data = json_decode(file_get_contents($location_json), true)[$lang];

    $regions = $location_data['regions'];

    if (!empty($active_shipping_methods)) {
        foreach ($active_shipping_methods as $index => $method) {
            $method_id = esc_attr($method->id);
            if($method_id != $free_shipping_id) {
                $method_Price = $free_shipping_id
                    ? '<div class="del-price">' . wc_price($method->cost) .'<span class="free-text">免運費</span></div> '
                    : wc_price($method->cost);
                $checked = ($method_id === $default_shipping_method || strpos($default_shipping_method, $method_id) === 0) ? 'checked' : '';
                ?>
                <label for="<?php echo $method_id; ?>">
                    <div
                            class="shipping-method <?php echo $checked ? 'active' : '' ?>"
                    >
                        <input
                                type="radio"
                                name="shipping_method"
                                value="<?php echo $method_id; ?>"
                                id="<?php echo $method_id; ?>" <?php echo $checked; ?>
                                data-method-name="<?php echo esc_attr($method->method_id); ?>"
                                data-title="<?php echo esc_attr($method->label); ?>"
                                data-cost="<?php echo esc_attr($method->cost); ?>"
                                style="display: none"
                        >
                        <?php echo esc_html($method->label); ?> +
                        <?php echo $method_Price; ?>
                        <span class="arrow"><img src="/wp-content/uploads/2025/02/arrow_forward_ios.png"></span> <!-- Right arrow -->
                    </div>
                </label>

                <?php
            }else {
                ?>
                <input type="hidden" value="<?php echo $free_shipping_id; ?>" class="free_shipping_id">
                <?php
            }
        }
    }
    ?>


    <!--    <button class="next-step disabled">Next Step</button>-->

</div>



<div class="shipping-methods-details" style="display: none">
    <?php
    if (!empty($active_shipping_methods)) {
        foreach ($active_shipping_methods as $method) {
            if($method->method_id != $free_shipping_id) {
                ?>
                <div
                        class="shipping-fields"
                        data-method-id="<?php echo esc_attr($method->id); ?>"
                        data-method-name="<?php echo esc_attr($method->method_id); ?>"
                        style="display: none;"
                >
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

                            <label for="shipping_region">地區</label>

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

                            <label for="shipping_district">區</label>

                            <select name="shipping_district" id="shipping_district" class="shipping_district select2">

                                <option value="">Select District</option>

                            </select>

                        </p>

                        <p class="form-row form-row-wide" id="shipping_address_wrap">

                            <label for="shipping_address">地址</label>

                            <select name="shipping_address" id="shipping_address" class="shipping_address select2">

                                <option value="">Select Address</option>

                            </select>

                        </p>



                    <?php }else { ?>

                        <p>

                            <label for="shipping_area">

                                地區

                            </label>

                            <select name="shipping_area" id="shipping_area" class="shipping-area">

                                <option value="">請選擇地區</option>

                                <?php

                                foreach ($areas as $area) {

                                    ?>

                                    <option value="<?php echo $area ?>"><?php echo $area ?></option>

                                <?php } ?>

                            </select>

                        </p>

                        <p>

                            <label for="shipping_address">

                                收貨地址

                            </label>

                            <input type="text" placeholder="請輸入收貨地址" id="shipping_address" class="shipping-address" />

                        </p>

                        <p>

                            <label for="shipping_date">

                                收貨日期

                            </label>

                            <input type="date" placeholder="請選擇收貨日期" id="shipping_date" class="shipping-date" />

                        </p>

                        <p>

                            <label for="shipping_time">

                                收貨時段

                            </label>

                            <select name="shipping_time" id="shipping_time" class="shipping-time">

                                <option value="">請選擇收貨時段</option>

                                <?php

                                foreach ($delivery_times as $time) {

                                    ?>

                                    <option value="<?php echo $time ?>"><?php echo $time ?></option>

                                <?php } ?>

                            </select>

                        </p>

                        <p class="contact-number">

                            <label for="shipping_number">

                                聯絡電話

                            </label>

                            <input type="text" placeholder="請輸入聯絡電話" id="shipping_number" class="shipping-number" />

                        </p>

                    <?php } ?>

                </div>

            <?php } } } ?>

    <button class="confirm-data backButton">確認</button>

</div>