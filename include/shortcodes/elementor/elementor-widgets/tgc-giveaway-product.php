<?php
class TGC_Giveaway_Product extends Elementor\Widget_Base {

    public function get_name()
    {
        return 'tgc-giveaway-product';
    }

    public function get_title()
    {
        return __('TGC Giveaway Product', 'tgc-framework');
    }

    public function get_icon()
    {
        return 'eicon-single-product';
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
            'tgc_giveaway_product_section',
            [
                'label' => __('TGC Giveaway Product', 'tgc-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );


        $this->add_control(
            'luckydraw_time',
            [
                'label' => __('Lucky Draw Time', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::DATE_TIME,
                'picker_options' => [
                    'noCalendar' => true, // Disable date selection
                    'dateFormat' => 'H',
                    //'dateFormat' => 'H:i',
                ],
            ]
        );

        $this->add_control(
            'giveaway_product',
            [
                'label' => __('Choose Giveaway Product', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => get_product_against_rule(), // Placeholder for dynamic options
            ]
        );


        $this->add_control(
            'giveaway-btn-title',
            [
                'label' => __('Button Title', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('Button title here', 'tgc-framework'),
                'default' => __('Gain More Entries', 'tgc-framework'),
                'label_block' => true
            ]
        );
        $this->add_control(
            'giveaway-btn-link',
            [
                'label' => __('Gain More Entries Link', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'tgc-framework'),
                'show_external' => true,
                'default' => [
                    'url' => '#',
                    'is_external' => true,
                    'nofollow' => true,
                ],
            ]
        );
        
        $this->add_control(
            'giveaway_terms',
            [
                'label' => esc_html__( 'Giveaway Terms Description', 'tgc-framework' ),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => esc_html__( 'Terms and Condition description', 'tgc-framework' ),
                'placeholder' => esc_html__( 'Type your description here', 'tgc-framework' ),
            ]
        );




        $this->end_controls_section();

    }






    protected function render()
    {

       $settings = $this->get_settings_for_display();
        $params['luckydraw_time'] = $settings['luckydraw_time'] ? $settings['luckydraw_time'] : '';
      // $params['giveaway_rules'] = $settings['giveaway_rules'] ? $settings['giveaway_rules'] : '';
       $params['giveaway_product'] = $settings['giveaway_product'] ? $settings['giveaway_product'] : '';
        $params['giveaway-btn-title'] = $settings['giveaway-btn-title'] ? $settings['giveaway-btn-title'] : '';
        $params['giveaway-btn-link'] = $settings['giveaway-btn-link']['url'] ? $settings['giveaway-btn-link']['url'] : '';
        $params['giveaway_terms'] = $settings['giveaway_terms'] ? $settings['giveaway_terms'] : '';
        echo $this->show_giveaway_product($params);

    }

    public function show_giveaway_product($params)
    {

        $category_html="";
        $user_id = get_current_user_id();
        $giveaway_time = $params['luckydraw_time'];
        $isPM = $giveaway_time >= 12;
        $timeSuffix = $isPM ? 'PM' : 'AM';
        $formattedGiveawayTime = $giveaway_time . ' ' . $timeSuffix;
        $giveaway_product_id = $params['giveaway_product'];
        $gain_more_title = $params['giveaway-btn-title'];
        $gain_more_link = $params['giveaway-btn-link'];
        $giveawy_terms_des=$params['giveaway_terms'];
        $target_one = isset($params['target_one']) ? $params['target_one'] : '';
        $nofollow_one = isset($params['nofollow_one']) ? $params['nofollow_one'] : '';
        if ($gain_more_title != '' && $gain_more_link != '') {
            $button_gain_more = tgc_elementor_button_link($target_one, $nofollow_one, $gain_more_title, $gain_more_link, 'gain-entry-class');
        }

        $giveawy_combine = explode("_", $giveaway_product_id);
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
        if (user_can($user_id, 'administrator')) {

            update_user_meta($user_id,"tgc_product_gift",$giveaway_product_id);
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

        return '<input type="hidden" id="todateval" value="'.$gift_data_to_set.'"/><input type="hidden" id="tohours" value="'.$giveaway_time.'"/>
    <section class="giveaway-main-section">
          <div class="container giveawy-couter-container">
            <div class="row">
              <div class="col-xxl-12 col-xl-12 col-md-12 col-lg-12">
                <div class="timer-inner-counter float-end">
                  <div class="inner-time"><div class="countdown-container">
        <div class="countdown-days" style="float: left"><span class="days-counter"></span><span class="days-text">'.esc_html__('days /').'</span></div>
        <div class="countdown-hours" style="float: left;padding-left: 10px"><span class="hours-counter"></span><span class="hours-text">'.esc_html__('hours /').'</span></div>
        <div class="countdown-minutes" style="float: left;padding-left: 10px"><span class="minuts-counter"></span><span class="minuts-text">'.esc_html__('mins /').'</span></div>
        <div class="countdown-seconds" style="float: left;padding-left: 10px"><span class="secs-counter"></span><span class="secs-text">'.esc_html__('secs').'</span></div>
    </div></div>
               </div>
              </div>
             </div>
            </div>
           </section>
          <section class="giveaway-main-section">
          <div class="container container-giveawya">
            <div class="row">
              <div class="col-xxl-6 col-xl-6 col-md-6 col-lg-6 p-0">
                <div class="giveaway-product">
                  <div class="giveaway-product-inner">
                  <img src="' . esc_url($image_url) . '" alt="Product Image">
                  </div>
                </div>
              </div>
              <div class="col-xxl-6 col-xl-6 col-md-6 col-lg-6 giveawy-data-col px-0">
                <div class="giveaway-data">
                 <div class="giveawy-data-inner">
                 <div class="category"><span>'.esc_html__('Prize:').'</span>
                 <div class="category-inner">'.esc_html($cat_name).'</div> 
                 </div>
                  <div class="giveaway-title">'.esc_html($post_title).'</div>
                  <div class="giveaway-value">
                  <span class="valued">'.esc_html__('Valued at').'</span>
                  <span class="price">'.esc_html($price).'</span>
                  </div> 
                 <div class="giveaway-last-date">
                   <span class="draw-date">'.esc_html__('Draw Date:').'</span>
                  <div class="draw-data">'.$gift_convert_date.' <span>('.$formattedGiveawayTime.')</span></div>
                  </div>
                 <div class="giveawya-description">
                <div class="accordion giveawy-accordion" id="accordionPanelsStayOpenExample">
  <div class="accordion-item">
    <h4 class="accordion-header" id="panelsStayOpen-headingOne">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
        '.esc_html__('Product Details:').'
      </button>
    </h4>
    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingOne">
      <div class="accordion-body">
      '.esc_html($description).'
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h4 class="accordion-header" id="panelsStayOpen-headingTwo">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
       '.esc_html__('Giveaways Terms:').'
      </button>
    </h4>
    <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingTwo">
      <div class="accordion-body">
      '.$giveawy_terms_des.'
      </div>
    </div>
  </div>
</div>
                 </div>
                 
                 <div class="button-gain-more-entries">' . $button_gain_more . '</div>
                 
                 
                </div>
              </div>
            </div>
          </div>
       </section>';
    }





}
