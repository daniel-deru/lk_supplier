<?php
require __DIR__ . '/vendor/autoload.php';

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
$host = "https";
else $host = "http";

$host .= "://" . $_SERVER['HTTP_HOST'];

use Automattic\WooCommerce\Client;
$key = get_option('WP_Smart_Feeds_consumer_key');
$secret = get_option('WP_Smart_Feeds_consumer_secret');
$woocommerce = new Client(
    $host, 
    $key, 
    $secret,
    [
        'version' => 'wc/v3',
    ]
);

?>

<?php
 
 $getHeaders = function() use ($woocommerce){
    $lastResponse = $woocommerce->http->getResponse();
    return $lastResponse->getHeaders();
 };

 $listCategories = function ($page=1) use ($woocommerce, $getHeaders){
    $data = array(
        'data' => $woocommerce->get("products/categories", array(
            'per_page' => 100,
            'page' => $page)),
        'headers' => $getHeaders());

    $data['headers'] = $getHeaders();

    return json_encode($data);
 };

 $listProducts = function ($page=1) use ($woocommerce, $getHeaders){
    $data = array(
        'data'=>$woocommerce->get("products", array(
            "per_page" => 90,
            "page" => $page)),
        'headers' => $getHeaders());

    
    return json_encode($data);
 };

$addProduct = function($data) use ($woocommerce){

    if($data['name']){
        $request = $woocommerce->post('products', $data);

        return json_encode(($request));
    }

};

$getProduct = function($id) use ($woocommerce){
    if($id){
        $data = $woocommerce->get('products/' . $id);
        return json_encode($data);
    }
};

$updateProduct = function($id, $data) use ($woocommerce){
    if($id && $data){
        $data = $woocommerce->put('products/' . $id, $data);
        return json_encode($data);
    }
};

$units = function() use ($woocommerce){
    $data = $woocommerce->get("settings/products");
    return json_encode($data);
};

$createCategory = function($data) use ($woocommerce) {
    $data = $woocommerce->post('products/categories', $data);
    return json_encode($data);
};

$getTaxClasses = function() use ($woocommerce) {
    try {
        $data = $woocommerce->get("taxes/classes");
        return json_encode($data);
    } catch (Exception $e){
        return json_encode(array('error' => true, 'message' => $e->getMessage()));
    }
};

$getShippingClasses = function() use ($woocommerce){
    try {
        $data = $woocommerce->get("products/shipping_classes");
        return json_encode($data);
    } catch (Exception $e) {
        return json_encode(array('error' => true, 'message' => $e->getMessage()));
    }
};

 ?>


