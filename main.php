<?php
    require __DIR__ . "/woocommerce-api.php";
?>
    <link rel="stylesheet" href="<?php echo dirname(plugin_dir_url(__FILE__), 1) . "/public/css/admin.css"?>">
<?php



// if(isset($_POST['create-product'])){
//     $product = [
//         "name" => 'test',
//         'regular_price' => "5"

//     ];

//     $saveProduct = json_decode($addProduct($product), true);
// }

$fd = "https://rctdatafeed.azurewebsites.net/xml/a325ca80-0eb1-41a9-8c09-afa42d866618/v1/Products/onhand";
$fd2 = "https://content.storefront7.co.za/stores/za.co.storefront7.rectron/xmlfeed/rectronfeed-637806849145434755.xml";
$fd4 = "https://www.syntech.co.za/feeds/feedhandler.php?key=05813947-DD1B-461A-A88E-02DBC278DE74&feed=syntech-xml-full";
// $options = array(
//     'http' => array(
//         'timeout' => 20
//     )
//     );

// $context = stream_context_create($options);

// $data = file_get_contents($fd, false, $context);

// echo "<pre>";
// print_r(simplexml_load_string($data));
// echo "</pre>";

// $curl = curl_init($fd5);
// $headers = array(
//     "Accept: application/xml"
// );

// curl_setopt($curl, CURLOPT_HEADER, $headers);

// $res = curl_exec($curl);
// curl_close($curl);

// echo "<pre>";
// echo $res;
// echo "</pre>";

// function wooCommerceAuth(){

//     if( is_admin() && get_option("WP_Smart_Feeds_activated")){
//         delete_option("WP_Smart_Feeds_activated");

//         $host = "https://" . $_SERVER['HTTP_HOST'];
//         $endpoint = "/wc-auth/v1/authorize";

//         $params = [
//             'app_name' => "WP Smart Feeds",
//             'scope' => "read_write",
//             'user_id' => get_current_user_id(),
//             'return_url' => get_admin_url() .   "admin.php?page=lk_supplier%2Fsettings.php",
//             'callback_url' => "https://d2ab-102-219-41-114.ngrok.io/api/woocommerce/callback"

//         ];

//         $query = http_build_query($params);

//         $url = $host . $endpoint . "?" . $query;

//         echo $url;
//         wp_redirect($url);
//         exit;
//     }
    
// }
// "admin.php?page=lk_supplier%2Fsettings.php"

if(isset($_POST['create-product'])){
    update_option("wp_smart_feeds_consumer_key", $_POST['consumer_key']);
    update_option("wp_smart_feeds_consumer_secret", $_POST['consumer_secret']);

    update_option("wp_smart_feeds_syntech_feed", $_POST['syntech_feed']);
    update_option("wp_smart_feeds_rectron_feed_onhand", $_POST['rectron_onhand']);
    update_option("wp_smart_feeds_rectron_feed_categories", $_POST['rectron_categories']);

    update_option("wp_smart_feeds_base_margin", $_POST['base_margin']);
}

?>

<main id="wp_smart_feed_admin">
    <h1>WP Smart Feeds</h1>
    <form action="" method="post" >

        <div class="form-field">
            <div class="label-container">
                <label for="consumer_key">WooCommerce API Credentials</label>
                <div id="woocommerce-help" class="help">
                    <img src="./public/images/help.png" alt="">
                    <ol>
                        <li>Go to WooCommerce > settings > advanced. </li>
                        <li>find the link that says "REST API" and click it.</li>
                        <li>Click on add key and fill in the required details.</li>
                        <li>Important* Permisions must be set to Read and Write.</li>
                        <li>Click on generate API key.</li>
                        <li>Copy the consumer key and secret to put into the required fields.</li>
                    </ol>
                </div>
            </div>
            <input type="text" name="consumer_key" value="<?php echo get_option("wp_smart_feeds_consumer_key");?>" placeholder="Consumer Key">
            <input type="text" name="consumer_secret" value="<?php echo get_option("wp_smart_feeds_consumer_secret");?>" placeholder="Consumer Secret">
        </div>

        <div class="form-field">
            <div class="label-container">
                <label for="base_margin">Base Margin</label>
            </div>
            <input type="text" name="base_margin" value="<?php echo get_option("wp_smart_feeds_base_margin");?>">
        </div>

        <div class="form-field">
            <div class="label-container">
                <label for="syntech_feed">Syntech Feed</label>
            </div>
            <input type="text" name="syntech_feed" value="<?php echo get_option("wp_smart_feeds_syntech_feed");?>">
        </div>

        <div id="rectron" class="form-field">
            <div class="label-container">
                <label for="rectron">Rectron Feeds</label>
            </div>
            <input type="text" name="rectron_onhand" placeholder="Onhand Feed" value="<?php echo get_option("wp_smart_feeds_rectron_feed_onhand");?>">
            <input type="text" name="rectron_categories" placeholder="Category Feed" value="<?php echo get_option("wp_smart_feeds_rectron_feed_onhand");?>">
        </div>

        <button type="submit" name="create-product">Save Settings</button>
    </form>


</main>