<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_send_otp', 'send_otp'); // For mobile
add_action('wp_ajax_nopriv_send_otp', 'send_otp'); // For mobile
function send_otp() {
    if (!isset($_POST['mobile_number'])) {
        wp_send_json_error('Mobile number is required');
    }
    $mobile_number = sanitize_text_field($_POST['mobile_number']);
    $otp = sanitize_text_field($_POST['otp']);

    // Twilio credentials
    $sid = 'AC7d242030987be8cc3748c9efbf150fc5';
    $token = '70d38a5f7c523fc2dfbaa57f09259777';
    $twilio_number = '+17073531385';

    // Twilio API endpoint
    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $sid . '/Messages.json';
    // Prepare data for the POST request
    $data = [
        'From' => $twilio_number,
        'To' => $mobile_number,
        'Body' => "Your OTP is: $otp",
    ];

    // Initialize cURL
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $token);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    // Execute the cURL request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check if the request was successful
    if ($http_code == 201) {
        // Optionally, store the OTP in a session or database for later verification
        // For example: $_SESSION['otp'] = $otp;

        wp_send_json_success('OTP sent successfully!');
    } else {
        wp_send_json_error('Error sending OTP: ' . $response);
    }

    wp_die(); // Terminate AJAX request
}

add_action('wp_ajax_nopriv_msc_mobile_login', 'msc_mobile_login');
add_action('wp_ajax_msc_mobile_login', 'msc_mobile_login');
function msc_mobile_login() {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'msc_nonce')) {
        wp_send_json_error('Nonce verification failed!', 400);
    }
    if (!isset($_POST['mobile'])) {
        wp_send_json_error('Invalid request!', 400);
    }

    $mobile = sanitize_text_field($_POST['mobile']);
    $otp = sanitize_text_field($_POST['otp']);

    if (!isset($_COOKIE['otp']) || $_COOKIE['otp'] != $otp) {
        wp_send_json_error('Invalid OTP!', 400);
    }

    $user_query = new WP_User_Query([
        'meta_key'   => 'xoo_ml_phone_no', // Change this to your actual meta key
        'meta_value' => $mobile,
        'number'     => 1,
    ]);

    $users = $user_query->get_results();
    $user  = !empty($users) ? $users[0] : '';
    if (empty($user)) {
        $username = 'user_' . $mobile; // Generate username from mobile number
        $random_password = wp_generate_password(); // Generate a random password
        $user_id = wp_create_user($username, $random_password, "{$mobile}@example.com");

        if (is_wp_error($user_id)) {
            echo 'Error creating user.';
            exit;
        }

        // Set user role to 'customer'
        wp_update_user(['ID' => $user_id, 'role' => 'customer']);

        // Store mobile number in user meta
        update_user_meta($user_id, 'mobile_number', $mobile);

        $user = get_user_by('ID', $user_id);
    }

    // Log in the user
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);
    wp_send_json_success(['message' => 'Login successful']);
}

add_action('wp_ajax_send_otp_email', 'send_otp_email'); // For mobile
add_action('wp_ajax_nopriv_send_otp_email', 'send_otp_email'); // For mobile
function send_otp_email() {
    if (!isset($_POST['email'])) {
        wp_send_json_error('Email address is required');
    }

    $email = sanitize_text_field($_POST['email']);

    $otp = sanitize_text_field($_POST['otp']);
    $subject = "Your OTP Code";
    $message = "Your OTP for verification is: <strong>{$otp}</strong>. This code is valid for 3 minutes.";
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    if (wp_mail($email, $subject, $message, $headers)) {
        wp_send_json_success(['message' => 'OTP sent to tour email!']);
    } else {
        wp_send_json_error(['message' => 'Failed to send OTP.']);
    }

    wp_die(); // Terminate AJAX request
}

add_action('wp_ajax_email_login', 'handle_email_login'); // For logged-in users
add_action('wp_ajax_nopriv_email_login', 'handle_email_login'); // For non-logged-in users
function handle_email_login() {
    // Check if email and password are set
    if (!isset($_POST['email'])) {
        wp_send_json_error('Email are required');
    }

    $email = sanitize_email($_POST['email']);
    $otp = sanitize_text_field($_POST['otp']);

    if (!isset($_COOKIE['otp']) || $_COOKIE['otp'] != $otp) {
        wp_send_json_error('Invalid OTP!', 400);
    }

    // Authenticate user
    $user = get_user_by('email', $email);
    if (empty($user)) {
        $username = explode('@', $email)[0]; // Use email prefix as username
        $random_password = wp_generate_password(); // Generate a random password
        $user_id = wp_create_user($username, $random_password, $email);

        if (is_wp_error($user_id)) {
            echo 'Error creating user.';
            exit;
        }
        wp_update_user(['ID' => $user_id, 'role' => 'customer']);
        $user = get_user_by('ID', $user_id);
    }

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID); // Set the authentication cookie

    wp_send_json_success('Login successful');
    wp_die(); // Terminate AJAX request
}

