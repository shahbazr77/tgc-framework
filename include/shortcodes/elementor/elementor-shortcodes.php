<?php
namespace ElementorTgc;
/**
 * Class Plugin
 *
 * Main Plugin class
 * @since 1.2.0
 */
class Plugin
{
    /**
     * Instance
     *
     * @since 1.2.0
     * @access private
     * @static
     *
     * @var Plugin The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /* call constructor */

    public function __construct()
    {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_categories']);
        /* include custom functions */
        require_once(__DIR__ . '/elementor-functions.php');
        /* include render html file */
        //require_once(__DIR__ . '/shortcodes-html.php');
    }
    //Ad Shortcode Category
    public function add_elementor_widget_categories($category_manager)
    {
        $category_manager->add_category(
            'tgc',
            [
                'title' => __('TGC Custom Widgets', 'tgc-framework'),
                'icon' => 'fa fa-home',
            ]
        );
    }
    public function register_widgets($widgets_manager )
    {
        require_once(__DIR__ . '/elementor-widgets/tgc-giveaway-product.php');
        require_once(__DIR__ . '/elementor-widgets/tgc-all-time-winners.php');
        require_once(__DIR__ . '/elementor-widgets/tgc-upcoming-giveaways.php');
        require_once(__DIR__ . '/elementor-widgets/tgc-all-brands.php');
        require_once(__DIR__ . '/elementor-widgets/tgc-all-entries.php');
        $widgets_manager->register( new \TGC_Giveaway_Product());
        $widgets_manager->register( new \TGC_All_Timer_winners());
        $widgets_manager->register( new \TGC_Upcoming_Giveawys());
        $widgets_manager->register( new \TGC_All_Brands());
        $widgets_manager->register( new \TGC_All_USER_ENTRIES());

    }
}
Plugin::instance();