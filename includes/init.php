<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode
function msc_checkout_shortcode() {
    $current_step = 1;
    $is_logged_in = is_user_logged_in();
    if( $is_logged_in && isset($_GET['order_id']) && isset($_GET['key']) ) {
        $current_step = 3;
    }elseif ($is_logged_in) {
        $current_step = 2;
    }
    ?>
    <div class="msc-checkout">
        <button id="backButton" class="backButton" style="display: none;">
            <span class="dashicons dashicons-arrow-left-alt2"></span>
        </button>
        <div class="steps">
            <input class="msc-current-step" type="hidden" value="<?php echo $current_step; ?>">
            <div class="step <?php echo $current_step == 1 ? 'disabled' : 'active'; ?>" data-step="1">1</div>
            <div class="step <?php echo $current_step == 2 ? 'active' : 'disabled'; ?>" data-step="2">2</div>
            <div class="step <?php echo $current_step == 3 ? 'active' : 'disabled' ?>" data-step="3">3</div>
        </div>

        <div class="msc-content">
            <div id="step-1" class="step-content <?php echo $current_step == 1 ? 'active' : 'hidden'; ?>">
			<div class="step-img"><img src="/wp-content/uploads/2025/02/Group-59468-1.png"></div>
                <?php include MSC_PLUGIN_PATH . 'includes/step-login.php'; ?>
            </div>
            <div id="step-2" class="step-content <?php echo $current_step == 2 ? 'active' : 'hidden'; ?>">
			<div class="step-img"><img src="/wp-content/uploads/2025/02/Group-59468-2.png"></div>
                <?php include MSC_PLUGIN_PATH . 'includes/step-cart.php'; ?>
            </div>
            <div id="step-3" class="step-content <?php echo $current_step == 3 ? 'active' : 'hidden'; ?>">
			<div class="step-img"><img src="/wp-content/uploads/2025/02/Group-59468-3.png"></div>
                <?php include MSC_PLUGIN_PATH . 'includes/step-checkout.php'; ?>
            </div>
        </div>

    </div>
    <?php
}

if (!is_admin()) {
    add_shortcode('multi_step_checkout_custom', 'msc_checkout_shortcode');
}