// Apply Coupon and Return Discount + Total Cart Amount
add_action('wp_ajax_apply_coupon', 'apply_coupon_ajax');
add_action('wp_ajax_nopriv_apply_coupon', 'apply_coupon_ajax');

function apply_coupon_ajax() {
    if (!isset($_POST['coupon_code'])) {
        wp_send_json_error(['message' => 'No coupon provided']);
    }

    $coupon_code = sanitize_text_field($_POST['coupon_code']);
    WC()->cart->apply_coupon($coupon_code);
    WC()->cart->calculate_totals();

    $discount_amount = WC()->cart->get_coupon_discount_amount($coupon_code);

    wp_send_json_success([
        'discount' => wc_price($discount_amount),
        'total' => WC()->cart->get_total() // Return total price after discount
    ]);
}

// Remove All Coupons and Return Updated Cart Total
add_action('wp_ajax_remove_all_coupons', 'remove_all_coupons_ajax');
add_action('wp_ajax_nopriv_remove_all_coupons', 'remove_all_coupons_ajax');

function remove_all_coupons_ajax() {
    $applied_coupons = WC()->cart->get_applied_coupons();
    foreach ($applied_coupons as $coupon) {
        WC()->cart->remove_coupon($coupon);
    }
    WC()->cart->calculate_totals();
    wp_send_json_success([
        'total' => WC()->cart->get_total()
    ]);
}

add_action('wp_ajax_update_shipping', 'update_shipping');
add_action('wp_ajax_nopriv_update_shipping', 'update_shipping');

function update_shipping() {
    if (!isset($_POST['shipping_method'])) {
        wp_send_json_error(['message' => 'Shipping method not provided.']);
    }

    $shipping_method = sanitize_text_field($_POST['shipping_method']);
    $packages = WC()->shipping()->get_packages();

    // Set the selected shipping method in WooCommerce session
    WC()->session->set('chosen_shipping_methods', [$shipping_method]);

    // Recalculate totals
    WC()->cart->calculate_totals();

    $new_total = WC()->cart->get_total(); // Get updated total
    wp_send_json_success(['total' => $new_total]);
}

