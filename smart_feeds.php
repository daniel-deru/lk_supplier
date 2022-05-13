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

    if(!get_option('smt_smart_feeds_exclude_products')) add_option('smt_smart_feeds_exclude_products');
    if(!get_option('smt_smart_feeds_dynamic_rules')) add_option('smt_smart_feeds_dynamic_rules');

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
        "Products", 
        "Products", 
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
    if($pagenow === 'admin.php'){
        if($page === "smt_smart_feeds_main_settings") {
            wp_enqueue_style("smt_lk_supplier_admin_style", plugins_url("/public/css/admin.css", __FILE__));
            wp_enqueue_script("smt_lk_supplier_admin_script", plugins_url("/public/js/smt_smart_feeds_help.js", __FILE__), array('jquery'), false, true);
            wp_localize_script("smt_lk_supplier_admin_script", "smart_feed_data", array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'dynamic_rules' => get_option('smt_smart_feeds_dynamic_rules')
            ));
        }
        if($page === "smt_smart_feeds_product_settings"){
            wp_enqueue_style("smt_smart_feeds_style_product_style", plugins_url("/public/css/products.css", __FILE__));
            wp_enqueue_script("smt_smart_feeds_products_script", plugins_url("/public/js/smt_product_feed.js", __FILE__), array('jquery'), false, true);
        }

    }

    

}

add_action('wp_ajax_get_rules', 'get_rules');
add_action('wp_ajax_nopriv_get_rules', 'get_rules');

function get_rules(){
    $data = json_encode($_POST['rules']);
    echo count($_POST['rules']);
    update_option('smt_smart_feeds_dynamic_rules', $data);
    wp_die();
};


// add_filter( 'post_thumbnail_html', 'thumbnail_external_replace', 10, PHP_INT_MAX );
add_filter( 'woocommerce_product_get_image', 'thumbnail_external_replace', 10, PHP_INT_MAX );

function thumbnail_external_replace( $html, $product ) {
    // $url =  get_post_meta( $post_id, '_thumbnail_ext_url', TRUE );
    $product = new WC_Product($product->get_id());
    $images = $product->get_attributes()['external_image']->get_options();
    return '<img width="260" height="300" src="' . esc_url( $images[0] ) . '" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" loading="lazy" />';
    // if ( empty( $images[0] )) {
    //     return $html;
    // }
    // $alt = get_post_field( 'post_title', $post_id ) . ' ' .  __( 'thumbnail', 'txtdomain' );
    // $attr = array( 'alt' => $alt );
    // $attr = array();
    // $attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, NULL );
    // $attr = array_map( 'esc_attr', $attr );
    // $html = sprintf( '<img src="%s"', esc_url($images[0]) );
    // foreach ( $attr as $name => $value ) {
    //     $html .= " $name=" . '"' . $value . '"';
    // }
    // $html .= ' />';
    // return $html;
}

add_filter('woocommerce_single_product_image_thumbnail_html', "product_page_html", 10, 2);

function product_page_html($html, $post_thumnail_id){
    global $product;
    $product = new WC_Product($product->get_id());
    $images = $product->get_attributes()['external_image']->get_options();
    if($images[0] === '') return $html;
    $image = '<div data-thumb="%1$s" data-thumb-alt="" class="woocommerce-product-gallery__image"><a href="%1$s"><img width="600" height="642" src="%1$s" class="" alt="" loading="lazy" title="61S2qlMWh6L._AC_SX679_" data-caption="" data-src="%1$s" data-large_image="%1$s" data-large_image_width="679" data-large_image_height="727" /></a></div>';
    return sprintf($image, $images[0]);
}

// add_filter('wc_get_template', "product_gallery_template", 999, 2);

// function product_gallery_template($template, $template_name){
//     if($template_name === "single-product/gallery_template.php"){
//         $template = path_join( get_stylesheet_directory(), 'templates/single-product/gallery_template.php' );
//     }
//     return $template;
// }

add_filter('woocommerce_product_get_gallery_image_ids', 'set_custom_gallery_image_ids', 99, 2);

function set_custom_gallery_image_ids($value, $product){
    $product_id = $product->get_id();

    $product = new WC_Product($product_id);
    $images = $product->get_attributes()['external_image']->get_options();

    if(empty($product_id)) return $value;
    if(empty($images)) return $value;

    $gallery_ids = [];
    for($i = 0; $i < count($images); $i++){
        $gallery_ids[] = $i . "__" . $product_id . "__" . $images[$i];
    }
    return $gallery_ids;

}

add_filter('wp_get_attachment_image_src', 'gallery_images', 99, 2);

function gallery_images($image, $attachment_id){
    $attachment = explode("__", $attachment_id);
    if(count($attachment) > 1){
        // $image_num = $attachment[0];
        // $product_id = $attachment[1];
        $image_url = $attachment[2];
        // $product = new WC_Product($product_id);
        // $images = $product->get_attributes()['external_image']->get_options();
        return array($image_url, 800, 600, false);
    }

    return $image;
}