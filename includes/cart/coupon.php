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
//    $allowed_emails = get_post_meta($coupon_post->ID, 'customer_email', true);
//    $expiry_date = get_post_meta($coupon_post->ID, 'date_expires', true); // Coupon expiry date
//
//    // Validate expiry date
//    if (!empty($expiry_date) && time() > $expiry_date) {
//        continue; // Skip expired coupons
//    }
//
//    if( !empty($allowed_emails) ) {
//        // Convert allowed emails to an array if stored as a string
//        if ( !is_array( $allowed_emails ) ) {
//            $allowed_emails = explode(',', $allowed_emails);
//        }
//
//        // Check if the coupon is restricted to specific users
//        if ( in_array($user_email, $allowed_emails) ) {
//            $customer_coupons[] = $coupon;
//        }else {
//            continue;
//        }
//        /* Get coupon by email */
//    }
//
//    /* Get coupon by minimum amount */
//    $minimum_spend = $coupon->get_minimum_amount();
//    if ( ! empty($minimum_spend) && $cart_sub_total >= $minimum_spend ) {
//        $customer_coupons[] = $coupon;
//    }
    if ($coupon->is_valid()) {
        $customer_coupons[] = $coupon;
    }
    /* Get coupon by minimum amount */
}
?>

<div id="coupon_wrap" style="display: none;">
    <h3>你的優惠券</h3>
    <div class="coupon-items-wrap">
        <?php
        if( ! empty($customer_coupons) ) {
            $checked = '';
            foreach ($customer_coupons as $coupon) {
                $coupon_expiry = $coupon->get_date_expires();
                if ($coupon_expiry) {
                    $expiry_date = $coupon_expiry->date('Y-m-d'); // Format as needed
                } else {
                    $expiry_date = 'No expiry';
                }
                $coupon_code = $coupon->get_code(); // Coupon code is the post title
                $coupon = new \WC_Coupon($coupon_code);
                $checked = $current_coupon == $coupon_code ? 'checked' : '';
                $type = $coupon->get_discount_type();
                if( $type == 'percent' ) {
                    $discount = '%' . $coupon->get_amount();
                }else {
                    $discount = wc_price($coupon->get_amount());
                }
                ?>
                <div class="coupon-items">
                    <div class="coupon-price">
                        <span class="coupon-minus">-</span><?php echo $discount; ?>
                        <?php
                        // Get description
                        //$description = get_post_field('post_content', $wc_coupon);
                        //echo 'Coupon Description: ' . $description . '<br>';
                        ?>
                    </div>
                    <div class="coupon-details">
                        <div class="code-label"><?php echo esc_html($coupon_code); ?></div>
                        <div class="coupon-date">
                            <div><?php echo '有效期至 ' . $expiry_date; // Format as YYYY-MM-DD?></div>							<div><a class="coupon-terms">使用規則 ❯</a></div>						</div>
                    </div>
                    <div class="coupon-btn">
<!--                        <button-->
<!--                                class="apply-coupon-btn apply-button"-->
<!--                                data-coupon="--><?php //echo esc_attr($coupon_code); ?><!--"-->
<!--                                style="--><?php //echo ! empty( $applied_coupons ) ? 'display: none;' : '' ?><!--"-->
<!--                        >-->
<!--                            確認-->
<!--                        </button>-->
                        <label for="coupon_<?php echo esc_attr($coupon_code); ?>">
                            <input
                                type="checkbox"
                                id="coupon_<?php echo esc_attr($coupon_code); ?>"
                                class="apply-coupon-checkmark"
                                value="<?php echo esc_attr($coupon_code); ?>"
                                <?php echo $checked; ?>
                            >
                        </label>
                    </div>
                </div>
            <?php } }else { ?>
            <h5>You have no coupon</h5>
        <?php } ?>
    </div>
<!--    <div id="coupon_summary" style="--><?php //echo empty($applied_coupons) ? 'display: none;' : ''; ?><!--">-->
<!--        <p>You saved: <span id="discountAmount">--><?php //echo $discount_total; ?><!--</span></p>-->
<!--        <button id="removeCoupon" class="remove-button">Remove</button>-->
<!--    </div>-->
    <button class="confirm-data backButton">確認</button>
</div>
