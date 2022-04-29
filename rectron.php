<?php

require_once "woocommerce-api.php";
require_once 'includes/convert.php';
require_once 'includes/print.php';


class Rectron  {
    private $onhand_feed;
    private $categories = "https://content.storefront7.co.za/stores/za.co.storefront7.rectron/xmlfeed/rectronfeed-637806849145434755.xml";
    private $attribute_name = "rectron";
    public $categories_data = null;
    // private $woocommerce;

    function __construct(){
        $this->register_feed();
        $this->categories_data = $this->get_categories();
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
            format($array_product['@attributes']["sku"]);
            $formated_categories[(string)$product['sku']] = $array_product;
        }
        return $formated_categories;
    }



    function create_product($product){
        $products = $this->get_data();
        $total = 0;
        $available = 0;
        // for($i = 0; $i < count($products); $i++){
            // $images = $this->categories_data[$products[$i]["Code"]]['pictures']['picture'];
            // $images = array_map(function($image){ return preg_replace("/(\/\/)/", "", $image['@attributes']['path']); }, $images);
            // echo "<br>" . $i . "<br>";
            // if(isset($this->categories_data[$products[$i]["Code"]])) format($this->categories_data[$products[$i]["Code"]]['@attributes']['sku']);
            // else if(!isset($this->categories_data[$products[$i]["Code"]])) format($this->categories_data[$products[$i]["Code"] . "-NA"]['@attributes']['sku']);
            // $total++;
            // if(isset($this->categories_data[$products[$i]["Code"]])) $available++;
            // if(isset($this->categories_data[$products[$i]["Code"] . "-NA"])) $available++;
             // // UpcBarcode
            // break;
        // }
        // echo $available . "/" . $total;
        // format($products[1]);
        // format($products[3]);
        // format($products[6]);
        // format($products[10]);
        // 383/906
    }

    function update_product($store_product, $feed_product){
        echo "Product needs to be updated";
    }

}