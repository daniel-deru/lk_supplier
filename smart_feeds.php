<?php
/* 

Plugin Name: WP Supplier Feed
Plugin URI: https://smartmetatec.com/wpsmartsupplier
Description: Import products directly from the supplier feed
Version: 1.0.0
Author: Smart Meta Technologies
Author URI: https://smartmetatec.com

*/

// require_once "woocommerce-api.php";
require_once "rectron.php";
require_once "includes/tax_classes.php";
require_once "includes/convert.php";

register_activation_hook(__FILE__, 'check_plugin_activation');


function check_plugin_activation(){
    // Required to interact with woocommerce
    // if(!get_option("smt_smart_feeds_consumer_key")) add_option("smt_smart_feeds_consumer_key", "". "", "yes");
    // if(!get_option("smt_smart_feeds_consumer_secret")) add_option("smt_smart_feeds_consumer_secret", "". "", "yes");

    // Settings for the plugin
    if(!get_option("smt_smart_feeds_rectron_feed_onhand")) add_option("smt_smart_feeds_rectron_feed_onhand", "", "", "yes");
    if(!get_option("smt_smart_feeds_base_margin")) add_option("smt_smart_feeds_base_margin", "", "", "yes");
    if(!get_option("smt_smart_feeds_interval")) add_option("smt_smart_feeds_interval", "", "", "yes");
    if(!get_option("smt_smart_feeds_tax_rate")) add_option("smt_smart_feeds_tax_rate", 15);
    if(!get_option("smt_smart_feeds_import_stock")) add_option("smt_smart_feeds_import_stock", 0);

    if(!get_option('smt_smart_feeds_exclude_products')) add_option('smt_smart_feeds_exclude_products');
    if(!get_option('smt_smart_feeds_dynamic_rules')) add_option('smt_smart_feeds_dynamic_rules', json_encode([]));

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

    echo "<pre>";
    print_r($data);
    echo "</pre>";
    
    update_option('smt_smart_feeds_dynamic_rules', $data);
    wp_die();
};

add_action('wp_ajax_smt_smart_feeds_get_custom_product_data', 'smt_smart_feeds_get_custom_product_data');
add_action('wp_ajax_nopriv_smt_smart_feeds_get_custom_product_data', 'smt_smart_feeds_get_custom_product_data');

function smt_smart_feeds_get_custom_product_data(){
    $data = $_POST['data'];
    $products_array = [];
    $query_args = array(
        'post_type' => 'product',
        'posts_per_page' => -1
    );
    $products = new WP_Query($query_args);
    if($products->have_posts()){
        while($products->have_posts()){
            $products->the_post();
            global $product;
            $sku = $product->get_sku();
            $products_array[$sku] = $product;
        }
    }
    
    foreach($data as $sku => $custom_data){
        if(!$products_array[$sku]) continue;

        $product = $products_array[$sku];
        
        $meta_data = $product->get_meta_data();
        $custom_meta = null;
        foreach($meta_data as $meta){
            if($meta->get_data()['key'] == 'custom') $custom_meta = $meta->get_data()['value'];
        }
        // All the product attributes
        format($custom_meta);


        $product_price = floatval($product->get_price());
        $product_tax = (floatval(TaxClass::getTaxRate()) + 100) / 100;
        $product_profit = (floatval($custom_meta['margin']) + 100) / 100;

        $cost = calcCostPrice($product_price, $product_tax, $product_profit);

        if(isset($custom_data['skip'])){
           
            if($custom_data['skip'] == 0){
                // Make the product a published
                $product->set_status('publish');
                $custom_meta['skip'] = '0';
            } else {
                // Make the product draft
                $product->set_status('draft');
                $custom_meta['skip'] = '1';
            }
        }
        if(isset($custom_data['otherCost'])){
            $otherCost = floatval($custom_data['otherCost']);
            $custom_meta['other_cost'] = $otherCost;
            $cost += $otherCost;
        }
        if(isset($custom_data['markup'])){

            $markup = floatval($custom_data['markup']);
            $markupType = $custom_data['markupType'];

            $custom_meta['margin'] = $markup;
            $custom_meta['margin_type'] = $markupType;

            if($markupType == "fixed") $cost += $markup;
            else if($markupType == "percent") $cost += $cost * ($markup / 100);
            // Get the product price before vat and profit and add the new profit

        } else {
            $markup = floatval($custom_meta['margin']);
            $markupType = $custom_meta['margin_type'];

            if($markupType == "fixed") $cost += $markup;
            else if($markupType == "percent") $cost += $cost * ($markup / 100);
        }

        $product_price_including = $cost * $product_tax;
       
        format($custom_meta);
        format($product_price_including);
        $product->update_meta_data('custom', $custom_meta);

        $product->set_regular_price($product_price_including);
        $product->save();
    }
    wp_die();
};

