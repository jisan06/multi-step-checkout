<?php
if (!defined('ABSPATH')) {
    exit;
}
if( $is_logged_in && isset($_GET['order_id']) && isset($_GET['key']) ) {
    $current_step = 3;
}

?>
<div class="msc-checkout-form">
    <div class="middle-content">
        <img src="/wp-content/uploads/2025/03/Group-7716.png">
        <h2>成功付款</h2>
        <h3>我們已經收到您的訂單</h3>
        <p>我們會盡快安排寄出您訂購的商品</p>
    </div>
    <div class="success-btn">
        <a href="#">返回目錄</a>
    </div>
</div>
