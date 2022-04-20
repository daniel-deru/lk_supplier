<?php
    include_once  dirname(plugin_dir_path(__FILE__)) . "/woocommerce-api.php";
    include_once  dirname(plugin_dir_path(__FILE__)) . "/syntech.php";
    include_once  dirname(plugin_dir_path(__FILE__)) . "/rectron.php";

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


// if(isset($_POST['refresh'])){
//     if(get_option("wp_smart_feeds_rectron_feed_onhand")){
//         $rectron = new Rectron(get_option("wp_smart_feeds_rectron_feed_onhand"));
//         // $products = json_decode($listProducts(), true);

//     }
// }
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

        <div id="rectron" class="form-field">
            <div class="label-container">
                <label for="rectron">Rectron Feed (onhand)</label>
            </div>
            <input type="text" name="rectron_onhand" placeholder="Onhand Feed" value="<?php echo get_option("wp_smart_feeds_rectron_feed_onhand");?>">
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