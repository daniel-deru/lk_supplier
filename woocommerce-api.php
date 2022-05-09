<?php
require 'vendor/autoload.php';
require "includes/link.php";

use Automattic\WooCommerce\Client;

$key = get_option('smt_smart_feeds_consumer_key');
$secret = get_option('smt_smart_feeds_consumer_secret');



$woocommerce = new Client(
    $getHost(), 
    $key, 
    $secret,
    [
        'version' => 'wc/v3',
    ]
);


 
function smt_smart_feeds_getHeaders($woocommerce){
    $lastResponse = $woocommerce->http->getResponse();
    return $lastResponse->getHeaders();
 };

 $smt_smart_feeds_listCategories = function ($page=1) use ($woocommerce){
    $data = array(
        'data' => $woocommerce->get("products/categories", array(
            'per_page' => 100,
            'page' => $page)),
        'headers' => smt_smart_feeds_getHeaders($woocommerce));

    $data['headers'] = smt_smart_feeds_getHeaders($woocommerce);

    return json_encode($data);
 };

 function smt_smart_feeds_listProducts($page=1, $woocommerce) {
    $data = array(
        'data'=>$woocommerce->get("products", array(
            "per_page" => 90,
            "page" => $page)),
        'headers' => smt_smart_feeds_getHeaders($woocommerce));

    
    return json_encode($data);
 };

function smt_smart_feeds_addProduct($data, $woocommerce){

    if($data['name']){
        $request = $woocommerce->post('products', $data);

        return json_encode(($request));
    }

};

// Update products in batches
function smt_smart_feeds_batch($data, $woocommerce){
    try{
        return json_encode($woocommerce->post('products/batch', $data));
    } catch (Exception $e){
        return json_encode(array('message' => $e->getMessage()));
    }
}

$smt_smart_feeds_getProduct = function($id) use ($woocommerce){
    if($id){
        $data = $woocommerce->get('products/' . $id);
        return json_encode($data);
    }
};

$smt_smart_feeds_updateProduct = function($id, $data) use ($woocommerce){
    if($id && $data){
        try {
            $response = $woocommerce->put('products/' . $id, $data);
            return json_encode($response);
        } catch( Exception $e){
            return json_encode(array('error' => true, 'message' => $e->getMessage()));
        }
        
    }
};

$smt_smart_feeds_units = function() use ($woocommerce){
    $data = $woocommerce->get("settings/products");
    return json_encode($data);
};

// $smt_smart_feeds_listCategories = function() use ($woocommerce) {
//     $categories = $woocommerce->get("products/categories");
//     return json_encode($categories);
// };

function smt_smart_feeds_createCategory($data, $woocommerce) {
    try{
        $data = $woocommerce->post('products/categories', $data);
        return json_encode($data);
    } catch (Exception $e) {
        return json_encode(array('error' => true, 'message' => $e->getMessage()));
    }

};

$smt_smart_feeds_getTaxClasses = function() use ($woocommerce) {
    try {
        $data = $woocommerce->get("taxes/classes");
        return json_encode($data);
    } catch (Exception $e){
        return json_encode(array('error' => true, 'message' => $e->getMessage()));
    }
};

$smt_smart_feeds_getShippingClasses = function() use ($woocommerce){
    try {
        $data = $woocommerce->get("products/shipping_classes");
        return json_encode($data);
    } catch (Exception $e) {
        return json_encode(array('error' => true, 'message' => $e->getMessage()));
    }
};

$smt_smart_feeds_createProductAttribute = function($attribute) use ($woocommerce) {
    try {
        $response = $woocommerce->post("products/attributes", $attribute);
        return json_encode($response);
    } catch (Exception $e){
        return json_encode(array('error' => true, 'message' => $e->getMessage()));
    }
};

$smt_smart_feeds_getProductAttribute = function($name) use ($woocommerce) {
    try {
        $productAttributes = $woocommerce->get("products/attributes");
        foreach($productAttributes as $attribute){
            if($attribute['name'] === $name) return json_encode($attribute);
        }
        return json_encode(array('message' => 'no attribute by that name'));
    } catch (Exception $e){
        return json_encode(array('error' => true, 'message' => $e->getMessage()));
    }
};

 ?>


