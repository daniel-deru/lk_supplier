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
    7. Price
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

    function __construct($existing_categories, $woocommerce){
        $this->existing_categories = $existing_categories;
        $this->woocommerce = $woocommerce;
        $this->register_feed();
        // This categories data is the data from the feed NOT the wordpress categories
        $this->categories_data = $this->get_categories();
        $this->create_categories();
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



    function create_product($product){
        $products = $this->get_data();
        $wp_categories = convert_existing_categories($this->existing_categories);
        // Loop over the rectron feed products
        for($i = 0; $i < count($products); $i++){

            // Check if the product has a code and pictures
            if(isset($this->categories_data[$products[$i]["Code"]]) && isset($this->categories_data[$products[$i]["Code"]]['pictures'])){

                // Get the images from the category feed
                $images = $this->categories_data[$products[$i]["Code"]]['pictures']['picture'];
                if(count($images) >= 2) $images = array_map(function($image){ return preg_replace("/(\/\/)/", "", $image['@attributes']['path']); }, $images);
                else $images = preg_replace("/(\/\/)/", "", $images['@attributes']['path']);

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
                    array_push($product_categories, $wp_categories[$category]["id"]);
                }
                
                format($products[$i]);
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

    function update_product($store_product, $feed_product){
        echo "Product needs to be updated";
    }


}