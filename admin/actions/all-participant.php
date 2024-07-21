<div id="tab-content1" class="tab-content tgc-tab1-admin-entries">
    <?php
    $user_id = get_current_user_id();
    if (user_can($user_id, 'administrator')) {
        $giveaway_product_id =get_user_meta($user_id,"tgc_product_gift",true);
        $giveawy_title = get_the_title($giveaway_product_id);
        $thumbnail_id = get_post_thumbnail_id($giveaway_product_id);
        if ($thumbnail_id) {
            $image_url = wp_get_attachment_image_src($thumbnail_id, 'full');
            $image_url = $image_url[0];
           $givaway_image= "<img style='width: 30px;height: 30px; border-radius: 8px;' src=". esc_url($image_url)." alt='Product Image'/>";
           ?>
            <input type="hidden" id="givewaway-gift-id" value="<?php echo $giveaway_product_id ?>">
        <?php

        } else {
            $givaway_image="";
        }
    }
    $meta_key = 'user_entries';
    $args = array(
        'meta_query' => array(
            array(
                'key'     => $meta_key,
                'value'   => '',
                'compare' => '!=',
            ),
        ),
    );
    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();
    if (!empty($users)) {
        echo '<div class="wrap-table-1">';
        echo '<h3 class="wp-heading-inline tab-heading-main-inner">Lucky Draw Participants <button id="selectRandomRow" class="tgc-button-classes tgc-buttons-class">Lucky Draw</button><button id="resetentryuser" class="tgc-button-classes tgc-buttons-class">Reset Non-Members</button><button id="exportdata" class="tgc-button-classes tgc-buttons-class">Export/Download</button><button id="syncentries" class="sync-button tgc-button-classes tgc-buttons-class">Sync Entries</button></h3>';
        echo '<div class="tgc-outer-div">';
        echo '<table class="wp-list-table widefat fixed striped tgc-table-tab-one">';
        echo '<thead>';
        echo '<tr><th scope="col" style="padding: 8px;width:3%;">Serial</th><th scope="col" style="padding: 8px;width:5%;">User ID</th><th scope="col" style="padding: 8px;width:5%;">User Name</th><th scope="col" style="padding: 8px;width:10%;">User Email</th><th scope="col" style="padding: 8px;width:7%;">User Role</th><th scope="col" style="padding: 8px;width:10%;">Giveawy Product Title</th><th scope="col" style="padding: 8px;width:10%;">Giveawy Product Icon</th><th scope="col" style="padding: 8px;width:7%;">Active Memberships</th><th scope="col" style="padding: 8px;width:5%;">User Entries</th></tr>';
        echo '</thead>';
        echo '<tbody>';
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

                echo "<tr><td style=\"padding: 8px;\">$serial_number</td><td style=\"padding: 8px;\">$user_id</td><td style=\"padding: 8px;\">$user_name</td><td style=\"padding: 8px;\">$user_email</td><td style=\"padding: 8px;\">$user_role</td><td style=\"padding: 8px;\">$giveawy_title</td><td style=\"padding: 8px;\">$givaway_image</td><td style=\"padding: 8px;\">$member_package</td><td style=\"padding: 8px;\">$user_entries</td></tr>";
                $serial_number++;
            }
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';

    } else {
        echo '<p style="margin-top:30px">No Participants Found Please Sync the Data with memberships.<button  id="syncentries" class="sync-button tgc-button-classes tgc-buttons-class">Sync Entries</button></p>';
    }
    ?>
</div>

