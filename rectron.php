<?php

require_once "woocommerce-api.php";
require_once 'includes/convert.php';
require_once 'includes/print.php';
require_once 'includes/categories.php';

/*
    Things you need to add to the wp product
    1. Categories
    2. Images
    3. Name
    4. Description
    5. Short Description
    6. SKU
    8. Regular Price
    9. Sale Price
    10. Manage Stock = true
    11. Stock Quantity



*/

class Rectron  {
    private $onhand_feed;
    private $categories = "https://content.storefront7.co.za/stores/za.co.storefront7.rectron/xmlfeed/rectronfeed-637806849145434755.xml";
    private $attribute_name = "rectron";
    public $categories_data = null;
    // private $woocommerce;

    // Class constructor function
    function __construct($existing_categories, $woocommerce){
        $this->existing_categories = $existing_categories;
        $this->woocommerce = $woocommerce;
        $this->register_feed();
        // This categories data is the data from the feed NOT the wordpress categories
        $this->categories_data = $this->get_categories();
        $this->create_categories();
        // $this->create_images();
    }

    // Called in the constructor to get the feed url
    function register_feed(){
        $feed = get_option("smt_smart_feeds_rectron_feed_onhand");

        if(!$feed) return;

        if($this->verify($feed)) $this->onhand_feed = $feed;
    }

    // Helper function to verify the feed
    function verify($feed){
        $onhand_pattern = "/https:\/\/rctdatafeed.azurewebsites.net\/xml\/[a-z0-9-]+\/v[0-9]{1,9}\/products\/onhand/i";
        return preg_match($onhand_pattern, $feed) ? true : false;
    }

    // Get WooCommerce Products filter out rectron products and convert to associative array
    function getWCProducts($woocommerce){
        if(!(get_option("smt_smart_feeds_consumer_key") && get_option("smt_smart_feeds_consumer_secret"))) return;

        $WCProducts = json_decode(smt_smart_feeds_listProducts(1, $woocommerce), true);

        $rectronProducts = array_filter($WCProducts['data'], function($product){
            foreach($product['attributes'] as $attribute){
                if($attribute['name'] === $this->attribute_name) return true;
            }
            return false;
            
        });

        return WCConvert($rectronProducts);
    }

    // Main function to the the data from the onhand feed
    function get_data(){
        if($this->onhand_feed){
            $options = array(
                'http' => array(
                    'timeout' => 20
                )
            );
            $context = stream_context_create($options);
            $data = file_get_contents($this->onhand_feed, false, $context);

            $dirty_data = simplexml_load_string($data)->Value;
            return $this->get_formated_data($dirty_data);
            
        }
    }

    // Important this function must only run after the new feed has been compared to the old feed
    function set_formated_data_obj($onhand_products){
        $data_obj = [];

        foreach($onhand_products as $product){
            $data_obj[$product["Code"]] = $product;
        }

        update_option("rectron_previous_onhand", json_encode($data_obj));
        return $data_obj;
    }

    function get_categories(){
        if($this->categories){
            $options = array(
                'http' => array(
                    'timeout' => 20
                )
            );
            $context = stream_context_create($options);
            $data = file_get_contents($this->categories, false, $context);

            $dirty_data = simplexml_load_string($data)->products;
            $this->xml_categories = $this->get_formated_categories($dirty_data);
            return $this->xml_categories;
        }
    }

    // This function is only callable from inside the class
    private function get_formated_data($dirty_data){
        $formated_array = array();
        $i = 0;
        foreach($dirty_data->ProductDto as $product){
            $json_product = json_encode($product);
            $array_product = json_decode($json_product, true);
            $formated_array[$i] = $array_product;
            $i++;
        }

        return $formated_array;
    }

    // Returns an associative array of products with the sku as the key
    private function get_formated_categories($dirty_data){
        $formated_categories = array();

        foreach($dirty_data->product as $product){
            $json_product = json_encode($product);
            $array_product = json_decode($json_product, true);
            $formated_categories[(string)$product['fullName']] = $array_product;
        }
        return $formated_categories;
    }



