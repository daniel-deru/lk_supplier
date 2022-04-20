<?php
require 'vendor/autoload.php';
require "includes/link.php";

use Automattic\WooCommerce\Client;

$key = get_option('WP_Smart_Feeds_consumer_key');
$secret = get_option('WP_Smart_Feeds_consumer_secret');

$woocommerce = new Client(
    getHost(), 
    $key, 
    $secret,
    [
        'version' => 'wc/v3',
    ]
);


 
 $smt_smart_feeds_getHeaders = function() use ($woocommerce){
    $lastResponse = $woocommerce->http->getResponse();
    return $lastResponse->getHeaders();
 };

 $smt_smart_feeds_listCategories = function ($page=1) use ($woocommerce, $smt_smart_feeds_getHeaders){
    $data = array(
        'data' => $woocommerce->get("products/categories", array(
            'per_page' => 100,
            'page' => $page)),
        'headers' => $smt_smart_feeds_getHeaders());

    $data['headers'] = $smt_smart_feeds_getHeaders();

    return json_encode($data);
 };

 $smt_smart_feeds_listProducts = function ($page=1) use ($woocommerce, $smt_smart_feeds_getHeaders){
    $data = array(
        'data'=>$woocommerce->get("products", array(
            "per_page" => 90,
            "page" => $page)),
        'headers' => $smt_smart_feeds_getHeaders());

    
    return json_encode($data);
 };

$smt_smart_feeds_addProduct = function($data) use ($woocommerce){

    if($data['name']){
        $request = $woocommerce->post('products', $data);

        return json_encode(($request));
    }

};

$smt_smart_feeds_getProduct = function($id) use ($woocommerce){
    if($id){
        $data = $woocommerce->get('products/' . $id);
        return json_encode($data);
    }
};

$smt_smart_feeds_updateProduct = function($id, $data) use ($woocommerce){
    if($id && $data){
        $data = $woocommerce->put('products/' . $id, $data);
        return json_encode($data);
    }
};

$smt_smart_feeds_units = function() use ($woocommerce){
    $data = $woocommerce->get("settings/products");
    return json_encode($data);
};

$smt_smart_feeds_createCategory = function($data) use ($woocommerce) {
    $data = $woocommerce->post('products/categories', $data);
    return json_encode($data);
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

 ?>


