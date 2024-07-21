<?php
add_action('wp_ajax_save_lucky_winners', 'save_lucky_winners_fun');
add_action('wp_ajax_resetentryusers', 'reset_entry_users_fun');
add_action('wp_ajax_export_entries_data', 'export_entries_data_fun');
add_action('wp_ajax_reset_luckydraw_winners', 'reset_luckydraw_winners_fun');
add_action('wp_ajax_reset_alltime_winners', 'reset_alltime_winners_fun');
add_action('wp_ajax_sync_entries_data', 'update_user_entries_with_active_memberships');
function save_lucky_winners_fun() {
        if (isset($_POST['user_id'])) {
         $selected_user_id = sanitize_text_field(wp_unslash($_POST['user_id']));
            $user_gift_id = sanitize_text_field(wp_unslash($_POST['gift_id']));
            $update_status= get_user_meta($selected_user_id,"lucky_draw_winner",true);
            if($update_status){
                $user_data = get_userdata($selected_user_id);
                $user_name = $user_data->display_name;
                $return = array('old_user_status'=>1,'staut_msg'=>'user is already winner','luckywinner' => $user_name);
                wp_send_json_success($return);
                wp_die();
            }else {
                global $wpdb;
                $table_name = $wpdb->prefix.'tgc_giveaway_winners_table';
                $users_data=wc_memberships_get_user_memberships($selected_user_id);
                if(!empty($users_data)){
                    $member_package = '';
                    foreach ($users_data as $user_key){
                        if ($user_key->status == 'wcm-active') {
                            $plan_id=$user_key->plan_id;
                            $plan_title = get_the_title($plan_id);
                            $member_package .= $plan_title . ', ';
                        }
                    }
                    $member_package = rtrim($member_package, ', ');
                }else {
                    $member_package = '-';
                }
                $update_status = update_user_meta($selected_user_id, "lucky_draw_winner", $user_gift_id);
                $user_exists = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $table_name WHERE user_id = %d", $selected_user_id));
                $user_data = get_userdata($selected_user_id);
                $user_name = $user_data->display_name;
                $user_email = $user_data->user_email;
                $user_role = implode(', ', $user_data->roles);
                $runmy_query = "INSERT INTO $table_name (user_id, user_name,user_email,user_role,user_membership,gift_id) VALUES ('$selected_user_id', '$user_name','$user_email','$user_role','$member_package','$user_gift_id')";
                require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
                dbDelta($runmy_query);
                $return = array('old_user_status'=>0,'staut_msg'=>'new winner','luckywinner' => $user_name);
                wp_send_json_success($return);
                wp_die();
            }

        } else {
            $return ="";
            wp_send_json_error($return);
            wp_die();
        }

}
function reset_entry_users_fun() {
    $meta_key = 'user_entries';
    $args = array(
        'meta_query' => array(
            array(
                'key'     => $meta_key,
                'value'   => '', // Empty value to check for not null
                'compare' => '!=', // Compare for not equal to
            ),
        ),
    );
    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();
    $tgcMiniCartSidebar = Tgc_Mini_Cart_Sidebar::get_instance();
    if (!empty($users)) {
        foreach ($users as $user) {
            $tgc_instance = Tgc_Mini_Cart_Sidebar::get_instance();
            $users_data = $tgcMiniCartSidebar->get_active_members_for_membership($user->ID);
            if (empty($users_data)) {
                delete_user_meta($user->ID, $meta_key);
            }
        }
        $current_tab=admin_url('admin.php?page=tgc_entries&tab=participant');
        $return = array('staut_msg'=>'Successfully Reset Participants','current_tab_reload'=>$current_tab);
        wp_send_json_success($return);
        wp_die();
    } else {
        $return = array('staut_msg'=>'Sorry No User Found');
        wp_send_json_error($return);
        wp_die();
    }

}
function export_entries_data_fun(){
    if (!current_user_can('administrator')) {
        wp_die('Unauthorized user');
    }

    $user_id = get_current_user_id();
    if (user_can($user_id, 'administrator')) {
        $giveaway_product_id =get_user_meta($user_id,"tgc_product_gift",true);
        $giveawy_title = get_the_title($giveaway_product_id);
        $thumbnail_id = get_post_thumbnail_id($giveaway_product_id);

    }

    $meta_key = 'user_entries';
    $args = array(
        'meta_query' => array(
            array(
                'key'     => $meta_key,
                'value'   => '', // Empty value to check for not null
                'compare' => '!=', // Compare for not equal to
            ),
        ),
    );
    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();
    $csv_data = array();
    $csv_data[] = array('Serial', 'User ID', 'User Name', 'User Email', 'User Role', 'Giveaway Product Title', 'Giveaway Product ID', 'Active Memberships', 'User Entries');
    $serial_number = 1;
    foreach ($users as $user) {
        $user_id = $user->ID;
        $users_data=wc_memberships_get_user_memberships($user_id);
        $user_name = $user->user_login;
        $user_email = $user->user_email;
        $user_data = get_userdata($user_id);
        $user_role = implode(', ', $user_data->roles);
        $user_entries = get_user_meta($user_id, $meta_key, true);
        $row_count = intval($user_entries);
        for ($i = 0; $i < $row_count; $i++) {
            if(!empty($users_data)){
                $member_package = '';
                foreach ($users_data as $user_key){
                    if ($user_key->status == 'wcm-active') {
                        $plan_id=$user_key->plan_id;
                        $plan_title = get_the_title($plan_id);
                        $member_package .= $plan_title . ', ';
                    }
                }
                $member_package = rtrim($member_package, ', ');

            }else {
                $member_package = '-';
            }
            //$member_package = isset($users_data[$i]->post_title) ? $users_data[$i]->post_title : "-";
            $csv_data[] = array($serial_number, $user_id, $user_name, $user_email, $user_role, $giveawy_title, $giveaway_product_id, $member_package, $user_entries);

            $serial_number++;
        }
    }

    // Prepare the CSV content
    $output = fopen('php://memory', 'w');
    foreach ($csv_data as $row) {
        fputcsv($output, $row);
    }
    fseek($output, 0);
    $csv_contents = stream_get_contents($output);
    fclose($output);

    // Return the CSV content
    wp_send_json_success(array('csv' => base64_encode($csv_contents)));
}
function reset_luckydraw_winners_fun() {
    $users = get_users();
    $meta_key = 'lucky_draw_winner';
    //$meta_key = 'user_entries';
    if (!empty($users)) {
        foreach ($users as $user) {
            delete_user_meta($user->ID, $meta_key);
        }
        $return = array('staut_msg'=>'Reset All winners');
        wp_send_json_success($return);
        wp_die();
    } else {
        $return = array('staut_msg'=>'No Winners are Found');
        $return ="";
        wp_send_json_error($return);
        wp_die();
    }

}
function reset_alltime_winners_fun() {
    global $wpdb;
    $table_name = $wpdb->prefix.'tgc_giveaway_winners_table';
    $wpdb->query("TRUNCATE TABLE $table_name");
    if ($wpdb->last_error) {
        $return = array('staut_msg'=>'Something went wrong');
        wp_send_json_error($return);
        wp_die();
    }else{
        $return = array('staut_msg'=>'Record has been successfully deleted');
        wp_send_json_success($return);
        wp_die();
    }

}
function update_user_entries_with_active_memberships() {
    $users = get_users(array('fields' => 'ID'));
    foreach ($users as $user_id) {
        $membership_array=wc_memberships_get_user_memberships($user_id);
        if(!empty($membership_array)){
            update_user_meta($user_id, 'user_entries', "");
            foreach ($membership_array as $membership) {
                if ($membership->status == 'wcm-active') {
                    $membership->plan_id;
                    $user_old_entries_updated = get_user_meta($user_id, 'user_entries', true);
                    $mp_luckydraw_entry = get_post_meta($membership->plan_id, 'ms_luckydraw_entry', true);
                    $user_updated_entries = intval($user_old_entries_updated) + intval($mp_luckydraw_entry);
                    update_user_meta($user_id, 'user_entries', $user_updated_entries);
                }

            }

        }
    }

    $return = array('staut_msg'=>'Record has been successfully Updated');
    wp_send_json_success($return);
    wp_die();

}
//add the metabox  for membership Luckydraw Entries
function add_membership_plan_meta_box_entries() {
    add_meta_box(
        'membership_plan_meta_box', // ID
        'Membership Lucky Draw Entries', // Title
        'membership_plan_meta_entries_callback', // Callback
        'wc_membership_plan', // Post type
        'normal', // Context
        'low' // Priority
    );
}
add_action( 'add_meta_boxes_wc_membership_plan', 'add_membership_plan_meta_box_entries' );
// Callback function to render the meta box
function membership_plan_meta_entries_callback( $post ) {
    $mp_luckydraw_entry = get_post_meta( $post->ID, 'ms_luckydraw_entry', true );
    ?>
    <p>
        <label for="ms_luckyentries">Lucky Draw Entries :</label>
        <input type="number"  id="ms_luckyentries" name="ms_luckyentries" value="<?php echo $mp_luckydraw_entry; ?>">
    </p>
    <?php
}
function save_membership_plan_meta_box( $post_id ) {
    if ( isset( $_POST['ms_luckyentries'] ) ) {
        update_post_meta( $post_id, 'ms_luckydraw_entry', sanitize_text_field( $_POST['ms_luckyentries'] ) );
    }
}
add_action( 'save_post_wc_membership_plan', 'save_membership_plan_meta_box' );