    function feed_loop(){
        $products = $this->get_data();
        $wp_categories = convert_existing_categories($this->existing_categories);
        set_time_limit(0);
        ignore_user_abort(true);
        $wp_images = $this->get_wp_images();

        // format($wp_images);
        // Loop over the rectron feed products
        for($i = 0; $i < count($products); $i++){

            // Check if the product has a code and pictures
            if(isset($this->categories_data[$products[$i]["Code"]]) && isset($this->categories_data[$products[$i]["Code"]]['pictures'])){

                // Get the images from the category feed
                $images = $this->categories_data[$products[$i]["Code"]]['pictures']['picture'];
                $imageRegex = "/(\.(jpe?g|png|webp))$/";
                // More than two images
                $image_array = [];
                if(count($images) >= 2){
                    foreach($images as $image){
                        $image_url = preg_replace("/(\/\/)/", "https://", $image['@attributes']['path']);
                        array_push($image_array, $image_url);
                    }
                } else {  // Just one image
                    $image_url = preg_replace("/(\/\/)/", "https://", $images['@attributes']['path']);
                    array_push($image_array, $image_url);
                }
                // $imageRegex = "/(\.(jp(e)?g|png|webp|jfif))$/";
                // There is more than one image
                // if(count($images) >= 2) $images = array_map(function($image){ 
                //     $imageRegex = "/(\.(jpe?g|png|webp))$/";
                //     $image_url = preg_replace("/(\/\/)/", "https://", $image['@attributes']['path']);
                //     // TODO: Important pull images in on separate method for simplicity
                //     /* 

                //         Solution 1: pull images in on separate method and match to product
                //         Solution 2: check if product exists and if it does no need to create the images becuase the product already 
                //                     exists
                //     */
                //     $image_index = preg_replace("/(\.(webp|jp(e)?g|png))$/", "", wp_basename($image_url));
                //     if(isset($wp_images[$image_index])){
                //         return;
                //     }
                //     else if(preg_match($imageRegex, $image_url)){
                //         // $image_id = $this->upload_image($image_url);
                //     } 
                //     if(isset($image_id)) return $image_id;
                // }, $images);
                // There is only one image
                // else {
                //     // Remove the // and add https://
                //     $image_url = preg_replace("/(\/\/)/", "https://", $images['@attributes']['path']);
                //     // Remove to file extension (file extension changes when uploaded to wordpress)
                //     $image_index = preg_replace("/(\.(webp|jp(e)?g|png))$/", "", sanitize_file_name(wp_basename($image_url)));
                //     // format($image_index);
                //     // Check if the image already exists
                //     if(isset($wp_images[$image_index])){
                //         continue;
                //     }
                //     // If the image doesn't exist validate the image and add it.
                //     else if(preg_match($imageRegex, $image_url)){
                //         // $image_id = $this->upload_image($image_url);
                //     } 

                // }

                // Get the categories from the category feed
                $categories = $this->categories_data[$products[$i]["Code"]]['categories']['category'];
                if(count($categories) < 2){
                    $categories = ltrim($categories['@attributes']['path'], "/");
                    $categories = rtrim($categories, "/");
                    $categories = explode("/", $categories);
                   
                } else {
                    $categories_array = [];
                    foreach($categories as $category){
                        $category = ltrim($category['@attributes']['path'], "/");
                        $category = rtrim($category, "/");
                        $category = explode("/", $category);
                        $categories_array = array_merge($categories_array, $category);
                    }
                    $categories = $categories_array;
                }
                // This is the categories that will be added to the product creation API call
                $product_categories = [];
                foreach($categories as $category){
                    $category = $category = preg_replace("/-(?=-)/", "", $category);
                    // Find the category
                    $cat = get_term_by('slug', $category, 'product_cat')->term_id;
                    // Put category in a list of categories for the product
                    array_push($product_categories, $cat);
                }
                

                $product_data = array(
                    'name' => $products[$i]['Title'],
                    'description' => $products[$i]['Description'],
                    'short_description' => $products[$i]['Description'],
                    'sku' => $products[$i]['Code'],
                    'regular_price' => $products[$i]['SellingPrice'],
                    'manage_stock' => true,
                    'stock_quantity' => $products[$i]['OnHand'],
                    'images' => $image_array,
                    'categories' => $product_categories
                );
                $product_id = wc_get_product_id_by_sku( $product_data['sku'] );
                if(empty($product_id)){
                    // There is no product so create one
                    $this->create_product($product_data);
                } 
                else{
                    // The product exists so update it
                    $product = new WC_Product($product_id);
                    format($product->get_attributes()['external_image']->get_options());
                }

            }

        }
    }
    // Loop through the XML feed and get the categories
    function create_categories(){
        $categories_array = array();
        
        foreach($this->categories_data as $i => $category){
            if(isset($category['categories']['category'])){
                $cat = $category['categories']['category'];
                if(count($cat) < 2){
                    // Get the main and sub categories
                    $cats = explode("/", rtrim(ltrim($cat['@attributes']['path'], "/"), "/"));
                    register_category($cats, $categories_array, convert_existing_categories($this->existing_categories), $this->woocommerce);

                } 
                else {
                    foreach($category['categories']['category'] as $ca){
                        $temp = explode("/", ltrim(rtrim($ca['@attributes']['path'], "/"), "/"));
                        register_category($temp, $categories_array, convert_existing_categories($this->existing_categories), $this->woocommerce);
                    }
                }
            }
        }

    }

