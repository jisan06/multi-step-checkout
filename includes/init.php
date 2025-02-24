<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode
function msc_checkout_shortcode() {
    ?>
    <div class="msc-checkout">
        <div class="steps">
            <div class="step active">1</div>
            <div class="step">2</div>
            <div class="step">3</div>
        </div>

        <div class="msc-content">
            <div id="step-1" class="step-content active"><?php include MSC_PLUGIN_PATH . 'includes/step-login.php'; ?></div>
            <div id="step-2" class="step-content"><?php include MSC_PLUGIN_PATH . 'includes/step-cart.php'; ?></div>
            <div id="step-3" class="step-content"><?php include MSC_PLUGIN_PATH . 'includes/step-checkout.php'; ?></div>
        </div>

        <div class="msc-nav">
            <button id="prevStep" class="hidden">Previous</button>
            <button id="nextStep">Next</button>
        </div>
    </div>
    <?php
}

if (!is_admin()) {
    add_shortcode('multi_step_checkout_custom', 'msc_checkout_shortcode');
}
