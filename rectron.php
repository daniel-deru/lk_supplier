<?php

include_once plugin_dir_url("woocommerce") .'/woocommerce.php';


class Rectron {
    private $onhand_feed;
    private $categories = "https://content.storefront7.co.za/stores/za.co.storefront7.rectron/xmlfeed/rectronfeed-637806849145434755.xml";

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
            $this->xml_onhand =  $this->get_formated_data($dirty_data);
            return $this->xml_onhand;
        }
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

    function get_formated_categories($dirty_data){
        $formated_categories = array();

        foreach($dirty_data->product as $product){
            $json_product = json_encode($product);
            $array_product = json_decode($json_product, true);
            $formated_categories[(string)$product['sku']] = $array_product;
        }
        return $formated_categories;
    }
}