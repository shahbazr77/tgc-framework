<?php
class TGC_All_Timer_winners extends Elementor\Widget_Base
{

    public function get_name()
    {
       return 'tgc-all-time-winners';
    }
    public function get_title()
    {
        return __('TGC All Time Winners', 'tgc-framework');
    }
    public function get_icon()
    {
        return 'eicon-slider-album';
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
            'tgc_giveaway_alltime_winner_section',
            [
                'label' => __('TGC All Time Winners', 'tgc-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'all-winner-heading',
            [
                'label' => __('Past Winners Heading', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Past Winners', 'tgc-framework'),
                'placeholder' => __('Give Past Winners Section Heading', 'tgc-framework'),
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'giveawy-all-winner',
            [
                'label' => __('Choose Winner For Slider', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => get_all_winners(), // Placeholder for dynamic options
            ]
        );

        $this->add_control(
                'all-winner-repeater',
                [
                    'label' => __('All Winner Slider', 'tgc-framework'),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $repeater->get_controls(),
                    'default' => [
                        [
                            'giveawy-all-winner' => '',
                        ],
                    ],
                ]
        );

        $this->end_controls_section();

    }
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $params['all-winner-heading'] = $settings['all-winner-heading'] ? $settings['all-winner-heading'] : '';
        $params['all-winner-repeater'] = $settings['all-winner-repeater'] ? $settings['all-winner-repeater'] : array();

       echo  $this->get_all_winner_repeating_render($params);
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            ?>
            <script>

                jQuery('.all_time_winner').owlCarousel({
                    loop: true,
                    margin: 20,
                    autoplay: true,
                    nav: true,
                    responsive: {
                        0: {
                            items: 1
                        },
                        600: {
                            items: 1
                        },
                        1000: {
                            items: 2
                        }
                    }
                });

            </script>
            <?php

        }


    }

    public function get_all_winner_repeating_render($params)
    {
        $all_winner_heading = $params['all-winner-heading'];
        $winners_team_repeater = $params['all-winner-repeater'];
        $winner_html = '';
        if ($winners_team_repeater) {
            foreach ($winners_team_repeater as $item) {
                $winner_data_id = $item['giveawy-all-winner'];
                $giveawy_combine = explode("_", $winner_data_id);
                $winner_id = $giveawy_combine[0];
                $giveaway_product_id = isset($giveawy_combine[1]) ? $giveawy_combine[1]:'' ;
                if ($winner_id != '') {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'tgc_giveaway_winners_table';
                   // $sql_tgc = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $winner_id);
                    $sql_tgc = $wpdb->prepare("SELECT * FROM $table_name WHERE gift_id = %d", $giveaway_product_id);
                    $winner_results = $wpdb->get_row($sql_tgc);
                    $user_name = $winner_results->user_name;
                    $user_email = $winner_results->user_email;
                    $user_role = $winner_results->user_role;
                    $user_draw_date = $winner_results->created_at;
                    $tgc_dateTime = new DateTime($user_draw_date);
                    $tgc_converted_date = $tgc_dateTime->format('l, F j');
                    $user_membership = $winner_results->user_membership;
                    $giveaway_product_id = $giveaway_product_id;
                    $post_title = get_the_title($giveaway_product_id);
                    $giveawy_title = get_the_title($giveaway_product_id);
                    $thumbnail_id = get_post_thumbnail_id($giveaway_product_id);
                    if ($thumbnail_id) {
                        $image_url = wp_get_attachment_image_src($thumbnail_id, 'full');
                        $image_url = $image_url[0];
                    } else {
                        $image_url = "";
                    }
                    $categories = get_the_terms($giveaway_product_id, 'product_cat');
                    foreach ($categories as $term) {
                        $cat_name = $term->name;
                    }
                    $product = wc_get_product($giveaway_product_id);
                    if ($product) {
                        $price = $product->get_price();
                    } else {
                        $price = 'Product not found.';
                    }


                    $winner_html.='<div class="item">
                <div class="main-winner-box">
                   <div class="row">
                     <div class="image-box">
                        <a href="#">
                            <img src="' . $image_url . '" alt="' . esc_attr__('icon', 'tgc-framework') . '" class="img-fluid">
                        </a>
                    </div>              
                    <div class="giveawy-data-inner">
                     <div class="category">
                        <span>' . esc_html__('Prize:') . '</span>
                        <div class="category-inner">' . esc_html($cat_name) . '</div> 
                     </div>                 
                     <div class="giveaway-title">' . esc_html($post_title) . '</div>
                     <div class="giveaway-value">
                        <span class="valued">' . esc_html__('Valued at') . '</span>
                        <span class="price">' . esc_html($price) . '</span>
                     </div>
                     <div class="winner-div">
                      <span>' . esc_html__('Winner:') . '</span>
                      <div class="winner-name"><div class="winner-name-inner">' . esc_html($user_name) . '</div><span class="user-package">(' . esc_html($user_membership) . ')</span></div>
                     </div>
                     <div class="drawn-div">
                      <span>' . esc_html__('Drawn:') . '</span>
                      <div class="drawn-date">' . esc_html($tgc_converted_date) . '</div>
                     </div>
                  </div>
                  </div>
                </div>
              </div>';
                }
            }





            return '<section class="our-winners">
          <div class="container">
            <div class="row">
              <div class="col-xxl-12 col-xl-12 col-lg-12 p-0">
                 <div class="winner-main-heading">
                   <h3 class="head-title">' . $all_winner_heading . '</h3>
                  </div>
              </div>
                  <div class="all_time_winner owl-carousel owl-theme">
                       ' . $winner_html . '
                </div>
                     
            </div>
          </div>
         </section>';

        }






    }


}