//Place order
add_action('wp_ajax_place_order', 'place_order');
add_action('wp_ajax_nopriv_place_order', 'place_order');
function place_order() {
    if (!isset($_POST['shipping_method'], $_POST['shipping_address'])) {
        wp_send_json_error(['message' => 'Incomplete order data.']);
        return;
    }

    // Get WooCommerce Cart and Customer data
    $cart = WC()->cart->get_cart();
    $shipping_method = sanitize_text_field($_POST['shipping_method']);
    $payment_method = sanitize_text_field($_POST['payment_method']);
    $shipping_address = sanitize_text_field($_POST['shipping_address']);
    $country = sanitize_text_field($_POST['country']); // Get country
    $state = sanitize_text_field($_POST['state']); // Get state
    $contact_number = sanitize_text_field($_POST['contact_number']);
    $coupon_code = sanitize_text_field($_POST['coupon_code']); // Get coupon code
    $receipt_date = sanitize_text_field($_POST['receipt_date']); // Get receipt date
    $delivery_time = sanitize_text_field($_POST['delivery_time']);

    // Create a new order
    $order = wc_create_order();

    // Add items to the order from the cart
    foreach ($cart as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $quantity = $cart_item['quantity'];
        $item_id = $order->add_product($product, $quantity);
        $order_item = $order->get_item($item_id);
        $item_data = [];
        $total_kcal = 0;
        $cart_item_data = apply_filters( 'woocommerce_get_item_data', $item_data, $cart_item );
        if (!empty($cart_item_data[0]['value'])) {
            $order_item->update_meta_data('order_meta_bundle_data', $cart_item_data[0]['value']);
        }
        if (have_rows('bundle_sub_products', $product->get_id())) {
            while (have_rows('bundle_sub_products', $product->get_id())) {
                the_row();
                $sub_product = get_sub_field('sub_product');
                $sub_quantity = get_sub_field('quantity');

                if ($sub_product) {
                    $sub_product_obj = wc_get_product($sub_product);
                    $sub_product_kcal = (int) $sub_product_obj->get_attribute('kcal');

                    if ($sub_product_kcal) {
                        $total_kcal += ($sub_product_kcal * $sub_quantity);
                    }
                }
            }
        }
        $order_item->update_meta_data('total_kcal', $total_kcal);
        $order_item->save();
    }

    // Set the order shipping methods
    $shipping_item = new WC_Order_Item_Shipping();
    $shipping_item->set_method_id($shipping_method);
    $order->add_item($shipping_item);

    // Set billing and shipping addresses
    $order->set_address([
        'address_1' => $shipping_address,
        'city' => '', // Add city if you have it
        'state' => $state,
        'postcode' => '', // Add postcode if you have it
        'country' => $country,
        'email' => '', // Add email if you have it
        'phone' => $contact_number, // Save phone number
    ], 'billing');

    $order->set_address([
        'address_1' => $shipping_address,
        'city' => '', // Add city if you have it
        'state' => $state,
        'postcode' => '', // Add postcode if you have it
        'country' => $country,
        'email' => '', // Add email if you have it
        'phone' => $contact_number, // Save phone number
    ], 'shipping');

    if( $receipt_date ) {
        $order->update_meta_data('receipt_date', $receipt_date);
    }
    if( $delivery_time ) {
        $order->update_meta_data('delivery_time', $delivery_time);
    }

    // Set payment method
//    $order->set_payment_method($payment_method);

    // Apply coupon code if provided
    if (!empty($coupon_code)) {
        $order->apply_coupon($coupon_code);
    }

    $order->calculate_totals(); // Calculate totals

    // Update order status
    $order->update_status('pending'); // Or 'completed', depending on your workflow

    $payment_url = get_qfpay_payment_url($order->get_id(), $order->get_total());

//    if (!$payment_url) {
//        wp_send_json_error('Payment initiation failed.');
//    }

//    WC()->cart->empty_cart();
    // Send success response
    wp_send_json_success(['redirect_url' => $payment_url]);
}

function get_qfpay_payment_url($order_id, $total) {
    $callback_url = home_url('/wp-admin/admin-ajax.php?action=qfpay_payment_callback');
    $request_data = [
        'order_id' => $order_id,
        'amount' => $total,
        'callback_url' => $callback_url,
        'return_url' => home_url('/custom-checkout?step=3&order_id=' . $order_id .'&status=paid'),
    ];

    $response = wp_remote_post('https://qfpay-api.com/payment', [
        'body'    => json_encode($request_data),
        'headers' => ['Content-Type' => 'application/json'],
    ]);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['payment_url'] ?? false;
    }

    return false;
}

add_action('wp_ajax_nopriv_qfpay_payment_callback', 'qfpay_payment_callback');
function qfpay_payment_callback() {
    $order_id = $_POST['order_id'] ?? '';
    $status = $_POST['status'] ?? '';

    if ($order_id && $status === 'paid') {
        $order = wc_get_order($order_id);
        if ($order) {
            $order->update_status('completed');
        }
    }

    wp_send_json_success(['message' => 'Payment updated successfully.']);
}

add_action('woocommerce_after_order_itemmeta', 'display_meta_after_order_name', 10, 3);
function display_meta_after_order_name($item_id, $item, $product) {
    $total_kcal = $item->get_meta('total_kcal');
    $bundle_data = $item->get_meta('order_meta_bundle_data');
    if (!empty($total_kcal)) {
        echo '<div class="product-kcal">' . $total_kcal . 'Kcal</div>';
    }
    if (!empty($bundle_data)) {
        echo '<div class="product-bundle-data">' . wp_kses_post($bundle_data) . '</div>';
    }
}

add_filter('woocommerce_order_item_get_formatted_meta_data', 'remove_custom_data', 10, 1);

function remove_custom_data($formatted_meta) {
    foreach ($formatted_meta as $key => $meta) {
        if ($meta->key === 'order_meta_bundle_data') {
            unset($formatted_meta[$key]);
        }elseif ($meta->key === 'total_kcal') {
            unset($formatted_meta[$key]);
        }
    }
    return $formatted_meta;
}


