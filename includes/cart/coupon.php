<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }

    // Get current user email
    $user = wp_get_current_user();
    $user_email = $user->user_email;

    // Fetch all published coupons
    $args = array(
        'post_type'      => 'shop_coupon',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );

    $coupons = get_posts($args);
    $applied_coupons = WC()->cart->get_applied_coupons();
    $discount_total = 0;

    if (!empty($applied_coupons)) {
        foreach ($applied_coupons as $coupon_code) {
            $discount_total += WC()->cart->get_coupon_discount_amount($coupon_code);
        }
    }

    $customer_coupons = [];
    foreach ($coupons as $coupon) {
        $allowed_emails = get_post_meta($coupon->ID, 'customer_email', true);
        $expiry_date = get_post_meta($coupon->ID, 'date_expires', true); // Coupon expiry date

        // Convert allowed emails to an array if stored as a string
        if (!empty($allowed_emails) && !is_array($allowed_emails)) {
            $allowed_emails = explode(',', $allowed_emails);
        }

        // Validate expiry date
        if (!empty($expiry_date) && time() > $expiry_date) {
            continue; // Skip expired coupons
        }

        // Check if the coupon is restricted to specific users
        if (!empty($allowed_emails) && in_array($user_email, $allowed_emails)) {
            $customer_coupons[] = $coupon;
        }
    }
?>

<div id="coupon_wrap" style="display: none;">
    <h3>Available Coupons</h3>
    <div>
        <?php
            if( ! empty($customer_coupons) ) {
            foreach ($customer_coupons as $coupon) {
                $coupon_code = $coupon->post_title; // Coupon code is the post title
                $wc_coupon = new WC_Coupon($coupon_code);
                $discount = $wc_coupon->get_amount();
                ?>
                <div class="coupon-items">
                    <div>
                        <div><strong>Coupon:</strong> <?php echo esc_html($coupon_code); ?></div>
                        <div><strong>Discount:</strong> <?php echo wc_price($discount); ?></div>
                    </div>
                    <div>
                        <button class="apply-coupon-btn apply-button" data-coupon="<?php echo esc_attr($coupon_code); ?>">Apply</button>
                    </div>
                </div>
            <?php } }else { ?>
                <h5>You have no coupon</h5>
            <?php } ?>
    </div>
    <div id="coupon_summary" style="<?php echo empty($applied_coupons) ? 'display: none;' : ''; ?>">
        <p>You saved: <span id="discountAmount"><?php echo wc_price($discount_total); ?></span></p>
        <button id="removeCoupon" class="remove-button">Remove</button>
    </div>
    <button class="confirm-data backButton">Confirmation</button>
</div>
