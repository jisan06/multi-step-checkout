<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="login-tabs">
    <button class="login-tab active" data-target="#mobile-login">Mobile OTP</button>
    <button class="login-tab" data-target="#email-login">Email Login</button>
</div>

<!-- Mobile OTP Login -->
<div id="mobile-login" class="login-content active">
    <p>Enter your mobile number to receive OTP:</p>
    <input type="text" id="mobile-number" placeholder="Mobile Number">
    <button id="send-otp">Send OTP</button>

    <div id="otp-section" style="display: none;">
        <p>Enter the OTP sent to your mobile:</p>
        <input type="text" id="otp-code" placeholder="OTP Code">
        <button id="verify-otp">Login</button>
    </div>
</div>

<!-- Email Login -->
<div id="email-login" class="login-content">
    <p>Enter your email to login:</p>
    <input type="email" id="email-address" placeholder="Email">
    <button id="login-email">Login</button>
</div>
