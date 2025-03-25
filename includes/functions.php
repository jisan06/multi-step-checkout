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

    // Twilio API endpoint
    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_SID . '/Messages.json';
    // Prepare data for the POST request
    $data = [
        'From' => TWILIO_PHONE_NUMBER,
        'To' => $mobile_number,
        'Body' => "Your OTP is: $otp",
    ];

    // Initialize cURL
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, TWILIO_SID . ':' . TWILIO_AUTH_TOKEN);
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

    $countryCode = sanitize_text_field($_POST['countryCode']);
    $mobile = sanitize_text_field($_POST['mobile']);
    $formatMobile = sanitize_text_field($_POST['formatMobile']);
    $otp = sanitize_text_field($_POST['otp']);
    $username = 'user_' . $mobile; // Generate username from mobile number

    if (!isset($_COOKIE['otp']) || $_COOKIE['otp'] != $otp) {
        wp_send_json_error('Invalid OTP!', 400);
    }

    $user_query = new WP_User_Query([
        'meta_key'   => 'xoo_ml_phone_no', // Change this to your actual meta key
        'meta_value' => $mobile,
        'number'     => 1,
    ]);

    $users = $user_query->get_results();
    if (!empty($users)) {
        $user = $users[0];
    } else {
        $user = get_user_by('login', $username);
    }
    if (empty($user)) {
        $random_password = wp_generate_password(); // Generate a random password
        $user_id = wp_create_user($username, $random_password, "{$mobile}@example.com");

        if (is_wp_error($user_id)) {
            echo 'Error creating user.';
            exit;
        }

        // Set user role to 'customer'
        wp_update_user(['ID' => $user_id, 'role' => 'customer']);

        // Store mobile number in user meta
        update_user_meta($user_id, 'xoo_ml_phone_code', $countryCode);
        update_user_meta($user_id, 'xoo_ml_phone_no', $mobile);
        update_user_meta($user_id, 'xoo_ml_phone_display', $formatMobile);

        $user = get_user_by('ID', $user_id);
    }

    // Log in the user
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);
    if( ! get_user_meta($user_id, 'xoo_ml_phone_no', true) ) {
        update_user_meta($user_id, 'xoo_ml_phone_code', $countryCode);
        update_user_meta($user_id, 'xoo_ml_phone_no', $mobile);
        update_user_meta($user_id, 'xoo_ml_phone_display', $formatMobile);
    }
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
    remove_al_coupons();
    $coupon_code = sanitize_text_field($_POST['coupon_code']);
    $wc_coupon = new \WC_Coupon($coupon_code);
    $type = $wc_coupon->get_discount_type();
    WC()->cart->apply_coupon($coupon_code);
    WC()->cart->calculate_totals();

    if( $type == 'percent' ) {
        $discount_amount = '%' . $wc_coupon->get_amount();
    }else {
        $discount_amount = wc_price(WC()->cart->get_coupon_discount_amount($coupon_code));
    }

    wp_send_json_success([
        'discount' => $discount_amount,
        'total' => WC()->cart->get_total() // Return total price after discount
    ]);
}

// Remove All Coupons and Return Updated Cart Total
add_action('wp_ajax_remove_all_coupons', 'remove_all_coupons_ajax');
add_action('wp_ajax_nopriv_remove_all_coupons', 'remove_all_coupons_ajax');

function remove_all_coupons_ajax() {
    remove_al_coupons();
    WC()->cart->calculate_totals();
    wp_send_json_success([
        'total' => WC()->cart->get_total()
    ]);
}

