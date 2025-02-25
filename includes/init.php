<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode
function msc_checkout_shortcode() {
    ?>
    <div class="msc-checkout">
        <button id="backButton" style="display: none;">
            <span class="dashicons dashicons-arrow-left-alt2"></span>
        </button>
        <div class="steps">
            <div class="step <?php echo is_user_logged_in() ? 'disabled' : 'active'; ?>" data-step="1">1</div>
            <div class="step <?php echo is_user_logged_in() ? 'active' : 'disabled'; ?>" data-step="2">2</div>
            <div class="step disabled" data-step="3">3</div>
        </div>

        <div class="msc-content">
            <div id="step-1" class="step-content <?php echo is_user_logged_in() ? 'hidden' : 'active'; ?>">
                <?php include MSC_PLUGIN_PATH . 'includes/step-login.php'; ?>
            </div>
            <div id="step-2" class="step-content <?php echo is_user_logged_in() ? 'active' : 'hidden'; ?>">
                <?php include MSC_PLUGIN_PATH . 'includes/step-cart.php'; ?>
            </div>
            <div id="step-3" class="step-content hidden">
                <?php include MSC_PLUGIN_PATH . 'includes/step-checkout.php'; ?>
            </div>
        </div>

    </div>
    <?php
}

if (!is_admin()) {
    add_shortcode('multi_step_checkout_custom', 'msc_checkout_shortcode');
}
