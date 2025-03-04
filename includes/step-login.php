<?php
    if (!defined('ABSPATH')) { exit; }
    $cc_list = xoo_ml_get_country_codes();
?><div class="step-body-content">	<div class="step-heading"><h2>請先登錄</h2></div>
	<div class="login-tabs">
		<button class="login-tab active" data-target="#mobile-login">手機登錄</button>
		<button class="login-tab" data-target="#email-login">電郵登錄</button>
	</div>

	<!-- Mobile OTP Login -->
	<div id="mobile-login" class="login-content active">
		<div class="mobile-number-group">			<label>手機號碼</label>
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
			<input type="text" id="mobile_number" placeholder="輸入手機號碼">
		</div>
		<div id="otp-section">			<label>手機號碼</label>
			<input type="text" id="otp_code" placeholder="輸入一次性驗證碼">
			<button id="send_otp">領取驗證碼</button>
		</div>
		<div>
			<button id="verify_otp">登錄</button>
		</div>
	</div>

	<!-- Email Login -->
	<div id="email-login" class="login-content">		<div class="email-group">		<label>手機號碼</label>
		<div>
			<input type="email" id="email_address" placeholder="輸入電郵地址">
		</div>		</div>
		<div id="otp-section">			<label>手機號碼</label>
			<input type="text" id="email_otp_code" placeholder="輸入一次性驗證碼">
			<button id="send_otp_email">領取驗證碼</button>
		</div>
		<button id="login_email">登錄</button>
	</div></div>
