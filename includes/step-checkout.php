<?php
if (!defined('ABSPATH')) {
    exit;
}
//$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
if (isset($_GET['step']) && $_GET['step'] == 3) {
    $order_id = $_GET['order_id'] ?? '';
    $status = $_GET['status'] ?? '';

    if ($order_id && $status === 'paid') {
        // Mark order as completed
        $order = wc_get_order($order_id);
        if ($order) {
            $order->update_status('completed');
        }
    }
}

?>
<div class="msc-checkout-form">
<!--    <div class="payment-methods-container">-->
<!--        --><?php //if (!empty($available_gateways)) : ?>
<!--            <h3>Select Payment Method</h3>-->
<!--            <div class="payment-methods-grid" id="payment-methods">-->
<!--                --><?php //foreach ($available_gateways as $gateway) : ?>
<!--                    <label for="payment_--><?php //echo esc_attr($gateway->id); ?><!--" class="payment-box">-->
<!--                        <input type="radio" name="payment_method" value="--><?php //echo esc_attr($gateway->id); ?><!--" id="payment_--><?php //echo esc_attr($gateway->id); ?><!--" class="payment-radio">-->
<!--                        <div class="payment-content">-->
<!--                            <strong>--><?php //echo esc_html($gateway->get_title()); ?><!--</strong>-->
<!--                            <p class="payment-description">--><?php //echo wp_kses_post($gateway->get_description()); ?><!--</p>-->
<!--                        </div>-->
<!--                    </label>-->
<!--                --><?php //endforeach; ?>
<!--            </div>-->
<!--        --><?php //else : ?>
<!--            <p>No payment methods available.</p>-->
<!--        --><?php //endif; ?>
<!--        <button type="submit" id="placeOrderButton">Place Order</button>-->
<!--    </div>-->
    <div class="order-success" style="display:none;">Your order has been placed successfully!</div>
    <div class="pay-screen">Payment Screen will appear here!</div>
</div>
