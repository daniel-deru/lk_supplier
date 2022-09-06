<?php

// require_once "woocommerce-api.php";
require_once 'includes/convert.php';
require_once 'includes/print.php';
require_once 'includes/categories.php';
require_once 'includes/product.php';
require_once 'includes/curl.php';

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
        $this->tax_rate = floatval(get_option("smt_smart_feeds_tax_rate"));
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
            // $context = stream_context_create($options);
                // $data = file_get_contents($this->onhand_feed, false, $context);
            $data = curl_get_file_contents($this->onhand_feed);

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

            // $data = file_get_contents($this->categories, false, $context);
            $data = curl_get_file_contents($this->categories);

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
        // Get the current products
        $existing_products = $this->getProducts();
        set_time_limit(0);
        ignore_user_abort(true);

        // Loop over the rectron feed products
        for($i = 0; $i < count($products); $i++){
           
            $has_data = isset($this->categories_data[$products[$i]["Code"]]);
            $has_pictures = isset($this->categories_data[$products[$i]["Code"]]['pictures']);
            $has_categories = isset($this->categories_data[$products[$i]["Code"]]['categories']);

            // Skip this product if there is insufficient data
            if(!$has_data || !$has_categories || ! $has_pictures) continue;
            
            // This will be added to the product data
            $image_array = $this->create_image_array($products[$i]);

            // This will be used to create the list of categories that will be added to the product data
            $categories = $this->get_feed_categories($products[$i]);

            // This will be added to the product data
            $product_categories = $this->create_product_categories($categories);
            

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
                $import_quantity = intval($product_data['stock_quantity']);
                $minimum_required_quantity = intval(get_option('smt_smart_feeds_import_stock'));
                if($import_quantity > $minimum_required_quantity && count($image_array) > 0) $this->create_product($product_data);
            }
            else {
                // The product exists so check if it needs to be updated
                $existing_product = $existing_products[$product_data['sku']];
                
                $this->update_cost($existing_product, $products[$i]);
                $this->update_stock($existing_product, $products[$i]);

                $existing_product->save();
            }

            $rectron_products[$products[$i]['Code']] = $product_data;
        }
        // Set the stock quantity to zero if the product is not in the $rectron_products array
        $this->delete_products($rectron_products, $existing_products);
    }

    function create_image_array($product){

        // Get the images from the category feed
        $images = $this->categories_data[$product["Code"]]['pictures']['picture'];
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

        return $image_array;
    }

    function get_feed_categories($product){

        // Get the categories from the category feed
        $categories = $this->categories_data[$product["Code"]]['categories']['category'];

        if(count($categories) < 2){
            $categories = ltrim($categories['@attributes']['path'], "/");
            $categories = rtrim($categories, "/");
            $categories = explode("/", $categories);
        } 
        else {
            $categories_array = [];
            foreach($categories as $category){
                $category = ltrim($category['@attributes']['path'], "/");
                $category = rtrim($category, "/");
                $category = explode("/", $category);
                $categories_array = array_merge($categories_array, $category);
            }
            $categories = $categories_array;
        }

        return $categories;
    }

    function create_product_categories($categories){
        // Get the category ID
        $product_categories = [];
        foreach($categories as $category){
            $category = $category = preg_replace("/-(?=-)/", "", $category);
            

            // This part is to add the products to the "Laptops and Tablets" category as requested
            if($category == "notebooks-accessories" || $category == "tablets"){
                $alt_cat = get_term_by('slug', "laptops-and-tablets", 'product_cat')->term_id;
                if(isset($alt_cat)) array_push($product_categories, $alt_cat);
            }
            // Find the category
            $cat = get_term_by('slug', $category, 'product_cat');
            // Put category in a list of categories for the product
            if(isset($cat->term_id)) array_push($product_categories, $cat->term_id);
        }

        return $product_categories;
    }

    function update_cost($existing_product, $product){
        // Get the original cost price
        $cost_price = smt_smart_feeds_get_meta_data('original', $existing_product);

        if(!$cost_price) return;

        $cost_price = floatval($cost_price['cost']);        

        // The current cost price is not the same as the cost price from the feed
        if($cost_price != floatval($product['SellingPrice'])){

            $profit = getProfit($cost_price);
            $tax = (floatval($this->tax_rate) + 100) / 100;

            $custom_data = smt_smart_feeds_get_meta_data('custom', $existing_product);
            $other_cost = floatval($custom_data['other_cost']);

            $new_cost = floatval($product['SellingPrice']) + $other_cost;
            $sellingPrice = calcSellingPrice($new_cost, $profit, $tax);

            $existing_product->set_regular_price($sellingPrice);
        }
    }

    function update_product_margin(){
        
    }

    function update_stock($existing_product, $product){

        // Get the current stock quantity
        $stock_quantity = $existing_product->get_stock_quantity();

        // The stock quantity is not the same
        if(intval($stock_quantity) != intval($product['OnHand'])){
            $existing_product->set_stock_quantity($product['OnHand']);
        }

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
    function delete_products($rectron_products, &$existing_products){
        // loop over the existing products
        foreach($existing_products as $existing_product){
            $sku = $existing_product->get_sku();
            $attributes = $existing_product->get_attributes();

            // Check if it is a rectron product
            if(isset($attributes['rectron'])){ // Check if the product is a rectron product
                // Check the product is not in the rectron products array which means the onhand needs to be set to 0
                if(!isset($rectron_products[$sku])){
                    $existing_product->set_stock_quantity("0");
                    $existing_product->save();
                }
            }
        }
    }

    function get_margin($cost){
        $dynamic_margins = json_decode(get_option("smt_smart_feeds_dynamic_rules"));

        // Return the base margin if there are no dynamic margins
        if(count($dynamic_margins) < 1) $margin = $this->base_margin;

        if($dynamic_margins){
            foreach($dynamic_margins as $dynamic_margin){
                $from = intval($dynamic_margin->more_than);
                $to = intval($dynamic_margin->less_than);

                // Check if the cost is between the range of the dynamic rule
                if($cost > $from && $cost < $to){
                    $margin = (intval($dynamic_margin->margin) + 100) / 100;
                } 
            }
        }

        return $margin;
    }

    function create_product($product_data, $product_id=0){

        // Create the product object to create or update the product
        $product = new WC_Product($product_id);

        $product->set_sku($product_data['sku']);
        $product->set_name($product_data['name']);
        $product->set_description($product_data['description']);
        $product->set_short_description($product_data['short_description']);

        // Calculate the price of the product
        $cost = floatval($product_data['regular_price']);

        $margin = $this->get_margin($cost);
        $price_excl = $cost * $margin;
        $price_incl = $price_excl * ($this->tax_rate + 100) / 100;

        $product->set_regular_price($price_incl);
        $product->set_manage_stock(true);
        $product->set_stock_quantity($product_data['stock_quantity']);
        $product->set_category_ids($product_data['categories']);
        $product->set_tax_class("Feed Tax");


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

        $product->set_attributes(array($image_attribute, $rectron_attribute));

        $product->update_meta_data('custom', 
        [
            'skip' => 0, 
            'other_cost' => 0, 
            'margin' => ($this->getProductMargin($price_incl) * 100) - 100,  
            'margin_type' => 'percent'
        ]);

        $product->update_meta_data('original', ['cost' => $product_data['regular_price']]);

        return $product->save();

    }
    
    function getProductMargin($productPrice){
        $dynamic_margins = json_decode(get_option("smt_smart_feeds_dynamic_rules"));
        $base_margin = intval(get_option("smt_smart_feeds_base_margin"));

        $margin = $base_margin;

        if($dynamic_margins){
            foreach($dynamic_margins as $dynamic_margin){
                if($productPrice > $dynamic_margin->more_than && $productPrice < $dynamic_margin->less_than){
                    $margin = $dynamic_margin->margin;
                }
            }
        }

        return ($margin + 100) / 100;
    }
}