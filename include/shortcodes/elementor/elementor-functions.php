<?php
function get_product_against_rule() {
    $options = [];
    $args = array(
        'post_type'      => 'fgf_rules',
        'post_status'    => 'fgf_active',
        'posts_per_page' => -1,
    );
    $custom_query = new WP_Query($args);

    if ($custom_query->have_posts()) {
        while ($custom_query->have_posts()) {
            $custom_query->the_post();
            $post_title = get_the_title();
            $free_gift_product_id_array = get_post_meta(get_the_ID(), 'fgf_gift_products', true);
            if (!empty($free_gift_product_id_array)) {
                foreach ($free_gift_product_id_array as $product_id) {
                    $product = get_post($product_id);

                    if ($product) {
                        $key = get_the_ID() . '_' . $product->ID;
                        $options[$key] = $post_title . ' - ' . $product->post_title;
                    }
                }
            }
        }

        return $options;
    } else {
        // No active posts found.
        //echo 'No active posts found.';
        return $options;
    }
}
function get_all_upcoming_giveawys() {
    $upcoming = [];
    $args = array(
        'post_type'      => 'fgf_rules',
        'post_status'    => 'fgf_inactive',
        'posts_per_page' => -1,
    );
    $custom_query = new WP_Query($args);

    if ($custom_query->have_posts()) {
        while ($custom_query->have_posts()) {
            $custom_query->the_post();
            $post_title = get_the_title();
            $free_gift_product_id_array = get_post_meta(get_the_ID(), 'fgf_gift_products', true);
            if (!empty($free_gift_product_id_array)) {
                foreach ($free_gift_product_id_array as $product_id) {
                    $product = get_post($product_id);

                    if ($product) {
                        $key = get_the_ID() . '_' . $product->ID;
                        $upcoming[$key] = $post_title . ' - ' . $product->post_title;
                    }
                }
            }
        }
        return $upcoming;
    } else {
        // No active posts found.
       // echo 'No active posts found.';
        return $upcoming;
    }
}


function get_all_winners(){
    $all_winner_data = array();
    global $wpdb;
    $table_name = $wpdb->prefix . 'tgc_giveaway_winners_table';
    $sql_tgc = "SELECT * FROM $table_name";
    $users = $wpdb->get_results($sql_tgc);
    if (!empty($users)) {
        foreach ($users as $user) {
            $user_table_id = $user->id;
            $user_id = $user->user_id;
            $user_name = $user->user_name;
            $user_email = $user->user_email;
            $user_role = $user->user_role;
            $user_draw_date=$user->created_at;
            $user_product_id = $user->gift_id;
            $post_title = get_the_title($user_product_id);
            $key = $user_id. '_' . $user_product_id;
            $all_winner_data[$key] = $user_name."--".$post_title;
        }
        return $all_winner_data;
    }else {
        return $all_winner_data;
    }

}

if (!function_exists('tgc_elementor_button_link')) {
    function tgc_elementor_button_link($is_external = '', $nofollow = '', $btn_title = 'Button Link', $url = '', $class_css = '', $i_class = '')
    {
        $i_class_html = '';
        $target = $is_external ? ' target="_blank"' : '';
        $nofollow = $nofollow ? ' rel="nofollow"' : '';
        if ($i_class != '') {
            $i_class_html = '';
        }
        return '<a href="' . esc_url($url) . '" class="' . $class_css . '"' . $target . $nofollow . '>' . $i_class_html . esc_html($btn_title) . '</a>';
    }
}
function tgc_elementor_all_rules() {
    $rules_data = array();
    $args = array(
        'post_type'      => 'fgf_rules',
        'post_status'    => 'fgf_active',
        'posts_per_page' => -1,
    );
    $custom_query = new WP_Query($args);
    if ($custom_query->have_posts()) {
        while ($custom_query->have_posts()) {
            $custom_query->the_post();
            $rules_data[get_the_ID()] = get_the_title(); // Use an associative array to store post ID => post title
        }
        return $rules_data;
    } else {
        //echo 'No active posts found.';
        return $rules_data; // Return an empty array if no posts are found
    }
}

function get_all_brands_names_fun(){
    if (taxonomy_exists('brands')) {
        $brands = [];
        $taxonomy = 'brands'; // Change 'brands' to your custom taxonomy name.
        $categories = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ));

        if ($categories) {
            foreach ($categories as $category) {
                $brands[$category->term_id] = $category->name;
            }
            return $brands;
        } else {
           // echo 'No categories found.';
            return $brands;
        }
        return $brands;
    }
}



//function get_all_enries_product() {
//   $product_id = 5926;
//
//    $product = wc_get_product($product_id);
//    $variations = [];
//
//    if ($product && $product->is_type('variable')) {
//        $available_variations = $product->get_available_variations();
//        foreach ($available_variations as $variation) {
//            $variation_id = $variation['variation_id'];
//            $variation_name = implode(' / ', array_values($variation['attributes']));
//            $variations[$variation_id."_".$variation_name] = $variation_name;
//
//        }
//    }
//
//    return $variations;
//}



function get_all_enries_product() {
    // Specify the product ID
    $product_id = 8555;
    // Get the product
    $product = wc_get_product($product_id);
    $variations = [];
    // Check if the product is valid and of type 'variable-subscription'
    if ($product && $product->is_type('variable-subscription')) {
        // Get the product variations
        $product_variations = $product->get_children();

        // Prepare options array for Elementor
        $options = [];

        // Loop through each variation
        foreach ($product_variations as $variation_id) {
            $variation = wc_get_product($variation_id);

            // Check if the variation is valid
            if ($variation && $variation->is_type('variation')) {
                // Get the 'entries' attribute for the variation dynamically
                $entries = $variation->get_attribute('entries');

                // Ensure the variation has the 'entries' attribute set
                if ($entries) {
                    // Add variation ID and entries attribute value to options array
                    $options[$variation_id."_".$entries] = $entries;
                }
            }
        }

        // Return options array for Elementor control
        return $options;
    }

    // If no valid options found, return empty array or handle as needed
    return [];
}



