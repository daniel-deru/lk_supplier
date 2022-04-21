<?php
    include_once  dirname(plugin_dir_path(__FILE__)) . "/woocommerce-api.php";
    require dirname(plugin_dir_path(__FILE__)) . "/includes/print.php";
    include dirname(plugin_dir_path(__FILE__)) . "/includes/link.php";
    require dirname(plugin_dir_path(__FILE__)) . "/rectron.php";

    format($getHost());

if(isset($_POST['create-product'])){
    if(isset($_POST['consumer_key'])){
        $consumer_key = sanitize_key($_POST['consumer_key']);
        $consumerKeyRegex = "/ck_[0-9a-f]{40}/i";
        if(preg_match($consumerKeyRegex, $consumer_key)) update_option("smt_smart_feeds_consumer_key", $_POST['consumer_key']);
    }

    if(isset($_POST['consumer_secret'])){
        $consumer_secret = sanitize_key($_POST['consumer_secret']);
        $consumerSecretRegex = "/cs_[0-9a-f]{40}/i";
        if(preg_match($consumerSecretRegex, $consumer_secret)) update_option("smt_smart_feeds_consumer_secret", $_POST['consumer_secret']);
    }

    if(isset($_POST['rectron_onhand'])){
        $rectron_onhand = sanitize_url($_POST['rectron_onhand']);
        $rectronOnhandRegex = "/^https:\/\/rctdatafeed.azurewebsites.net\/.*/";
        if(preg_match($rectronOnhandRegex, $rectron_onhand)) update_option("smt_smart_feeds_rectron_feed_onhand", $_POST['rectron_onhand']);

    }

    if(isset($_POST['base_margin'])){
        $base_margin = sanitize_text_field($_POST['base_margin']);
        $marginRegex = "/[0-9]{1,3}/";
        if(preg_match($marginRegex, $base_margin)) update_option("smt_smart_feeds_base_margin", $_POST['base_margin']);
    }

    if(isset($_POST['interval'])){
        $interval = sanitize_text_field($_POST['interval']);
        $intervalRegex = "/(daily)|(weekly)|(hourly)/";
        if(preg_match($intervalRegex, $interval)) update_option("smt_smart_feeds_interval", $_POST['interval']);
    }

}

// $smt_smart_feeds_updateProduct(18, array('attributes' => array(array('name' => 'rectron', 'options' => array()))));
// $smt_smart_feeds_updateProduct(19, array('attributes' => array(array('name' => 'rectron', 'options' => array()))));
// $smt_smart_feeds_updateProduct(12, array('attributes' => array(array('name' => 'rectron', 'options' => array()))));

$rectron = new Rectron();
format($rectron->getWCProducts($woocommerce));

?>
<main id="wp_smart_feed_admin">
    <h1>WP Smart Feeds Settings</h1>
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
            <input type="text" name="consumer_key" value="<?php echo get_option("smt_smart_feeds_consumer_key");?>" placeholder="Consumer Key">
            <input type="text" name="consumer_secret" value="<?php echo get_option("smt_smart_feeds_consumer_secret");?>" placeholder="Consumer Secret">
        </div>

        <div class="form-field">
            <div class="label-container">
                <label for="base_margin">Global Base Margin (%)</label>
            </div>
            <input type="text" name="base_margin" value="<?php echo get_option("smt_smart_feeds_base_margin");?>" placeholder="Example: 76">
        </div>

        <div id="rectron" class="form-field">
            <div class="label-container">
                <label for="rectron">Rectron Feed (onhand)</label>
            </div>
            <input type="text" name="rectron_onhand" placeholder="Onhand Feed" value="<?php echo get_option("smt_smart_feeds_rectron_feed_onhand");?>">
        </div>
        <div  class="form-field">

            <div class="label-container">
                <label>Request Interval</label>
                <input id="interval" type="hidden" value="<?php echo get_option("smt_smart_feeds_interval");?>">
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
                        <input id="hourly" type="radio" name="interval" value="hourly">
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