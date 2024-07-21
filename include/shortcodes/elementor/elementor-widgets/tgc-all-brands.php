<?php
class TGC_All_Brands extends Elementor\Widget_Base
{
  public function get_name(){
   return "tgc-all-brands";
   }
   public function get_title()
   {
       return __('TGC All Brands','tgc-framework');
   }
   public function get_icon()
   {
       return 'eicon-product-categories';
   }
    public function get_categories()
    {
        return ['tgc'];
    }
    public function get_script_depends()
    {
        return [''];
    }
    protected function _register_controls(){
        $this->start_controls_section(
            'tgc_upcoming_giveaway_section',
            [
                'label' => __('TGC All Brands', 'tgc-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'all-brands-heading',
            [
                'label' => __('All Brands Main Section Heading', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Get access to perks and discounts to over 50 of the best brands in global fashion:', 'tgc-framework'),
                'placeholder' => __('Upcoming Giveaways Section Heading', 'tgc-framework'),
                'label_block' => true
            ]
        );
        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'all-brands-name',
            [
                'label' => __('Chose Brands Names', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => get_all_brands_names_fun(), // Placeholder for dynamic options
                'label_block' => true
            ]
        );

        $this->add_control(
            'all-brands-repeater',
            [
                'label' => __('All Brands Repeater', 'tgc-framework'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'all-brands-name' => '',
                    ],
                ],
            ]
        );

        $this->end_controls_section();
    }
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $params['all-brands-heading'] = $settings['all-brands-heading'] ? $settings['all-brands-heading'] : '';
        $params['all-brands-repeater'] = $settings['all-brands-repeater'] ? $settings['all-brands-repeater'] : array();
        echo $this->get_all_brands_render($params);
    }

    public function get_all_brands_render($params){
        $all_brands_heading = $params['all-brands-heading'];
        $all_brands_repeater = $params['all-brands-repeater'];
        $taxonomy = 'brands'; // Replace with your actual taxonomy
        $current_letter = '';
        $brands_html = '';
        if ($all_brands_repeater) {
            foreach ($all_brands_repeater as $item) {
               $category_id = $item['all-brands-name'];
                $term = get_term($category_id);
                $cat_name = $term->name;
                $first_character = strtoupper(substr($cat_name, 0, 1));
                $first_letter = is_numeric($first_character) ? '#' : $first_character;
                if ($first_letter !== $current_letter) {
                    $current_letter = $first_letter;
                    $brands_html.='<li style="list-style:none;display:inline-block;width:20%;">';
                    $brands_html.='<h3>' . $first_letter . '</h3><a style="display:block;" href="' . get_term_link($term) . '">' . $cat_name . '</a>';
                }else{
                    $brands_html.='<a style="display:block;" href="' . get_term_link($term) . '">' . $cat_name . '</a>';
                }

            }
        }


        return '<section class="all-brands-section">
          <div class="container-fluid">
             <div class="container-brands">
              <div class="col-xxl-12 col-xl-12 col-lg-12">
                 <div class="brands-main-heading">
                   <h3 class="head-title">' . $all_brands_heading . '</h3>
                  </div>
              </div>
              <div class="brands-main-category">
                <ul class="brands-ul-custom-class">
                   ' . $brands_html . '
                </ul>   
                </div>
             </div>   
          </div>
         </section>';



    }

}
