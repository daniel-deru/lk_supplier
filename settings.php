<?php
/* 

Plugin Name: WP Supplier Feed
Plugin URI: https://smartmetatec.com/wpsmartsupplier
Description: Import products directly from the supplier feed
Version: 1.0.0
Author: Smart Meta Technologies
Author URI: https://smartmetatec.com

*/

?>
<?php

    if(isset($_POST)){
        add_option("WP_Smart_Feeds_consumer_key", $_POST['consumer_key']);
        add_option("WP_Smart_Feeds_consumer_secret", $_POST['consumer_secret']);
    }


?>
<?php

register_activation_hook(__FILE__, 'check_plugin_activation');

add_action("admin_menu", "MenuSetup");
add_action("admin_init", "WP_Smart_Feeds_Options");
add_action("admin_head", "add_style");


function MenuSetup(){
    add_menu_page("WP Smart Feed", "Smart Feed", 'manage_options', __FILE__, "WP_Smart_Feeds_Admin_Page");
}

function WP_Smart_Feeds_Admin_Page(){
    if (!current_user_can('manage_options')) wp_die(__("You don't have access to this page"));
    else {
        require_once(dirname(__FILE__)."/main.php");
    }

}

function check_plugin_activation(){
    add_option("WP_Smart_Feeds_activated", true);
}

function WP_Smart_Feeds_Options(){
    add_option("wp_smart_feeds_consumer_key", "". "", "yes");
    add_option("wp_smart_feeds_consumer_secret", "". "", "yes");

    add_option("wp_smart_feeds_syntech_feed", "", "", "yes");
    add_option("wp_smart_feeds_rectron_feed_onhand", "", "", "yes");
    add_option("wp_smart_feeds_rectron_feed_categories", "", "", "yes");

    add_option("wp_smart_feeds_base_margin", "", "", "yes");



}

function add_style($page){
    // if( "options-general.php" != $page){
    //     return;
    // }
    // wp_enqueue_style("wp_smart_feeds_style", plugins_url('public/css/admin.css', __FILE__));

    echo '<link rel="stylesheet" href="' . dirname(plugin_dir_url(__FILE__)) .'/lk_supplier/public/css/admin.css">';
    echo  '<script src="' . dirname(plugin_dir_url(__FILE__)) . '/lk_supplier/public/js/wp_smart_feeds_help.js" defer></script>';
}




