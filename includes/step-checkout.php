<?php
if (!defined('ABSPATH')) {
    exit;
}
if( $is_logged_in && isset($_GET['order_id']) && isset($_GET['key']) ) {
    $current_step = 3;
}

?>
<div class="msc-checkout-form">
    <span>Successful payment</span>
    <span>We have received your order</span>
    <span>We will arrange to ship your ordered goods as soon as possible</span>

    <div>
        <a href="<?php echo home_url('/catalog') ?>">Back to Contents</a>
    </div>
</div>
