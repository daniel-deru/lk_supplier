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
    function __construct(){
        // Get the feed URL from WP DB
        $this->register_feed();
        // This categories data is the data from the feed NOT the wordpress categories
        $this->categories_data = $this->get_categories();
        $this->tax_rate = intval(get_option("smt_smart_feeds_tax_rate"));
        $this->create_categories();
        $this->base_margin = $this->set_base_margin();
    }

    // Called in the constructor to get the feed url
    function register_feed(){
        $feed = get_option("smt_smart_feeds_rectron_feed_onhand");

        if(!$feed) return;

        if($this->verify($feed)) $this->onhand_feed = $feed;
    }
    // Set the base margin from the wordpress options db
    function set_base_margin(){
        $base_margin = intval(get_option("smt_smart_feeds_base_margin"));

        if(isset($base_margin)) $base_margin = ($base_margin + 100) / 100;
        else $base_margin = 1;

        return $base_margin;
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

    function getProducts(){
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
        return $products_array;
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
        // Get the latest data from the onhand feed
        $products = $this->get_data();

        $rectron_products = [];
        $existing_products = $this->getProducts();
        set_time_limit(0);
        ignore_user_abort(true);

        // Loop over the rectron feed products
        for($i = 0; $i < count($products); $i++){

            // Check if the product has a code and pictures
            if(isset($this->categories_data[$products[$i]["Code"]]) && isset($this->categories_data[$products[$i]["Code"]]['pictures'])){

                // Get the images from the category feed
                $images = $this->categories_data[$products[$i]["Code"]]['pictures']['picture'];
                $image_array = [];

                // More than two images
                if(count($images) >= 2){
                    foreach($images as $image){
                        $image_url = preg_replace("/(\/\/)/", "https://", $image['@attributes']['path']);
                        array_push($image_array, $image_url);
                    }
                } else {  // Just one image
                    $image_url = preg_replace("/(\/\/)/", "https://", $images['@attributes']['path']);
                    array_push($image_array, $image_url);
                }

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
                // Get the category ID
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
                else {
                    // The product exists so update it
                    $existing_product = $existing_products[$product_data['sku']];

                    $regular_price = $existing_product->get_regular_price();
                    $stock_quantity = $existing_product->get_stock_quantity();

                    if($regular_price != $products[$i]['SellingPrice']) $existing_product->set_regular_price($products[$i]['SellingPrice']);
                    if($stock_quantity != $products[$i]['OnHand']) $existing_product->set_stock_quantity($products[$i]['OnHand']);
                }

                $rectron_products[$products[$i]['Code']] = $product_data;

            }

        }
        // Set the stock quantity to zero if the product is not in the $rectron_products array
        $this->delete_products($rectron_products, $existing_products);
    }

    function get_wp_categories(){
        // Array for the cleaned categories after they have been converted from WP_Term Object
        $categories_array = [];
        $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
        foreach($categories as $category){
            array_push($categories_array, $category->to_array());
        }

        return $categories_array;
    }
    // Loop through the XML feed and get the categories
    function create_categories(){
        // Array for unique values to keep track of which categories have been added
        $categories_array = array();
        // TODO convert to category name as the key
        $existing_categories = convert_existing_categories($this->get_wp_categories());

        foreach($this->categories_data as $i => $category){
            if(isset($category['categories']['category'])){
                $cat = $category['categories']['category'];
                if(count($cat) < 2){
                    // Get the main and sub categories
                    $cats = explode("/", rtrim(ltrim($cat['@attributes']['path'], "/"), "/"));
                    register_category($cats, $categories_array, $existing_categories);

                } 
                else {
                    // This if for products with more than one category tree
                    foreach($category['categories']['category'] as $ca){
                        $temp = explode("/", ltrim(rtrim($ca['@attributes']['path'], "/"), "/"));
                        register_category($temp, $categories_array, $existing_categories);
                    }
                }
            }
        }

    }
    // Set the stock quantity to 0 if the product is no longer onhand
    function delete_products($rectron_products, $existing_products){
        foreach($existing_products as $existing_product){
            $sku = $existing_product->get_sku();
            $attributes = $existing_product->get_attributes();

            if(isset($attributes['rectron'])){ // Check if the product is a rectron product
                // Check the product is not in the rectron products array which means the onhand needs to be set to 0
                if(!isset($rectron_products[$sku])){
                    $existing_product->set_stock_quantity(0);
                }
            }
        }
    }

    function create_product($product_data, $product_id=0){
        // Create the product object to create or update the product
        $product = new WC_Product($product_id);
        // Set the image urls as data attributes
        $image_attribute = new WC_Product_Attribute();
        $image_attribute->set_id(0);
        $image_attribute->set_name('external_image');
        $image_attribute->set_visible(false);
        $image_attribute->set_options($product_data['images']);

        // Set the rectron data attribute to identify unique rectron products
        $rectron_attribute = new WC_Product_Attribute();
        $rectron_attribute->set_id(0);
        $rectron_attribute->set_name('rectron');
        $rectron_attribute->set_visible(false);

        $product->set_sku($product_data['sku']);
        $product->set_name($product_data['name']);
        $product->set_description($product_data['description']);
        $product->set_short_description($product_data['short_description']);
        $price_excl = intval($product_data['regular_price']) * $this->base_margin;
        $price_incl = $price_excl * ($this->tax_rate + 100) / 100;
        $product->set_regular_price($price_incl);

        $product->set_manage_stock(true);
        $product->set_stock_quantity($product_data['stock_quantity']);
        $product->set_category_ids($product_data['categories']);
        $product->set_tax_class("Feed Tax");
        $product->set_attributes(array($image_attribute, $rectron_attribute));

        return $product->save();

    }
}