<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }

    // Get current user email
    $user = wp_get_current_user();
    $user_email = $user->user_email;
    $cart_sub_total = WC()->cart->subtotal;

    // Fetch all published coupons
    $args = array(
        'post_type'      => 'shop_coupon',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );

    $coupons = get_posts($args);

    $customer_coupons = [];
    foreach ($coupons as $coupon_post) {
        $coupon = new \WC_Coupon($coupon_post->post_title);

        /* Get coupon by email */
        $allowed_emails = get_post_meta($coupon_post->ID, 'customer_email', true);
        $expiry_date = get_post_meta($coupon_post->ID, 'date_expires', true); // Coupon expiry date

        // Validate expiry date
        if (!empty($expiry_date) && time() > $expiry_date) {
            continue; // Skip expired coupons
        }

        if( !empty($allowed_emails) ) {
            // Convert allowed emails to an array if stored as a string
            if ( !is_array( $allowed_emails ) ) {
                $allowed_emails = explode(',', $allowed_emails);
            }

            // Check if the coupon is restricted to specific users
            if ( in_array($user_email, $allowed_emails) ) {
                $customer_coupons[] = $coupon;
            }
            /* Get coupon by email */
        }

        /* Get coupon by minimum amount */
        $minimum_spend = $coupon->get_minimum_amount();
        if ( ! empty($minimum_spend) && $cart_sub_total >= $minimum_spend ) {
            $customer_coupons[] = $coupon;
        }
        /* Get coupon by minimum amount */
    }
?>

<div id="coupon_wrap" style="display: none;">
    <h3>Available Coupons</h3>
    <div>
        <?php
            if( ! empty($customer_coupons) ) {
            foreach ($customer_coupons as $coupon) {
                $coupon_code = $coupon->get_code(); // Coupon code is the post title
                $coupon = new \WC_Coupon($coupon_code);
                $type = $coupon->get_discount_type();
                if( $type == 'percent' ) {
                    $discount = '%' . $coupon->get_amount();
                }else {
                    $discount = wc_price($coupon->get_amount());
                }
        ?>
                <div class="coupon-items">
                    <div>
                        <div><strong>Coupon:</strong> <?php echo esc_html($coupon_code); ?></div>
                        <div><strong>Discount:</strong> <?php echo $discount; ?></div>
                    </div>
                    <div>
                        <button
                            class="apply-coupon-btn apply-button"
                            data-coupon="<?php echo esc_attr($coupon_code); ?>"
                            style="<?php echo ! empty( $applied_coupons ) ? 'display: none;' : '' ?>"
                        >
                            Apply
                        </button>
                    </div>
                </div>
            <?php } }else { ?>
                <h5>You have no coupon</h5>
            <?php } ?>
    </div>
    <div id="coupon_summary" style="<?php echo empty($applied_coupons) ? 'display: none;' : ''; ?>">
        <p>You saved: <span id="discountAmount"><?php echo $discount_total; ?></span></p>
        <button id="removeCoupon" class="remove-button">Remove</button>
    </div>
    <button class="confirm-data backButton">Confirmation</button>
</div>
