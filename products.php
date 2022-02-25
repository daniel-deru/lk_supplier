<?php

// include_once __DIR__ . "/syntech.php";
// include_once __DIR__ . "/rectron.php";
include_once plugin_dir_url("woocommerce") .'/woocommerce.php';

    // $woo = new WC_Product();
    // $woo->set_name("Test Product");
    // $woo->save();

    // $products = new WC_Product_Query();
    // echo "<pre>";
    // print_r($products->get_products()[0]->get_data());
    // echo "</pre>";

    // Steps to create/update a product
    /*
        1. Get any existing products from the store.
        2. Compare the skus to see if the products in the store match the product in the feed.
        3. If the product exists: check to see if the data in that product matches the data from the feed
            3.1 If the data matches skip update
            3.2 If some of the data is different: update the data
        4. If the product doesn't exist create the new product (if the feed has more products that the store)
            4.1 Add the image to the media library and get the id to set the image id of the product
            4.2 check if the category for the product exists and set it to that categories
            4.3 if the category doesn't exist create the categorie and set the category
        5. If the product is in the store but not in the feed set the stock onhand to 0
    
    */

class Store_Products {
    public $store_products;

    public function __construct(){
        $this->store_products = array();
    }

    // Returns an associative array of the store products with the sku as the key,
    // If there is no sku the product is not returned
    public function get_store_products(){
        $query = new WC_Product_Query();
        $products = $query->get_products();
        $i = 0;

        foreach($products as $product){
            $product_data = $product->get_data();
            if($product_data['sku']){
                $this->store_products[$product_data['sku']] = $product_data;
            }
            $i++;
        }

        return $this->store_products;
        
    }

    function upload_image($url) {
        if($url != "") {
            
            $file = array(
                'name' => $url,
                'tmp_name' => download_url($url)
            );
     
            if (is_wp_error($file['tmp_name'])) {
                @unlink($file['tmp_name']);
                var_dump( $file['tmp_name']->get_error_messages( ) );
            } else {
                $attachmentId = media_handle_sideload($file);
                 
                if ( is_wp_error($attachmentId) ) {
                    @unlink($file['tmp_name']);
                    var_dump( $attachmentId->get_error_messages( ) );
                }
            }
        }
        return $attachmentId;
    }
}