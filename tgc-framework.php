<?php
/*
 * Plugin Name:       TGC Framework
 * Description:       This plugin is used to add functionality to That Girl Club.
 * Version:           1.0.2
 * Author:            Yodo Developers
 * Text Domain:       tgc-framework
 */
//Exit if accessed directly
if (!defined('ABSPATH')) {
    return;
}
define('TGC_PLUGIN_FRAMEWORK_PATH',plugin_dir_path(__FILE__));
define('TGC_PLUGIN_URL', plugin_dir_url(__FILE__));
define( 'TGC_THEMEURL_PLUGIN', get_template_directory_uri () . '/' );

class Tgc_Mini_Cart_Sidebar{
    protected static $instance=null;
    public static function get_instance(){
        if(self::$instance===null){
            self::$instance=new self();
        }
        return self::$instance;
    }
    public function __construct(){

        if (in_array('woocommerce-memberships/woocommerce-memberships.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_filter('woocommerce_get_price_html', array($this,'add_sticker_under_price'), 10, 2);
            add_action('woocommerce_order_status_completed', array($this,'check_users_packages_product_entries'), 10, 1);
            add_filter('body_class',array($this,'add_tgc_cart_empty_class'));
            add_action('woocommerce_edit_account_form_start',array($this,'tgc_add_custom_field_to_edit_account'));
            add_action('wp_login', array($this,'tgc_new_custom_redirect_after_login'),10, 2);
            add_filter( 'woocommerce_checkout_fields',array($this,'tgc_custom_checkout_fields'));
        }
        add_action('admin_enqueue_scripts', array($this,'tgc_framework_scripts'));
        add_action('wp_enqueue_scripts', array($this,'tgc_frontend_scripts'));
        add_action('elementor/frontend/after_register_scripts', array($this,'enqueue_frontend_elementor_scripts'),999);
        add_action('plugins_loaded', array($this,'tgc_framework_load_plugin_textdomain'));
        add_action('wp_footer',array($this,'add_html_infooter_mini_cart'));
        require_once( TGC_PLUGIN_FRAMEWORK_PATH. 'include/shortcodes/elementor/elementor-shortcodes.php' );
        include (TGC_PLUGIN_FRAMEWORK_PATH. 'include/ajax/action.php');
        include (TGC_PLUGIN_FRAMEWORK_PATH. 'admin/actions/admin_actions.php');
        add_action( 'set_user_role', array($this,'tgc_send_email_tocustomer'), 10, 3);
        add_filter('woocommerce_account_menu_items', array($this,'tgc_custom_account_menu_items'));
        add_filter('woocommerce_add_to_cart_redirect', array($this,'custom_redirect_to_checkout'));
        add_action('admin_menu', array($this,'tgc_entries_option_page'));
        register_activation_hook(__FILE__,array($this,'tgc_all_time_winners'));
    }

    function tgc_custom_checkout_fields( $fields ) {
        $fields['shipping']['shipping_country']['default'] = 'AU';
        $fields['billing']['billing_country']['default'] = 'AU';
        // Remove other countries from the dropdown
        $fields['shipping']['shipping_country']['type'] = 'select';
        $fields['billing']['billing_country']['type'] = 'select';
        $fields['shipping']['shipping_country']['options'] = array('AU' => 'Australia');
        $fields['billing']['billing_country']['options'] = array('AU' => 'Australia');

        return $fields;
    }
    function tgc_new_custom_redirect_after_login($redirect, $user) {
        if (class_exists('WooCommerce')) {
            if (is_a($user, 'WP_User') && in_array('customer', $user->roles)) {
                wp_redirect(home_url('/my-account/edit-account/'));
                exit();
            }
        }
    }
    function tgc_add_custom_field_to_edit_account() {
        $custom_path = '/giveaways/#entries_section_id';
        $buy_entries = home_url($custom_path);
        $user_id = get_current_user_id();
        $user_entries = get_user_meta($user_id, 'user_entries', true);
        echo '<div class="form-entries">';
        echo '<fieldset>';
        echo '<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">';
        echo '<a target="_blank" href="'.wp_logout_url( home_url() ).'"  class="button tgc-logout">'. __('Logout', 'woocommerce') .'</a>';
        echo '</p>';
        echo '</fieldset>';
        echo '<fieldset>';
        echo '<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">';
        echo '<label for="user_entry">' . __('Number of Entries', 'woocommerce') . '</label>';
        echo '<input type="text" class="input-text" name="user_entry" id="user_entry" value="' . esc_attr($user_entries) . '" readonly="false" />';
        echo '</p>';
        echo '<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">';
        echo '<a target="_blank" href="'.$buy_entries.'"  class="button buy-entries-buton" name="save_account_details" >'. __('Gain More Entries', 'woocommerce') .'</a>';
        echo '</p>';
        echo '</fieldset>';
        echo '</div>';
    }
    function add_tgc_cart_empty_class($classes) {
        if (WC()->cart->is_empty()) {
            $classes[] = 'tgc-empty-cart-custom';
        }
        return $classes;
    }
    public function enqueue_frontend_elementor_scripts() {
        //wp_enqueue_script('tgc-giveaway-product-script', TGC_PLUGIN_URL . 'assets/js/elementor_custom.js',['jquery', 'elementor-frontend'], false, true);
        wp_enqueue_script('tgc-giveaway-product-script', TGC_PLUGIN_URL . 'assets/js/elementor_custom.js',['jquery'], false, true);
        wp_localize_script('tgc-giveaway-product-script', 'tgcGiveawayProductScript', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'current_time' => current_time('timestamp'),
        ]);
    }
    function check_users_packages_product_entries($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        $user_old_entries = get_user_meta($user_id, 'user_entries', true);
        $user_old_entries_updated = !empty($user_old_entries) ? intval($user_old_entries) : 0;
        foreach ($order->get_items() as $item_id => $item) {
            $product_id = $item->get_product_id();
            $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
            $product_categories_id=$product_categories[0];
            $category = get_term($product_categories_id, 'product_cat');
            $category_slug = $category->slug;
            if ($category_slug === 'entry-category') {
                    $variation_id = $item->get_variation_id();
                    $variation_data = wc_get_product_variation_attributes($variation_id);
                    $attribute_entries = isset($variation_data['attribute_entries']) ? $variation_data['attribute_entries'] : '';
                    $user_updated_entries=intval($user_old_entries_updated)+intval($attribute_entries);
                    update_user_meta($user_id, "user_entries", $user_updated_entries);
             }
      }
    }
    function tgc_entries_option_page(){
        add_menu_page(
            'Tgc_entries',
            'TGC_Entries_Data',
            'manage_options',
            'tgc_entries',
            array($this, 'tgc_users_entries_callback'),
            'dashicons-list-view',
            50
        );
    }
    function tgc_users_entries_callback() {
        $loader_svg = TGC_PLUGIN_URL . 'admin/assets/images/bars-white.svg';
        ?>
        <div class="tgc-wraper-main">
        <div class="response-html"><div class="respons-heading"></div></div>
        <div class="tgcframe_loader"><div class="tgcframe__inner"><div class="tgcframe__content"><div class="tgcframeloader"><img class="tgcframe-loader" src="<?php echo esc_url($loader_svg) ?>" /></div></div></div></div>
        <h1 class="wp-heading-inline tab-heading-main">That Girl Club Lucky Draw</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=tgc_entries&tab=participant" class="tab-first nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'participant') ? 'nav-tab-active' : ''; ?>">Lucky Draw Participant</a>
            <a href="?page=tgc_entries&tab=winners" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'winners') ? 'nav-tab-active' : ''; ?>">Lucky Draw Winners</a>
            <a href="?page=tgc_entries&tab=alltimewinner" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'alltimewinner') ? 'nav-tab-active' : ''; ?>">All Time Winners</a>
        </h2>
        <?php
        // Display the content based on the selected tab
        if (isset($_GET['tab'])) {
            $current_tab = sanitize_text_field($_GET['tab']);
            switch ($current_tab) {
                case 'participant':
                    include (TGC_PLUGIN_FRAMEWORK_PATH. 'admin/actions/all-participant.php');
                    break;
                case 'winners':
                    include (TGC_PLUGIN_FRAMEWORK_PATH. 'admin/actions/all-winners.php');
                    break;
                case 'alltimewinner':
                    include (TGC_PLUGIN_FRAMEWORK_PATH. 'admin/actions/all-timewinners.php');
                    break;
                    default:
                    include (TGC_PLUGIN_FRAMEWORK_PATH. 'admin/actions/all-participant.php');
                    break;
            }
        } else {
            include (TGC_PLUGIN_FRAMEWORK_PATH. 'admin/actions/all-participant.php');
        }
        ?>
        </div>
        <?php
    }
    function add_sticker_under_price($price, $product) {
        if (!is_admin()) {
            $current_user_id = get_current_user_id();
            $users_data=wc_memberships_get_user_memberships($current_user_id);
            $currency_symbol = get_woocommerce_currency_symbol();
            $member_url = 'https://thatgirlclub.com.au/membership/';
            $sticker_html="";
            if (is_product()) {
                if (!empty($users_data)) {
                    $product_id = $product->get_id(); // Updated for WC 3.0+
                    $regular_price = floatval($product->get_regular_price());
                    $sale_price = $product->get_sale_price();
                    if ($product && $product->is_type('simple')){
                        $flat_saving=floatval($regular_price)-floatval($sale_price);
                        $sticker_html = '<p class="main-sticker member"><span class="currency-wrap">'.$currency_symbol.$regular_price.'</span>Non-Members Price-<span class="saving-wrip">You are Saving '.$currency_symbol.$flat_saving.'</span></p>';
                    }elseif ($product && $product->is_type('variable')) {
                        $variations = $product->get_available_variations();
                        $variation = reset($variations);
                        $variation_sale_price = $variation['display_regular_price'];
                        if ($variation_sale_price !== $variation['display_price']) {
                            $flat_saving=floatval($variation_sale_price)-floatval($variation['display_price']);
                            $sticker_html = '<p class="main-sticker member"><span class="currency-wrap">'.$currency_symbol.$variation_sale_price.'</span>Non-Members Price-<span class="saving-wrip">You are Saving '.$currency_symbol.$flat_saving.'</span></p>';
                        } else {
                        }
                    }
                } else {
                    if ($product && $product->is_type('simple')) {
                        $regular_price = $product->get_regular_price();
                        $discount_value = floatval($regular_price) - (20 / 100) * floatval($regular_price);
                        $sticker_html = '<p class="main-sticker non-member"><span class="currency-wrap">'.$currency_symbol.$discount_value .'</span>for Members Price <span class="saving-wrip">(Save 20%)</span>--<a href="'.esc_url($member_url).'" target="_self">Become a Member!</a></p>';
                    }elseif ($product && $product->is_type('variable')) {
                        $variations = $product->get_available_variations();
                        $variation = reset($variations);
                        $variation_price = $variation['display_price'];
                        $discount_value = floatval($variation_price)-(20 / 100) * floatval($variation_price);
                        $sticker_html = '<p class="main-sticker non-member"><span class="currency-wrap">'.$currency_symbol.$discount_value.'</span>for Members Price <span class="saving-wrip">(Save 20%)</span>--<a href="'.esc_url($member_url).'" target="_self">Become a Member!</a></p>';
                    }
                }
            }else{

                if (!empty($users_data)) {
                    $product_id = $product->get_id(); // Updated for WC 3.0+
                    $regular_price = floatval($product->get_regular_price());
                    $sale_price = $product->get_sale_price();
                    if ($product && $product->is_type('simple')){
                        $flat_saving=floatval($regular_price)-floatval($sale_price);
                        $sticker_html = '<p class="main-sticker member sticker-transparent"><span class="currency-wrap">'.$currency_symbol.$regular_price.'</span>Non-Members Price</p>';
                    }elseif ($product && $product->is_type('variable')) {
                        $variations = $product->get_available_variations();
                        $variation = reset($variations);
                        $variation_sale_price = $variation['display_regular_price'];
                        if ($variation_sale_price !== $variation['display_price']) {
                            $flat_saving=floatval($variation_sale_price)-floatval($variation['display_price']);
                            $sticker_html = '<p class="main-sticker member sticker-transparent"><span class="currency-wrap">'.$currency_symbol.$variation_sale_price.'</span>Non-Members Price</p>';
                        } else {
                        }
                    }
                } else {
                    if ($product && $product->is_type('simple')) {
                        $regular_price = $product->get_regular_price();
                        $discount_value = floatval($regular_price) - (20 / 100) * floatval($regular_price);
                        $sticker_html = '<p class="main-sticker non-member sticker-transparent"><span class="currency-wrap">'.$currency_symbol.$discount_value .'</span>for Members Price</p>';
                    }elseif ($product && $product->is_type('variable')) {
                        $variations = $product->get_available_variations();
                        $variation = reset($variations);
                        $variation_price = $variation['display_price'];
                        $discount_value = floatval($variation_price)-(20 / 100) * floatval($variation_price);
                        $sticker_html = '<p class="main-sticker non-member sticker-transparent"><span class="currency-wrap">'.$currency_symbol.$discount_value.'</span>for Members Price</p>';
                    }
                }
            }
        }
        if(isset($sticker_html)){
            return $price .=$sticker_html;
        }else{
            return $price;
        }
    }
    public function get_active_members_for_membership($memberships_userid){
        global $wpdb;
        $results= $wpdb->get_results( "
        SELECT DISTINCT *
        FROM {$wpdb->prefix}posts AS p
        LEFT JOIN {$wpdb->prefix}posts AS p2 ON p2.ID = p.post_parent
        LEFT JOIN {$wpdb->prefix}users AS u ON u.id = p.post_author
        LEFT JOIN {$wpdb->prefix}usermeta AS um ON u.id = um.user_id
        WHERE p.post_type = 'wc_user_membership'
        AND p.post_status IN ('wcm-active')
        AND p2.post_type = 'wc_membership_plan'
        AND um.user_id LIKE '$memberships_userid'
    ");
        return $results;
    }
//    function custom_redirect_to_checkout($url) {
//        if (isset($_REQUEST['add-to-cart'])) {
//            if ($_REQUEST['add-to-cart'] == 4573 || $_REQUEST['add-to-cart'] == 4531 || $_REQUEST['add-to-cart'] == 4575 || $_REQUEST['add-to-cart'] == 4537 || $_REQUEST['add-to-cart'] == 4574 || $_REQUEST['add-to-cart'] == 4538 || $_REQUEST['add-to-cart'] == 5927 || $_REQUEST['add-to-cart'] == 5928 || $_REQUEST['add-to-cart'] == 5929 || $_REQUEST['add-to-cart'] == 5930 || $_REQUEST['add-to-cart'] == 5931) {
//                $checkout_url = wc_get_checkout_url();
//                wp_safe_redirect($checkout_url);
//                exit;
//            }
//        }
//        return $url;
//
//    }

    function custom_redirect_to_checkout($url) {
        if (isset($_REQUEST['add-to-cart'])) {
            $product_id = absint($_REQUEST['add-to-cart']);
            $product = wc_get_product($product_id);
            if ($product && $product->is_type('subscription') || $product->is_type('variable-subscription') || $product->is_type('variation') ) {
                $checkout_url = wc_get_checkout_url();
                //return $checkout_url;
                wp_safe_redirect($checkout_url);
               exit;
            }
        }
        return $url;
    }

    function tgc_custom_account_menu_items($items) {
        $new_order = array(
            'edit-account'    => isset($items['edit-account']) ? $items['edit-account'] : '',
            'members-area'    => isset($items['members-area']) ? $items['members-area'] : '',
            'orders'          => isset($items['orders']) ? $items['orders'] : '',
            'customer-logout' => isset($items['customer-logout']) ? $items['customer-logout'] : '',
        );
        return $new_order;
    }
    function tgc_send_email_tocustomer( $user_id, $new_role, $old_roles ) {
        $user_roles = get_userdata($user_id)->roles;
        if ( in_array('dropshipper', $user_roles)) {
            $user_info = get_userdata($user_id);
            $to = $user_info->user_email;
            $user_name = $user_info->display_name;
            $subject = 'Your Role Has Been Changed';
            $message = '<html>';
            $message .= '<head><title>' . $subject . '</title></head>';
            $message .= '<body>';
            $message .= '<p>Hi ' . $user_name . ',</p>';
            $message .= '<p>Congratulations! Your role has been changed to "Dropshipper".</p>';
            $message .= '<p>Now You Can Login as Dropshiper.</p>';
            $message .= '<p>Thank you for using our service.</p>';
            $message .= '</body>';
            $message .= '</html>';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($to, $subject, $message, $headers);
        }
    }
    function tgc_framework_scripts(){
        wp_enqueue_style('tgc_admin_style', TGC_PLUGIN_URL . 'admin/assets/css/tgc_admin_style.css');
        /*Enqueue scripts in wp admin*/
        wp_enqueue_script('jquery_admin', TGC_PLUGIN_URL . 'admin/assets/js/jquery_admin.min.js', false, false, true);
        wp_enqueue_script( 'tgc_admin_custom',  TGC_PLUGIN_URL . 'admin/assets/js/admin_custom.js', false, false, true );
        wp_localize_script('tgc_admin_custom', 'tgc_admin_strings', array(
            'ajax_admin_url' => admin_url('admin-ajax.php'),
            'nonce_admin' => wp_create_nonce('ajax-nonce'),
            'site_url' => get_site_url(),
            'admin_url' => admin_url(),
        ));
    }
    function tgc_frontend_scripts(){
        wp_enqueue_style('tgc_bootstrap', TGC_PLUGIN_URL . 'assets/css/bootstrap.min.css');
        wp_enqueue_style('bootstrap-icons', TGC_PLUGIN_URL . 'assets/css/bootstrap-icons.css');
        wp_enqueue_style('owl-carousel', TGC_PLUGIN_URL . 'assets/css/owl.carousel.min.css');
        wp_enqueue_style("jquery-custom-scroll", TGC_PLUGIN_URL . "assets/css/jquery-custom-scroll-min.css");
        wp_enqueue_style('sidebar_mini_cart', TGC_PLUGIN_URL . 'assets/css/sidebar_mini_cart.css', array(), '1.0.7');
        wp_enqueue_style('tgc_elementor_style', TGC_PLUGIN_URL . 'assets/css/elementor-widget-style.css');
        //wp_enqueue_script('jquery');
        wp_enqueue_script('jquery', TGC_PLUGIN_URL . 'assets/js/jquery.min.js', false, false, true);
        wp_enqueue_script( 'tgc_bootstrap',  TGC_PLUGIN_URL . 'assets/js/bootstrap.bundle.min.js', false, false, true );
        wp_enqueue_script( 'owl-careusel',  TGC_PLUGIN_URL . 'assets/js/owl.carousel.min.js', false, false, true );
        wp_enqueue_script('smooth-scrollbar', TGC_PLUGIN_URL . 'assets/js/smooth-scrollbar.js', false, false, true);
        wp_enqueue_script("jquery-custom-scroll", TGC_PLUGIN_URL. "assets/js/jquery-custom-scroll.min.js", false, false, true);
        wp_enqueue_script("notiflix", TGC_PLUGIN_URL. "assets/js/notiflix.js", false, false, true);
        wp_enqueue_script("add2-cart", TGC_PLUGIN_URL . "assets/js/jquery-add2cart.js", false, false, true);
        wp_enqueue_script("loading-overlay",TGC_PLUGIN_URL. "assets/js/loadingoverlay.js", false, false, true);
        wp_enqueue_script( 'wc-cart-fragments' );
        wp_enqueue_script( 'tgc_custom',  TGC_PLUGIN_URL . 'assets/js/custom.js', false, false, true );
        wp_localize_script('tgc_custom', 'tgc_strings', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ajax-nonce'),
            'checkout_url'=> wc_get_checkout_url(),
        ));
    }
    function tgc_framework_load_plugin_textdomain(){
        load_plugin_textdomain('tgc-framework', FALSE, basename(dirname(__FILE__)) . '/languages/');
    }
    function add_html_infooter_mini_cart(){
        $loader_images = trailingslashit(get_template_directory_uri()) . 'libs/images/options/loading.gif';
        $clear_basket = TGC_PLUGIN_URL.'assets/css/images/supermarket.png';
       $image_alt_id = $thumb_url = '';
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    ?>
    <div class="cf-off-canvas">
        <div id="mySidenav" class="sidenav">
            <div class="res-cart-image"></div>
            <div class="cf-canvas-content">
                <div class="heading-panel">
                    <h3><?php echo esc_html__('Your Bag', 'tgc-framework'); ?> <span><a href="javascript:void(0)" class="closebtn" id="closeNav">&times;</a></span></h3>
                    <div class="bottom-dots  clearfix">
                        <span class="dot line-dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                </div>
                <div class="cf-canvas-checboxes mCustomScrollbar" data-mcs-theme="dark">
                    <div class="cf-order-details">
                        <div class="counter cart-count">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="res-order-box">
        <div class="container-fluid">
            <div class="row">
            </div>
        </div>
    </section>
    <?php
}
}
    function tgc_all_time_winners() {
        global $wpdb;
        $table_name = $wpdb->prefix.'tgc_giveaway_winners_table';
        $sql_plugin_status = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            user_id INT(11),
            user_name VARCHAR(255),
            user_email VARCHAR(255),
            user_role VARCHAR(255),
            user_membership VARCHAR(255),
            gift_id INT(11),
            created_at DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        )";
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta( $sql_plugin_status );
    }
}
Tgc_Mini_Cart_Sidebar::get_instance();