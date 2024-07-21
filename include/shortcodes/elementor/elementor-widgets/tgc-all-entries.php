<?php
class TGC_All_USER_ENTRIES extends Elementor\Widget_Base
{
    public function get_name()
    {
        return 'tgc-all-user-entires';
    }
    public function get_title()
    {
        return __('TGC All User Entries', 'tgc-framework');
    }
    public function get_icon()
    {
        return 'eicon-product-rating';
    }
    public function get_categories()
    {
        return ['tgc'];
    }
    public function get_script_depends()
    {
        return [''];
    }
    protected function _register_controls()
    {
        $this->start_controls_section(
            'tgc_giveaway_entries_section',
            [
                'label' => __('TGC Giveaways Entries', 'tgc-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'all-entries-heading',
            [
                'label' => __('Give Main Heading', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Add more entries', 'tgc-framework'),
                'placeholder' => __('Give Main Heading', 'tgc-framework'),
            ]
        );
        $this->add_control(
            'all-entries-description',
            [
                'label' => __('Give Main Description', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'rows' => 3,
                'default' => __('Some text about gaining more entries. Lorem ipsum donec donec sed semper neque enim lectus posuere vehicula. Faucibus faucibus pharetra sed congue amet bibendum.', 'tgc-framework'),
                'placeholder' => __('Give Main Description', 'tgc-framework'),
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'select-entries-product-variation',
            [
                'label' => __('Choose Entries For Giveaway', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => get_all_enries_product(), // Placeholder for dynamic options
            ]
        );

        $repeater->add_control(
            'entry_popular',
            [
                'label' => esc_html__( 'Most Popular!', 'tgc-framework' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'True', 'tgc-framework' ),
                'label_off' => esc_html__( 'False', 'tgc-framework' ),
                'return_value' => 'yes',
                'default' => 'false',
            ]
        );

        $repeater->add_control(
            'select-entries-variation-description',
            [
                'label' => __('Choose Entries Heading Description', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'rows' => 3,
                'default' => __('+ 7 days of Bronze access', 'tgc-framework'),

            ]
        );
        $repeater->add_control(
            'select-entries-li-description',
            [
                'label' => esc_html__( 'Description', 'tgc-framework' ),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => esc_html__( 'Default description', 'tgc-framework' ),
                'placeholder' => esc_html__( 'Type your description here', 'tgc-framework'),
            ]
        );

        $this->add_control(
            'all-entries-repeater',
            [
                'label' => __('All Entries Repeater', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'select-entries-product-variation' => '',
                        'select-entries-variation-description' => '',
                        'select-entries-li-description' => '',
                    ],
                ],
            ]
        );

        $this->end_controls_section();

    }
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $params['all-entries-heading'] = $settings['all-entries-heading'] ? $settings['all-entries-heading'] : '';
        $params['all-entries-description'] = $settings['all-entries-description'] ? $settings['all-entries-description'] : '';
        $params['all-entries-repeater'] = $settings['all-entries-repeater'] ? $settings['all-entries-repeater'] : array();
        echo  $this->get_all_entries_repeating_render($params);
    }

    public function get_all_entries_repeating_render($params)
    {
        $all_entries_heading = $params['all-entries-heading'];
        $all_entries_description = $params['all-entries-description'];
        $entries_repeater = $params['all-entries-repeater'];
        $entries_html = '';
        $pauplar_html='';
        if ($entries_repeater) {
            foreach ($entries_repeater as $item) {
                $entry_variation_id = $item['select-entries-product-variation'];

                echo $entry_variation_id;
                echo "<br>";


                $entry_small_description = $item['select-entries-variation-description'];
                $entry_ul_description = $item['select-entries-li-description'];
                $entry_most_popular=$item['entry_popular'];
               // echo "this is teh most Papular value==".$entry_most_popular;
                if($entry_most_popular=='yes'){
                    $pauplar_html='<div class="main-popular"><span class="most-popular">Most Popular!</span></div>';
                }else{
                    $pauplar_html="";
                }
                if ($entry_variation_id != '') {
                    $entries_id_refine = explode("_", $entry_variation_id);
                    $entries_id = $entries_id_refine[0];
                    $entries_quantity = isset($entries_id_refine[1]) ? $entries_id_refine[1]:'' ;
                    $cart_url=get_site_url().'/cart/?add-to-cart='.$entries_id.'&quantity=1';
                    $product = wc_get_product($entries_id);
                    if ($product !== false) {
                        $entry_price=$product->get_price();
                    } else {
                        $entry_price = 0;  // or any other default value you consider appropriate
                    }
                    $entry_price_update=get_woocommerce_currency_symbol().$entry_price;
                    $entries_html.='<div class="bg">
            <div class="main_box">
                '.$pauplar_html.'
                <div class="top_section">
                  <h2>'.$entries_quantity.' Entries</h2>
                  <p>'.$entry_small_description.'</p>
                </div>
                <div class="line"></div>       
                <div class="list">
                '.$entry_ul_description.'
                </div>
                <div class="btn">
                  <a href="'.$cart_url.'" class="buttons">
                    BUY NOW -'.$entry_price_update.'.00'.'
                    </a>
                </div>
              </div>
        </div>';
                }
            }
            return '<section class="main_section entries-section">
          <div class="container-entries">
                 <div class="entries-main-heading">
                   <h3 class="head-title">'.$all_entries_heading.'</h3>
                    <p class="head-description">'.$all_entries_description.'</p>
                  </div>

            <div class="row-enries-repeater">      
               ' . $entries_html . '
            </div>
                     
            </div>
          </div>
         </section>';

        }

    }

}
