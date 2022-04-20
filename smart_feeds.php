<?php
/* 

Plugin Name: WP Supplier Feed
Plugin URI: https://smartmetatec.com/wpsmartsupplier
Description: Import products directly from the supplier feed
Version: 1.0.0
Author: Smart Meta Technologies
Author URI: https://smartmetatec.com

*/

register_activation_hook(__FILE__, 'check_plugin_activation');

function check_plugin_activation(){
    // Required to interact with woocommerce
    if(!get_option("smt_smart_feeds_consumer_key")) add_option("smt_smart_feeds_consumer_key", "". "", "yes");
    if(!get_option("smt_smart_feeds_consumer_secret")) add_option("smt_smart_feeds_consumer_secret", "". "", "yes");

    // Settings for the plugin
    if(!get_option("smt_smart_feeds_rectron_feed_onhand")) add_option("smt_smart_feeds_rectron_feed_onhand", "", "", "yes");
    if(!get_option("smt_smart_feeds_base_margin")) add_option("smt_smart_feeds_base_margin", "", "", "yes");
    if(!get_option("smt_smart_feeds_interval")) add_option("smt_smart_feeds_interval", "", "", "yes");
}



add_action("admin_menu", "MenuSetup");

// Create Menu Page
function MenuSetup(){
    // add_menu_page("WP Smart Feed", "Smart Feed", 'manage_options', __FILE__, "WP_Smart_Feeds_Admin_Page");
    add_menu_page( 
        "Smart Feed", 
        "Smart Feed", 
        "manage_options", 
        "smt_smart_feeds_main_settings", // This is the page name
        "WP_Smart_Feeds_Admin_Page"
    );

    add_submenu_page( 
        'smt_smart_feeds_main_settings', 
        "Products", "Products", 
        "manage_options", 
        "smt_smart_feeds_product_settings", // This is the page name
        "smt_smart_feed_admin_product_settings" 
    );
}

// Callback to load the main menu page
function WP_Smart_Feeds_Admin_Page(){
    if (!current_user_can('manage_options')) return wp_die(__("You don't have access to this page"));
    require_once("menus/main.php");
}

// Calback for the submenu page
function smt_smart_feed_admin_product_settings(){
    if (!current_user_can('manage_options')) return wp_die(__("You don't have access to this page"));
    require_once("menus/products.php");
}

//  Add admin styles and scripts
add_action("admin_enqueue_scripts", "smt_lk_supplier_scripts");

function smt_lk_supplier_scripts(){
    global $pagenow;
    if(isset($_GET['page'])) $page = sanitize_text_field($_GET['page']);

    wp_enqueue_style("smt_smart_commerce_fontawesome_css", "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css");
    wp_enqueue_script("smt_smart_commerce_fontawesome_js", "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js", [], false, true);

    if($pagenow === 'admin.php' && $page === "smt_smart_feeds_main_settings") {
        // enqueue css
        wp_enqueue_style("smt_lk_supplier_admin_style", plugins_url("/public/css/admin.css", __FILE__));

        // enqueue js
        wp_enqueue_script("smt_lk_supplier_admin_script", plugins_url("/public/js/smt_smart_feeds_help.js", __FILE__), array('jquery'), false, true);
    }

}
