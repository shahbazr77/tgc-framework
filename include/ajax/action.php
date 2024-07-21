<?php
add_filter('woocommerce_add_to_cart_fragments', 'refresh_cart_count', 50, 1);
if (!function_exists('refresh_cart_count')) {
    function refresh_cart_count($fragments)
    {
        ob_start();
        ?>
        <div class="counter cart-count">
            <?php
            global $woocommerce;
            ?>
            <?php
            echo do_shortcode('[woocommerce_cart]');
            ?>
        </div>
        <?php
        $fragments['.cart-count'] = ob_get_clean();
        return $fragments;
    }
}
add_action('wp_ajax_update_item_from_cart', 'update_item_from_cart');
add_action('wp_ajax_nopriv_update_item_from_cart', 'update_item_from_cart');
if (!function_exists('update_item_from_cart')) {
    function update_item_from_cart()
    {
        $cart_item_key = $_POST['item_key'];
        $threeball_product_values = WC()->cart->get_cart_item($cart_item_key);
        $threeball_product_quantity = apply_filters('woocommerce_stock_amount_cart_item', apply_filters('woocommerce_stock_amount', preg_replace("/[^0-9\.]/", '', filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT))), $cart_item_key);
        $passed_validation = apply_filters('woocommerce_update_cart_validation', true, $cart_item_key, $threeball_product_values, $threeball_product_quantity);
        if ($passed_validation) {
            WC()->cart->set_quantity($cart_item_key, $threeball_product_quantity, true);
        }
        $return_data = do_shortcode('[woocommerce_cart]');
        $return = array('res_filter_data' => $return_data);
        wp_send_json_success($return);
        die();
    }
}
if (!function_exists('woocommerce_button_proceed_to_checkout')) {
    function woocommerce_button_proceed_to_checkout()
    {
        $new_checkout_url = wc_get_checkout_url();
        ?>
        <a href="<?php echo $new_checkout_url; ?>" class="checkout-button button alt wc-forward">
            <?php _e('Checkout', 'tgc-framework'); ?></a>
        <?php
    }
}
add_action('wp_ajax_product_remove', 'product_remove');
add_action('wp_ajax_nopriv_product_remove', 'product_remove');
if (!function_exists('product_remove')) {
    function product_remove()
    {
        global $wpdb, $woocommerce;
        session_start();
        foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $_POST['product_id']) {
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }
        $return_data = do_shortcode('[woocommerce_cart]');
        $return = array('res_filter_data' => $return_data,'cart_status'=>WC()->cart->is_empty());
        wp_send_json_success($return);
        die();

    }
}
add_action('wp_ajax_spyr_coupon_redeem_handler', 'spyr_coupon_redeem_handler');
add_action('wp_ajax_nopriv_spyr_coupon_redeem_handler', 'spyr_coupon_redeem_handler');
if (!function_exists('spyr_coupon_redeem_handler')) {
    function spyr_coupon_redeem_handler()
    {
        global $wpdb, $woocommerce;
        $code = $_REQUEST['coupon_code'];
        $coupon = new \WC_Coupon($code);
        $discounts = new \WC_Discounts(WC()->cart);
        $valid_response = $discounts->is_coupon_valid($coupon);
        if (empty($code) || !isset($code)) {
            $response = array(
                'result' => 'isempty',
            );
            wp_send_json_error($response);
            exit();
        } elseif (is_wp_error($valid_response)) {
            $response = array(
                'result' => 'isinvalid',
            );
            wp_send_json_error($response);
            exit();
        } else {
            $coupon_code = $code;
            $coupon_status = WC()->cart->apply_coupon($coupon_code);
            $return_data = do_shortcode('[woocommerce_cart]');
            $response = array('res_filter_data' => $return_data);
            wp_send_json_success($response);
            exit();
        }
    }
}

add_action('wp_ajax_simple_product_on_single_page', 'simple_product_on_single_page_function');
add_action('wp_ajax_nopriv_simple_product_on_single_page', 'simple_product_on_single_page_function');
if (!function_exists('simple_product_on_single_page_function')) {
    function simple_product_on_single_page_function()
    {
        $product_id = ($_POST['simple_id']);
        if (!empty($product_id)) {
            parse_str($_POST['my_quentity'], $params);
            $quentity_update = $params['quantity'];
            $product_id = apply_filters('ql_woocommerce_add_to_cart_product_id', absint($product_id));
            $quantity = empty($quentity_update) ? 1 : wc_stock_amount($quentity_update);
            $product_status = get_post_status($product_id);
            if (WC()->cart->add_to_cart($product_id, $quantity) && 'publish' === $product_status) {
                do_action('ql_woocommerce_ajax_added_to_cart', $product_id);
                WC_AJAX:: get_refreshed_fragments();
            } else {
                $data = array(
                    'error' => true,
                    'product_url' => apply_filters('ql_woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id));
                echo wp_send_json($data);
            }

        }
        wp_die();
    }
}

add_action('wp_ajax_ql_woocommerce_ajax_add_to_cart', 'ql_woocommerce_ajax_add_to_cart');
add_action('wp_ajax_nopriv_ql_woocommerce_ajax_add_to_cart', 'ql_woocommerce_ajax_add_to_cart');
if (!function_exists('ql_woocommerce_ajax_add_to_cart')) {
    function ql_woocommerce_ajax_add_to_cart()
    {

        parse_str($_POST['my_val'], $params);
        $add_cart_id = ($params['add-to-cart']);
        $_product = wc_get_product($add_cart_id);
        if ($_product->is_type('grouped')) {
            $product_inner_array = $params['quantity'];
            $product_cart_id = $params['add-to-cart'];
            foreach ($product_inner_array as $key => $value) {
                $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($key));
                $quantity = empty($value) ? 1 : wc_stock_amount($value);
                $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
                $product_status = get_post_status($product_id);
                if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity) && 'publish' === $product_status) {
                    do_action('woocommerce_ajax_added_to_cart', $product_id);
                    if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
                        wc_add_to_cart_message(array($product_id => $quantity), true);
                    }
                } else {
                    $data = array(
                        'error' => true,
                        'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
                    );
                    echo wp_send_json($data);
                }
            }
        } else {
            $product_id = apply_filters('ql_woocommerce_add_to_cart_product_id', absint($params['product_id']));
            $quantity = empty($params['quantity']) ? 1 : wc_stock_amount($params['quantity']);
            $variation_id = absint($params['variation_id']);
            $passed_validation = apply_filters('ql_woocommerce_add_to_cart_validation', true, $product_id, $quantity);
            $product_status = get_post_status($product_id);
            $variation = array();
            foreach ($params as $key => $value) {
                if (substr($key, 0, 10) == 'attribute_') {
                    $variation[$key] = $value;
                }
            }
            if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation, null) && 'publish' === $product_status) {
                do_action('ql_woocommerce_ajax_added_to_cart', $product_id);
                if ('yes' === get_option('ql_woocommerce_cart_redirect_after_add')) {
                    wc_add_to_cart_message(array($product_id => $quantity), true);

                }
                WC_AJAX:: get_refreshed_fragments();
            } else {
                $data = array(
                    'error' => true,
                    'product_url' => apply_filters('ql_woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id));
                echo wp_send_json($data);
            }
        }
        wp_die();
    }
}