function remove_al_coupons()
{
    $applied_coupons = WC()->cart->get_applied_coupons();
    foreach ($applied_coupons as $coupon) {
        WC()->cart->remove_coupon($coupon);
    }
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
    try {
        // 1. Security Verification
//        if (!check_ajax_referer('place_order_nonce', '_wpnonce', false)) {
//            throw new Exception(__('Security check failed. Please refresh the page and try again.', 'your-textdomain'));
//        }

        // 2. Validate Required Fields
        if (!isset($_POST['shipping_method'], $_POST['shipping_address'])) {
            wp_send_json_error(['message' => 'Incomplete Shipping Information.']);
            return;
        }

        // 3. Initialize WooCommerce Environment
        WC()->frontend_includes();
        WC()->session->set('chosen_shipping_methods', [$_POST['shipping_method']]);
        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();

        // 4. Prepare Order Data
        $order_data = [
            'status' => 'pending',
            'customer_id' => get_current_user_id(),
            'customer_note' => isset($_POST['delivery_note']) ? sanitize_text_field($_POST['delivery_note']) : '',
            'created_via' => 'custom_checkout',
            'cart_hash' => md5(json_encode(WC()->cart->get_cart_for_session()) . time()),
        ];

        // 5. Create Order Through WC_Checkout for Hook Support
        $checkout = WC()->checkout();
        $order_id = $checkout->create_order($order_data);
        $order = wc_get_order($order_id);

        if (!$order) {
            throw new Exception(__('Order creation failed. Please try again.', 'your-textdomain'));
        }

        // 6. Add Products from Cart with Meta Data
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $item_id = $order->add_product(
                $cart_item['data'],
                $cart_item['quantity'],
                [
                    'variation' => $cart_item['variation'],
                    'totals' => [
                        'subtotal' => $cart_item['line_subtotal'],
                        'total' => $cart_item['line_total'],
                    ]
                ]
            );

            // Handle your custom meta data
            $order_item = $order->get_item($item_id);
            if (!empty($cart_item['custom_data'])) {
                $order_item->update_meta_data('custom_data', $cart_item['custom_data']);
            }
            $order_item->save();
        }

        // 7. Handle Shipping Methods
        $shipping_method = sanitize_text_field($_POST['shipping_method']);
        $is_local_pickup = strpos($shipping_method, 'local_pickup') !== false;

        // Create shipping item
        $shipping_item = new WC_Order_Item_Shipping();
        $shipping_item->set_method_id($shipping_method);
        $shipping_item->set_method_title(sanitize_text_field($_POST['shipping_title'] ?? $shipping_method));
        $shipping_item->set_total(floatval($_POST['shipping_cost'] ?? 0));

        // 8. Handle Addresses Based on Shipping Type
        $address_fields = [
            'first_name' => sanitize_text_field($_POST['contact_person']),
            'phone' => sanitize_text_field($_POST['contact_number']),
            'country' => sanitize_text_field($_POST['country']),
        ];

        if ($is_local_pickup) {
            // Local Pickup specific handling
            $region = sanitize_text_field($_POST['region'] ?? '');
            $district = sanitize_text_field($_POST['district'] ?? '');
            $pickup_location = sanitize_text_field($_POST['shipping_address'] ?? '');

            // Add pickup location meta
            $shipping_item->add_meta_data('pickup_region', $region);
            $shipping_item->add_meta_data('pickup_district', $district);
            $shipping_item->add_meta_data('pickup_location', $pickup_location);

            // Set address fields for local pickup
            $address_fields['address_1'] = $pickup_location;
            $address_fields['city'] = $district;
            $address_fields['state'] = $region;
        } else {
            // Regular shipping handling
            $address_fields['address_1'] = sanitize_text_field($_POST['shipping_address'] ?? '');
            $address_fields['city'] = sanitize_text_field($_POST['city'] ?? '');
            $address_fields['state'] = sanitize_text_field($_POST['state'] ?? '');
            $address_fields['postcode'] = sanitize_text_field($_POST['postcode'] ?? '');

            // Add shipping meta
            $shipping_item->add_meta_data('contact_person', sanitize_text_field($_POST['contact_person']));
            $shipping_item->add_meta_data('contact_number', sanitize_text_field($_POST['contact_number']));
        }

        $order->add_item($shipping_item);
        $order->set_address($address_fields, 'billing');
        $order->set_address($address_fields, 'shipping');

        // 9. Apply coupons if any
        if (!empty($_POST['coupon_code'])) {
            $order->apply_coupon(sanitize_text_field($_POST['coupon_code']));
        }

        // 10. Calculate totals after all items are added
        $order->calculate_totals();

        // 11. CRITICAL: Trigger all ShipAny required hooks
        do_action('woocommerce_checkout_create_order_shipping_item', $shipping_item, 'custom_shipping_package', $order);
        do_action('woocommerce_checkout_create_order', $order, $_POST);
        do_action('woocommerce_checkout_update_order_meta', $order_id, $_POST);
        do_action('woocommerce_checkout_order_processed', $order_id, $_POST, $order);

        // ShipAny's auto-create hook (from their init_hooks)
        do_action('woocommerce_payment_successful_result', ['result' => 'success'], $order_id);

        // For HPOS compatibility
        if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil') &&
            \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            do_action('woocommerce_order_status_changed', $order_id, 'checkout-draft', 'pending', $order);
        }

        // 12. Process payment
        $payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
        $payment_method = 'qfpay';
        WC()->session->set('chosen_payment_method', $payment_method);
        if (isset($payment_gateways[$payment_method])) {
            // If no payment gateway or payment processing not needed
            $order->set_payment_method($payment_method);
            $result = $payment_gateways[$payment_method]->process_payment($order_id);

            if ($result['result'] === 'success') {
                WC()->cart->empty_cart();

                // Trigger ShipAny's auto-create via payment successful hook
                do_action('woocommerce_payment_successful_result', $result, $order_id);

                wp_send_json_success([
                    'redirect_url' => $result['redirect'],
                    'order_id' => $order_id
                ]);
            }
        }

        $order->update_status('pending');
        WC()->cart->empty_cart();

        wp_send_json_success([
            'redirect_url' => $order->get_checkout_order_received_url(),
            'order_id' => $order_id
        ]);

    } catch (Exception $e) {
        error_log('ShipAny Order Error: ' . $e->getMessage());
        wp_send_json_error([
            'message' => $e->getMessage(),
            'code' => 'shipany_integration_error'
        ]);
    }
}

