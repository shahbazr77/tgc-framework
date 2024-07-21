<div id="tab-content3" class="tab-content tgc-tab3-admin-entries">
    <?php
    global $wpdb;
    $table_name = $wpdb->prefix . 'tgc_giveaway_winners_table';
    $sql_tgc = "SELECT * FROM $table_name";
    $users = $wpdb->get_results($sql_tgc);
    if (!empty($users)) {
        echo '<div class="wrap-table-3">';
        echo '<h3 class="wp-heading-inline wp-heading-inline tab-heading-main-inner">All Time Winners <button id="resetalltimewinner" class="tgc-button-classes tgc-buttons-class">Reset All Time Winners</button></h3>';
        echo '<div class="tgc-outer-div">';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr><th scope="col" style="padding: 8px;width:3%;">Serial</th><th scope="col" style="padding: 8px;width:5%;">User ID</th><th scope="col" style="padding: 8px;width:5%;">User Name</th><th scope="col" style="padding: 8px;width:10%;">User Email</th><th scope="col" style="padding: 8px;width:10%;">Giveawy Product Title</th><th scope="col" style="padding: 8px;width:10%;">Giveawy Product Icon</th><th scope="col" style="padding: 8px;width:7%;">Active Memberships</th><th scope="col" style="padding: 8px;width:5%;">User Role</th><th scope="col" style="padding: 8px;width:5%;">Draw Date</th></tr>';
        echo '</thead>';
        echo '<tbody>';
        $serial_number = 1;
        foreach ($users as $user) {
            $user_id = $user->user_id;
            $giveaway_product_id = $user->gift_id;
            $giveawy_title = get_the_title($giveaway_product_id);
            $thumbnail_id = get_post_thumbnail_id($giveaway_product_id);
            if ($thumbnail_id) {
                $image_url = wp_get_attachment_image_src($thumbnail_id, 'full');
                $image_url = $image_url[0];
                $givaway_image= "<img style='width: 30px;height: 30px; border-radius: 8px;' src=". esc_url($image_url)." alt='Product Image'/>";
            } else {
                $givaway_image="";
            }
            $user_name = $user->user_name;
            $user_email = $user->user_email;
            $user_role = $user->user_role;
            $user_draw_date=$user->created_at;
            $member_package = isset($user->user_membership) ? $user->user_membership :'';
            echo "<tr><td style=\"padding: 8px;\">$serial_number</td><td style=\"padding: 8px;\">$user_id</td><td style=\"padding: 8px;\">$user_name</td><td style=\"padding: 8px;\">$user_email</td><td style=\"padding: 8px;\">$giveawy_title</td><td style=\"padding: 8px;\">$givaway_image</td><td style=\"padding: 8px;\">$member_package</td><td style=\"padding: 8px;\">$user_role</td><td style=\"padding: 8px;\">$user_draw_date</td></tr>";
            $serial_number++;
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p>No users found.</p>';
    }
    ?>
</div>