// This sets the product image in the store
add_filter( 'woocommerce_product_get_image', 'thumbnail_external_replace', 10, PHP_INT_MAX );
function smart_feed_replace_woo_image($image, $product){
    if(isset($product)) {
        $images = $product->get_attributes();
        $image = $images[0];
    }
    return $image;
}


// add_filter( 'post_thumbnail_html', 'thumbnail_external_replace', 10, PHP_INT_MAX );
function thumbnail_external_replace( $html, $post_id ) {

    $product = new WC_Product($post_id);
    $images = $product->get_attributes();

    if(isset($images['external_image'])){
        $images = $images['external_image']->get_options();
        return '<div style="
            width: 300px; 
            height: 300px;
            margin: auto;
            display: flex; 
            justify-content: center;
            align-items: center;">
                <img style="
                    max-height: 300px;
                    width: auto; 
                    aspect-ratio: auto; 
                    overflow: hidden;
                    margin: auto;" src="' . esc_url( $images[0] ) . '" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" loading="lazy" />
            </div>';
    }

    return $html;
}


// This is the product image on the product page
add_filter('woocommerce_single_product_image_thumbnail_html', "product_page_html", 10, 2);
function product_page_html($html, $post_thumnail_id){
    global $product;
    $product = new WC_Product($product->get_id());

    // Get the product attributes to determine if it is a feed product or normal product
    $productAttributes = $product->get_attributes();

    if(!isset($productAttributes['external_image'])) return $html;

    // Get the images
    $images = $productAttributes['external_image']->get_options();

    // If there is no image or the external_image attribute doesn't exist on the product return the original html
    if($images[0] === '') return $html;

    // Create the custom image html if the external image exists
    $image = '<div data-thumb="%1$s" data-thumb-alt="" class="woocommerce-product-gallery__image"><a href="%1$s"><img width="600" height="642" src="%1$s" class="" alt="" loading="lazy" title="61S2qlMWh6L._AC_SX679_" data-caption="" data-src="%1$s" data-large_image="%1$s" data-large_image_width="679" data-large_image_height="727" /></a></div>';
    return sprintf($image, $images[0]);
}

// This creates the product gallery template
add_filter('wc_get_template', "product_gallery_template", 999, 2);
function product_gallery_template($template, $template_name){
    // global $product;
    // $product = new WC_Product($product->get_id());
    // $images = $product->get_attributes();
    if($template_name === "single-product/product-thumbnails.php"){
        $template = plugin_dir_path( __FILE__ ) . 'templates/single-product/gallery_template.php' ;
    }
    return $template;
}


add_filter( 'woocommerce_get_image_size_gallery_thumbnail', function( $size ) {
    return array(
    'width' => 150,
    'height' => 150,
    'crop' => 0,
    );
    } );

// Round the woocommerce price according to preferance
function smt_smart_feeds_round_price($price, $product = NULL){
    $price = floor($price) + .9;
    return $price;
}
add_filter('woocommerce_product_get_price', 'smt_smart_feeds_round_price', 99, 2);
add_filter('woocommerce_get_variation_regular_price', 'smt_smart_feeds_round_price', 99);
add_filter('woocommerce_get_variation_price', 'smt_smart_feeds_round_price', 99);




// Register the hook on plugin activation
register_activation_hook(__FILE__, 'my_cron_job_activation');
add_action('my_cron_event', 'my_cron_job');

function my_cron_job_activation() {
    wp_schedule_event(time(), 'fively', 'my_cron_event');
}

// Unregister the hook on plugin deactivation
register_deactivation_hook( __FILE__, 'my_cron_job_deactivation' );
function my_cron_job_deactivation(){
  wp_clear_scheduled_hook( 'my_cron_event' );
}