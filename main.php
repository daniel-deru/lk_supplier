<?php
    require __DIR__ . "/woocommerce-api.php";
    require __DIR__ . "/syntech.php";
    include_once __DIR__ . "/rectron.php";
    include_once plugin_dir_url("woocommerce") .'/woocommerce.php';
?>
    <link rel="stylesheet" href="<?php echo dirname(plugin_dir_url(__FILE__), 1) . "/public/css/admin.css"?>">
<?php



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
    update_option("wp_smart_feeds_interval", $_POST['interval']);



}

?>

<?php
// Important classes WC_Product, WC_Product_Query

// function upload_image($url, $post_id) {
//     $image = "";
//     if($url != "") {
     
//         $file = array();
//         $file['name'] = $url;
//         $file['tmp_name'] = download_url($url);
 
//         if (is_wp_error($file['tmp_name'])) {
//             @unlink($file['tmp_name']);
//             var_dump( $file['tmp_name']->get_error_messages( ) );
//         } else {
//             $attachmentId = media_handle_sideload($file, $post_id);
             
//             if ( is_wp_error($attachmentId) ) {
//                 @unlink($file['tmp_name']);
//                 var_dump( $attachmentId->get_error_messages( ) );
//             } else {                
//                 $image = wp_get_attachment_url( $attachmentId );
//             }
//         }
//     }
//     return $image;
// }

if(isset($_POST['refresh'])){
    $rectronURL = get_option("wp_smart_feeds_rectron_feed_onhand");
    $rectronFeed = new Rectron();
    $rectronFeed->register_feed($rectronURL);

    echo "<pre>";
    print_r($rectronFeed->get_data());
    // print_r($rectronFeed->verify($rectronURL));
    echo "</pre>";

    // $woo = new WC_Product();
    // $woo->set_name("Test Product");
    // $woo->save();

    // $products = new WC_Product_Query();
    // echo "<pre>";
    // print_r($products->get_products()[0]->get_data());
    // echo "</pre>";
}
?>

<main id="wp_smart_feed_admin">
    <h1>WP Smart Feeds</h1>
    <form action="" method="post" >

        <div class="form-field">
            <div class="label-container">
                <label for="consumer_key">WooCommerce API Credentials</label>
                <div id="woocommerce-help" class="help">
                    <img src="<?php echo dirname(plugin_dir_url(__FILE__))?>/lk_supplier/public/images/help.png" alt="">
                    <ol class="info">
                        <li>1. Go to WooCommerce > settings > advanced. </li>
                        <li>2. find the link that says "REST API" and click it.</li>
                        <li>3. Click on add key and fill in the required details.</li>
                        <li>4. Important* Permisions must be set to Read and Write.</li>
                        <li>5. Click on generate API key.</li>
                        <li>6. Copy the consumer key and secret to put into the required fields.</li>
                    </ol>
                </div>
            </div>
            <input type="text" name="consumer_key" value="<?php echo get_option("wp_smart_feeds_consumer_key");?>" placeholder="Consumer Key">
            <input type="text" name="consumer_secret" value="<?php echo get_option("wp_smart_feeds_consumer_secret");?>" placeholder="Consumer Secret">
        </div>

        <div class="form-field">
            <div class="label-container">
                <label for="base_margin">Base Margin (%)</label>
            </div>
            <input type="text" name="base_margin" value="<?php echo get_option("wp_smart_feeds_base_margin");?>" placeholder="Example: 76">
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
        <div  class="form-field">

            <div class="label-container">
                <label>Request Interval</label>
                <input id="interval" type="hidden" value="<?php echo get_option("wp_smart_feeds_interval");?>">
            </div>

            <div>

                <div class="radio-input">
                    <div>
                        <input id="weekly" type="radio" name="interval" value="weekly">
                        <label for="">Weekly</label>
                    </div>

                    <div>
                        <input id="daily" type="radio" name="interval" value="daily">
                        <label for="">Daily</label>
                    </div>

                    <div>
                        <input id="monthly" type="radio" name="interval" value="monthly">
                        <label for="">Monthly</label>
                    </div>                  
                </div>

            </div>

        </div>

        <div>
            <button type="submit" name="create-product">Save Settings</button>
            <button type="submit" name="refresh">Sync Now</button>
        </div>
        
    </form>


</main>