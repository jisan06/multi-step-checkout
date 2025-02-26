<?php
    if (!defined('ABSPATH')) { exit; }
    $cc_list = xoo_ml_get_country_codes();
?>
<div class="login-tabs">
    <button class="login-tab active" data-target="#mobile-login">Mobile OTP</button>
    <button class="login-tab" data-target="#email-login">Email Login</button>
</div>

<!-- Mobile OTP Login -->
<div id="mobile-login" class="login-content active">
    <div class="mobile-number-group">
        <div class="country-code">
<!--            <select class="xoo-ml-phone-cc" id="msc_country_cc" name="mobile-cc">-->
<!--                <option disabled>--><?php //_e( 'Select Country Code', 'mobile-login-woocommerce' ); ?><!--</option>-->
<!---->
<!--                --><?php //foreach( $cc_list as $country_code => $country_phone_code ): ?>
<!---->
<!--                    <option value="--><?php //echo esc_attr( $country_phone_code ); ?><!--" >--><?php //echo esc_attr( $country_code.' '.$country_phone_code ); ?><!--</option>-->
<!---->
<!--                --><?php //endforeach; ?>
<!---->
<!--            </select>-->
            <input type="text" value="+852" id="msc_country_cc" name="mobile-cc" readonly style="background: #e5e5e5;">
        </div>
        <input type="text" id="mobile_number" placeholder="Mobile Number">
    </div>
    <div id="otp-section">
        <input type="text" id="otp_code" placeholder="OTP Code">
        <button id="send_otp">GET OTP</button>
    </div>
    <div>
        <button id="verify_otp">Login</button>
    </div>
</div>

<!-- Email Login -->
<div id="email-login" class="login-content">
    <div>
        <input type="email" id="email_address" placeholder="Email">
    </div>
    <div id="otp-section">
        <input type="text" id="email_otp_code" placeholder="OTP Code">
        <button id="send_otp_email">GET OTP</button>
    </div>
    <button id="login_email">Login</button>
</div>


<div>
    Not a member yet?
    <a href="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ); ?>">
        Register a new account now
    </a>
</div>
