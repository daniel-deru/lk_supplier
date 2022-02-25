<?php

// include_once plugin_dir_url("woocommerce") .'/woocommerce.php';
include_once __DIR__ . "/products.php";


class Rectron  {
    private $onhand_feed;
    private $categories = "https://content.storefront7.co.za/stores/za.co.storefront7.rectron/xmlfeed/rectronfeed-637806849145434755.xml";

    function __construct(){
        if(get_option("rectron_previous_onhand")){
            $this->previous_onhand = get_option("rectron_previous_onhand");
        } else {
            add_option("rectron_previous_onhand", "");
            $this->previous_onhand = "";
        }
    }

    function register_feed($feed){
        if($this->verify($feed)){
            $this->onhand_feed = $feed;
        };
    }

    function verify($feed){
        $onhand_pattern = "/https:\/\/rctdatafeed.azurewebsites.net\/xml\/[a-z0-9-]+\/v[0-9]{1,9}\/products\/onhand/i";
        if(preg_match($onhand_pattern, $feed)){
            return true;
        } 
        else {
            echo "Something went wrong";
            return false;
        }
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

    // Returns an array of the products that is indexed
    function get_formated_data($dirty_data){
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
    function get_formated_categories($dirty_data){
        $formated_categories = array();

        foreach($dirty_data->product as $product){
            $json_product = json_encode($product);
            $array_product = json_decode($json_product, true);
            $formated_categories[(string)$product['sku']] = $array_product;
        }
        return $formated_categories;
    }

    function get_store_products(){
        $store = new Store_products();
        $store_products = $store->get_store_products();
        return $store_products;
    }

    function compare_stock_feed(){
        $store = new Store_products();
        $store_products = $store->get_store_products();

        $feed_products = $this->get_data();

        echo "<pre>";
        print_r($store_products);
        echo "</pre>";

        foreach($feed_products as $feed_product){

            $feed_sku = (string)$feed_product["Code"];

            if(!$store_products[$feed_sku]){
                $this->create_product($feed_product);
            } 
            else if($store_products[$feed_sku]){
                $store_product = $store_products[$feed_sku];
                $this->update_product($store_product, $feed_product);
            }
        }
    }

    function create_product($product){
        return;
    }

    function update_product($store_product, $feed_product){
        echo "Product needs to be updated";
    }

    function sync(){
        $this->compare_stock_feed();
    }
}