    function create_images(){
        $images = $this->get_wp_images();
        $product_images = $this->categories_data;
        // format($product_images);
        // if(!isset($product_images['pictures']['picture']['@attributes'])) format("Product with no pictures");
    }

    function upload_image($url) {
        if($url == "") return null;
        // format($url);
        // format(download_url($url));
        $file = array('name' => sanitize_file_name(wp_basename($url)), 'tmp_name' => download_url($url));

        if (is_wp_error($file['tmp_name'])) {
            @unlink($file['tmp_name']);
            var_dump( $file['tmp_name']->get_error_messages( ) );
            var_dump(array("message" => "error with tmp name"));
        } else {
            $attachmentId = media_handle_sideload($file);
                
            if ( is_wp_error($attachmentId) ) {
                @unlink($file['tmp_name']);
                var_dump( $attachmentId->get_error_messages( ) );
                var_dump(array("message" => "error with attachment id"));
                return null;
            }
            return $attachmentId;
        }
        return null;
    }
    

    function get_wp_images(){
        $query_args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1
        );

        $query = new WP_Query($query_args);
        $images = [];
        foreach($query->posts as $image){
            // format(wp_basename($image->guid));
            $index = preg_replace("/(\.(webp|jp(e)?g|png))$/", "", wp_basename($image->guid));
            $images[$index] = wp_basename($image->guid);
        }
        return $images;
    }

    function create_product($product_data, $product_id=0){
        // Create the product object to create or update the product
        $product = new WC_Product($product_id);
        $attribute = new WC_Product_Attribute();
        $attribute->set_id(0);
        $attribute->set_name('external_image');
        $attribute->set_visible(false);
        $attribute->set_options($product_data['images']);

        $product->set_sku($product_data['sku']);
        $product->set_name($product_data['name']);
        $product->set_description($product_data['description']);
        $product->set_short_description($product_data['short_description']);
        $product->set_regular_price($product_data['regular_price']);
        $product->set_manage_stock(true);
        $product->set_stock_quantity($product_data['stock_quantity']);
        $product->set_category_ids($product_data['categories']);
        $product->set_attributes(array($attribute));
        // $product->set_image_id($product_data['images'][0]);
        // $product->set_gallery_image_ids($product_data['images']);

        return $product->save();

    }

    function update_product($store_product, $feed_product){
        echo "Product needs to be updated";
    }


}