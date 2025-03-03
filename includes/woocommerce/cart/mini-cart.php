<?php
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'msc_render_mini_cart_item' ) ) {
    function msc_render_mini_cart_item( $cart_item_key, $cart_item ) {
        $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        $is_product_visible = ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) );

        if ( ! $is_product_visible ) {
            return;
        }

        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
        $product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
        ?>
        <div class="elementor-menu-cart__product woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">

            <div class="elementor-menu-cart__product-image msc-image product-thumbnail">
                <?php
                $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

                if ( ! $product_permalink ) :
                    echo wp_kses_post( $thumbnail );
                else :
                    printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), wp_kses_post( $thumbnail ) );
                endif;
                ?>
            </div>

            <div class="elementor-menu-cart__product-name product-name" data-title="<?php echo esc_attr__( 'Product', 'elementor-pro' ); ?>">
                <?php
                if ( ! $product_permalink ) :
                    echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
                else :
                    echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                endif;

                do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

                // Meta data.
                echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
            </div>

            <div class="woosb-price-quantity">
                <div>
                    <div><?php echo $product_price; ?></div>
                    <div>
                        <?php
                        $tags = get_the_terms($product_id, 'product_tag');
                        if (!empty($tags[0]->name)) {
                            echo '<div>' . $tags[0]->name .'</div>';
                        }
                        ?>
                    </div>
                </div>
                <div class="woosb-quantity">
                    <button class="woosb-quantity-minus" data-product-id="<?php echo esc_attr($product_id);?>">−</button>
                    <input
                        type="number"
                        class="woosb-quantity-input" id="quantity_<?php echo esc_attr($product_id);?>"
                        name="quantity"
                        value="<?php echo $cart_item['quantity'] ?>"
                        min="0"
                        data-product-id="<?php echo $product_id?>"
                    >
                    <button class="woosb-quantity-plus" data-product-id="<?php echo esc_attr($product_id);?>">+</button>
                </div>
            </div>


        </div>
        <?php
    }
}

$cart_items = WC()->cart->get_cart();

if ( empty( $cart_items ) ) { ?>
    <div class="woocommerce-mini-cart__empty-message"><?php echo esc_attr__( 'No products in the cart.', 'elementor-pro' ); ?></div>
<?php } else {
    $cart_count = WC()->cart->get_cart_contents_count();
    $btn_class =  $cart_count  > 5 ? '' : 'disabled';
?>
    <div class="elementor-menu-cart__products woocommerce-mini-cart cart woocommerce-cart-form__contents">
        <div>
            <div class="msc-mini-add-more-wrap" style="display:none;">
                Add <span class="msc-mini-add-more"></span> more meals to your order!
            </div>
            <div>
                <button id="woosb-multi-mini-add-to-cart" class="button add-to-cart-button <?php echo $btn_class; ?>">立即下單</button>
            </div>
        </div>
        <?php
        do_action( 'woocommerce_before_mini_cart_contents' );

        foreach ( $cart_items as $cart_item_key => $cart_item ) {
            msc_render_mini_cart_item( $cart_item_key, $cart_item );
        }

        do_action( 'woocommerce_mini_cart_contents' );
        ?>
    </div>

    <div class="elementor-menu-cart__footer-buttons">
        <div>
            <div class="msc-mini-qty-wrap">
                <span class="dashicons dashicons-cart"></span>
                <span class="msc-mini-qty"><?php echo $cart_count; ?></span>
            </div>
            <div class="msc-mini-total">
                <?php echo WC()->cart->get_total(); ?>
            </div>
        </div>
        <div>
            <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="elementor-button elementor-button--checkout elementor-size-md">
                <span class="elementor-button-text"><?php echo esc_html__( 'Order Now', 'woocommerce' ); // phpcs:ignore WordPress.WP.I18n ?></span>
            </a>
            <div>6餐起送貨</div>
        </div>
    </div>
    <?php
}