//function place_order() {
//    if (!isset($_POST['shipping_method'], $_POST['shipping_address'])) {
//        wp_send_json_error(['message' => 'Incomplete Shipping Information.']);
//        return;
//    }
//
//    // Get WooCommerce Cart and Customer data
//    $cart = WC()->cart->get_cart();
//    $shipping_method = sanitize_text_field($_POST['shipping_method']);
//    $shipping_name = sanitize_text_field($_POST['shipping_name']);
//    $shipping_cost = sanitize_text_field($_POST['shipping_cost']);
//    $shipping_title = sanitize_text_field($_POST['shipping_title']);
//    $payment_method = 'qfpay';
//    $contact_number = sanitize_text_field($_POST['contact_number']);
//    $contact_person = sanitize_text_field($_POST['contact_person']);
//    $delivery_note = sanitize_text_field($_POST['delivery_note']);
//
//    $shipping_address = sanitize_text_field($_POST['shipping_address']);
//
//    $country = sanitize_text_field($_POST['country']); // Get country
//    $country_name = WC()->countries->countries[ $country ];
//    $region = sanitize_text_field($_POST['region']);
//    $district = sanitize_text_field($_POST['district']);
//
//
//    $coupon_code = sanitize_text_field($_POST['coupon_code']); // Get coupon code
//
//    // Create a new order
//    $order = wc_create_order();
//    $order_id = $order->get_id();
//
//    // Add items to the order from the cart
//    foreach ($cart as $cart_item_key => $cart_item) {
//        $product = $cart_item['data'];
//        $quantity = $cart_item['quantity'];
//        $item_id = $order->add_product($product, $quantity);
//        $order_item = $order->get_item($item_id);
//        $item_data = [];
//        $total_kcal = 0;
//        $cart_item_data = apply_filters( 'woocommerce_get_item_data', $item_data, $cart_item );
//        if (!empty($cart_item_data[0]['value'])) {
//            $order_item->update_meta_data('order_meta_bundle_data', $cart_item_data[0]['value']);
//        }
//        if (have_rows('bundle_sub_products', $product->get_id())) {
//            while (have_rows('bundle_sub_products', $product->get_id())) {
//                the_row();
//                $sub_product = get_sub_field('sub_product');
//                $sub_quantity = get_sub_field('quantity');
//
//                if ($sub_product) {
//                    $sub_product_obj = wc_get_product($sub_product);
//                    $sub_product_kcal = (int) $sub_product_obj->get_attribute('kcal');
//
//                    if ($sub_product_kcal) {
//                        $total_kcal += ($sub_product_kcal * $sub_quantity);
//                    }
//                }
//            }
//        }
//        $order_item->update_meta_data('total_kcal', $total_kcal);
//        $order_item->save();
//    }
//
//    // Set the order shipping methods
//    $shipping_item = new WC_Order_Item_Shipping();
//    $shipping_item->set_method_id($shipping_method);
//    $shipping_item->set_method_title($shipping_title . ( $country_name ? '(' . $country_name .')' : '' ));
//    $shipping_item->set_total($shipping_cost);
//
//    if( $shipping_name === 'local_pickup' ) {
//        $lang = 'chinese';
//        $location_json = MSC_PLUGIN_PATH . 'lib/locations.json';
//        $location_data = json_decode(file_get_contents($location_json), true)[$lang];
//        $regions = $location_data['regions'];
//
//
//        foreach ($regions as $region_obj) {
//            if ($region_obj['name'] === $region) { // Find region
//                foreach ($region_obj['districts'] as $district_obj) {
//                    if ($district_obj['name'] === $district) { // Find district
//                        foreach ($district_obj['stores'] as $store) {
//                            if ($store['code'] === $shipping_address) { // Find store by code
//                                $shipping_address = $store['address'];
//                            }
//                        }
//                    }
//                }
//            }
//        }
//
//        $shipping_item->add_meta_data('Region', $region);
//        $shipping_item->add_meta_data('District', $district);
//        $shipping_item->add_meta_data(
//            'Delivery Address',
//            $shipping_address
//        );
//
//        // Set billing and shipping addresses
//        $order->set_address([
//            'address_1' => $shipping_address,
//            'state' => strtoupper($region),
//            'country' => $country,
//        ], 'billing');
//
//        $order->set_address([
//            'address_1' => $shipping_address,
//            'state' => strtoupper($region),
//            'country' => $country,
//        ], 'shipping');
//    }else {
//        $shipping_item->add_meta_data(
//            'Delivery Address',
//            $shipping_address
//        );
//        $shipping_item->add_meta_data('Contact Number', $contact_number);
//        $shipping_item->add_meta_data('Contact Person', $contact_person);
//        $shipping_item->add_meta_data('Delivery Notes', $delivery_note);
//
//        // Set billing and shipping addresses
//        $order->set_address([
//            'address_1' => $shipping_address,
//            'phone' => $contact_number, // Save phone number
//        ], 'billing');
//
//        $order->set_address([
//            'address_1' => $shipping_address,
//            'phone' => $contact_number, // Save phone number
//        ], 'shipping');
//    }
//
//    $order->add_item($shipping_item);
//
//    // Apply coupon code if provided
//    if (!empty($coupon_code)) {
//        $order->apply_coupon($coupon_code);
//    }
//
//    // Set payment method
//    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
//
//    $order->calculate_totals(); // Calculate totals
//
//    $payment_url = '';
//    if( isset( $available_gateways[ $payment_method ] ) ) {
//        $method = $available_gateways[ $payment_method ];
//        $order->set_payment_method($method);
//        $result = $available_gateways[ $payment_method ]->process_payment( $order_id );
//        if ( isset( $result['result'] ) && 'success' === $result['result'] ) {
//            $result = apply_filters( 'woocommerce_payment_successful_result', $result, $order_id );
//            $payment_url = $result['redirect'];
//        }
//    }else {
//        $order->set_payment_method($payment_method);
//    }
//
//    // Update order status
//    $order->update_status('pending'); // Or 'completed', depending on your workflow
//
//    WC()->cart->empty_cart(true);
//    // Send success response
//    wp_send_json_success(['redirect_url' => $payment_url]);
//}

add_filter('woocommerce_get_return_url', function($return_url, $order) {
    if ($order instanceof WC_Order) {
        $order_key = $order->get_order_key(); // Get the WooCommerce order key
        return home_url('/custom-checkout/?order_id=' . $order->get_id() . '&key=' . $order_key);
    }
    return $return_url;
}, 10, 2);

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

add_filter( 'woocommerce_locate_template', 'msc_woocommerce_template', 10000, 3 );
function msc_woocommerce_template( $template, $template_name, $template_path ) {
    if ( 'cart/mini-cart.php' === $template_name ) {
        // Define the path to your custom template in the plugin
        $plugin_template = plugin_dir_path( __FILE__ ) . 'woocommerce/cart/mini-cart.php';

        // If the custom template exists, return it instead of the default
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }
    return $template;
}