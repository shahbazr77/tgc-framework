<?php
class TGC_Upcoming_Giveawys extends Elementor\Widget_Base
{

    public function get_name()
    {
       return 'tgc-upcoming-giveawys';
    }
    public function get_title()
    {
        return __('TGC Upcomming Giveawy', 'tgc-framework');
    }
    public function get_icon()
    {
        return 'eicon-post-slider';
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
            'tgc_upcoming_giveaway_section',
            [
                'label' => __('TGC Upcoming Giveaways', 'tgc-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'up-coming-heading',
            [
                'label' => __('Upcoming Giveaways Section Heading', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('See other upcoming giveaways', 'tgc-framework'),
                'placeholder' => __('Upcoming Giveaways Section Heading', 'tgc-framework'),
                'label_block' => true
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'up-coming-giveawy',
            [
                'label' => __('Chose Product Against Rule', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => get_all_upcoming_giveawys(), // Placeholder for dynamic options
                'label_block' => true
            ]
        );
        $repeater->add_control(
            'upcoming-draw-time',
            [
                'label' => __('Upcoming Draw Time', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::DATE_TIME,
                'picker_options' => [
                    'noCalendar' => true, // Disable date selection
                    'dateFormat' => 'H',
                    //'dateFormat' => 'H:i',
                ],
            ]
        );
        $this->add_control(
            'upcoming-giveaways-repeater',
            [
                'label' => __('Up Coming Giveaway Slider', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'up-coming-giveawy' => '',
                        'upcoming-draw-time' => '',
                    ],
                ],
            ]
        );

        $this->end_controls_section();

    }
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $params['up-coming-heading'] = $settings['up-coming-heading'] ? $settings['up-coming-heading'] : '';
        $params['upcoming-giveaways-repeater'] = $settings['upcoming-giveaways-repeater'] ? $settings['upcoming-giveaways-repeater'] : array();
       echo  $this->get_all_winner_repeating_render($params);
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            ?>
            <script>

                jQuery('.upcoming-giveaway').owlCarousel({
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
        $upcoming_giveaway_heading = $params['up-coming-heading'];
        $upcoming_giveaway_repeater = $params['upcoming-giveaways-repeater'];
        $upcoming_html = '';
        if ($upcoming_giveaway_repeater) {
            foreach ($upcoming_giveaway_repeater as $item) {
                $upcoming_combine_data = $item['up-coming-giveawy'];
                $upcoming_draw_time = $item['upcoming-draw-time'];
                if (!empty($upcoming_combine_data)) {
                    $isPM = $upcoming_draw_time >= 12;
                    $timeSuffix = $isPM ? 'PM' : 'AM';
                    $formattedGiveawayTime = $upcoming_draw_time . ' ' . $timeSuffix;
                    $giveawy_combine = explode("_", $upcoming_combine_data);
                    $giveaway_rule_id = $giveawy_combine[0];
                    $giveaway_product_id = $giveawy_combine[1];
                    $thumbnail_id = get_post_thumbnail_id($giveaway_product_id);
                    $gift_date_from = get_post_meta($giveaway_rule_id, 'fgf_rule_valid_from_date', true);
                    $gift_data_to_set = get_post_meta($giveaway_rule_id, 'fgf_rule_valid_to_date', true);
                    $date_updater = new DateTime($gift_data_to_set);
                    $gift_convert_date = $date_updater->format('l, F j');
                    $categories = get_the_terms ( $giveaway_product_id, 'product_cat' );
                    $post_title = get_the_title($giveaway_product_id);
                    $post = get_post($giveaway_product_id);
                    if ($post) {
                        $description = $post->post_content;
                    } else {
                        $description= 'Description not found.';
                    }

                    foreach ( $categories as $term ) {
                        $cat_name = $term->name;
                        // print_r($term);
                    }
                    $product = wc_get_product($giveaway_product_id);

                    if ($product) {
                        $price = $product->get_price();
                    } else {
                        echo 'Product not found.';
                    }
                    if ($thumbnail_id) {
                        $image_url = wp_get_attachment_image_src($thumbnail_id, 'full');
                        $image_url = $image_url[0];
                    } else {
                        $image_url="";
                    }

                    $upcoming_html.='<div class="item">
                <div class="main-upcoming-box">
                  <div class="row">
                   <div class="col-lg-6">
                    <div class="image-box">
                        <a href="#">
                            <img src="' . $image_url . '" alt="' . esc_attr__('icon', 'tgc-framework') . '" class="img-fluid">
                        </a>
                    </div>
                   </div> 
                   <div class="col-lg-6">
                    <div class="upcoming-data-inner">
                     <div class="category">
                        <span>' . esc_html__('Prize:') . '</span>
                        <div class="category-inner">' . esc_html($cat_name) . '</div> 
                     </div>                 
                     <div class="upcoming-title">' . esc_html($post_title) . '</div>
                     <div class="upcoming-value">
                        <span class="valued">' . esc_html__('Valued at') . '</span>
                        <span class="price">' . esc_html($price) . '</span>
                     </div>
                     <div class="giveaway-last-date">
                      <span class="draw-date">'.esc_html__('Draw Date:').'</span>
                       <div class="draw-data">'.$gift_convert_date.' <span>('.$formattedGiveawayTime.')</span></div>
                  </div>
                  </div>
                   </div>
                  </div>
                </div>
              </div>';
                }
            }





            return '<section class="upcoming-giveaway-section">
          <div class="container-fluid">
             <div class="container-giveawy">
              <div class="col-xxl-12 col-xl-12 col-lg-12">
                 <div class="upcomain-main-heading">
                   <h3 class="head-title">' . $upcoming_giveaway_heading . '</h3>
                  </div>
              </div>
              <div class="upcoming-giveaway owl-carousel owl-theme">
                       ' . $upcoming_html . '
                </div>
             </div>   
          </div>
         </section>';

        }






    }


}
