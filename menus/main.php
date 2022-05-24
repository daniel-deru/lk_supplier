<?php
    require_once  dirname(plugin_dir_path(__FILE__)) . "/woocommerce-api.php";
    require_once dirname(plugin_dir_path(__FILE__)) . "/includes/print.php";
    include dirname(plugin_dir_path(__FILE__)) . "/includes/link.php";
    include_once dirname(plugin_dir_path(__FILE__)) . "/includes/categories.php";
    include_once dirname(plugin_dir_path(__FILE__)) . "/includes/tax_classes.php";
    require_once dirname(plugin_dir_path(__FILE__)) . "/rectron.php";

    $link = sanitize_url($getHost());

if(isset($_POST['save'])){
    if(isset($_POST['consumer_key'])){
        $consumer_key = sanitize_key($_POST['consumer_key']);
        $consumerKeyRegex = "/ck_[0-9a-f]{40}/i";
        // if(preg_match($consumerKeyRegex, $consumer_key)) update_option("smt_smart_feeds_consumer_key", $_POST['consumer_key']);
    }

    if(isset($_POST['consumer_secret'])){
        $consumer_secret = sanitize_key($_POST['consumer_secret']);
        $consumerSecretRegex = "/cs_[0-9a-f]{40}/i";
        // if(preg_match($consumerSecretRegex, $consumer_secret)) update_option("smt_smart_feeds_consumer_secret", $_POST['consumer_secret']);
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

    if(isset($_POST['tax_rate'])){
        $tax_rate = sanitize_text_field($_POST['tax_rate']);
        $tax_rate_regex = "/[0-9]*/";
        $current_tax = get_option("smt_smart_feeds_tax_rate");
    
        // if($current_tax != $tax_rate){
            $tax_class = new TaxClass(get_option("smt_smart_feeds_tax_rate"));
        // }
        if(preg_match($tax_rate_regex, $tax_rate)) update_option("smt_smart_feeds_tax_rate", $tax_rate);
        // Create or update the tax class settings
        
    }

    if(isset($_POST['import_stock'])){
        $import_stock = sanitize_text_field($_POST['import_stock']);
        $import_regex = "/[0-9]*/";
        if(preg_match($import_regex, $import_stock)) update_option("smt_smart_feeds_import_stock", $import_stock);
    }


    wp_localize_script("smt_lk_supplier_admin_script", "smart_feed_data", array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'dynamic_rules' => get_option('smt_smart_feeds_dynamic_rules')
    ));

    $rectron = new Rectron();
    $rectron->feed_loop();

}

if(isset($_POST['refresh'])){
    $rectron = new Rectron();
    $rectron->feed_loop();
}


?>
<main id="wp_smart_feed_admin">
    <h1>Smart Feeds Settings</h1>
    <div id="main-container">
        <form action="" method="post" >

            <!-- <div class="form-field">
                <label for="consumer_key">WooCommerce API Consumer Key</label>
                <input type="text" name="consumer_key" value="<?php //echo get_option("smt_smart_feeds_consumer_key");?>" placeholder="Consumer Key">
            </div> -->

            <!-- <div class="form-field">
                <label for="consumer_secret">WooCommerce API Consumer Secret</label>
                <input type="text" name="consumer_secret" value="<?php // echo get_option("smt_smart_feeds_consumer_secret");?>" placeholder="Consumer Secret">
                <div id="woocommerce-help-container"> -->
                    <!-- <a href="<?php //echo esc_url($link . "/wp-admin/admin.php?page=wc-settings&tab=advanced&section=keys") ?>" target="_blank">Create a Key</a> -->
                        <!-- This div is hidden -->
                    <!-- <div id="woocommerce-help" class="help"> -->
                        <!-- <img src="<?php //echo dirname(plugin_dir_url(__FILE__))?>/lk_supplier/public/images/help.png" alt=""> -->
                        <!-- <i class="fa fa-question-circle" aria-hidden="true"></i>
                        <ol class="info">
                            <li> Click on add key and fill in the required details.</li>
                            <li> Important* Permisions must be set to Read and Write.</li>
                            <li> Click on generate API key.</li>
                            <li> Copy the consumer key and consumer secret to put into the required fields.</li>
                        </ol> -->
                    <!-- </div> -->
                <!-- End of hidden div -->
                <!-- </div>
            </div> -->
            <!-- Rectron Feed Onhand -->
            <div id="rectron" class="form-field">
                <div class="label-container">
                    <label for="rectron">Rectron Feed (onhand)</label>
                </div>
                <input type="text" name="rectron_onhand" placeholder="Onhand Feed" value="<?php echo get_option("smt_smart_feeds_rectron_feed_onhand");?>">
            </div>

            <div class="form-field">
                <label for="feed_interval">Sync Interval</label>
                <select name="feed_interval" id="feed_interal">
                    <option value="10-min">Every 10 Minutes</option>
                    <option value="hourly">Hourly</option>
                    <option value="daily">Daily</option>
                </select>
            </div>

            <!-- Base Margin -->
            <div class="form-field">
                <div class="label-container">
                    <label for="base_margin">Base Margin (%)</label>
                </div>
                <input type="text" name="base_margin" value="<?php echo get_option("smt_smart_feeds_base_margin");?>" placeholder="Example: 76">
            </div>
            
            <!-- Dynamic Margin -->
            <div class="form-field">
                <div id="add-dynamic-rules">
                    <label for="dynamic_rules">Dynamic Margin</label>
                    <button type="button" id="add-rule">Add</button>
                </div>
            </div>

            <!-- Tax Rate -->
            <div class="form-field">
                <div class="label-container">
                    <label for="tax_rate">Tax Rate (%)</label>
                </div>
                <input type="text" name="tax_rate" value="<?php echo get_option("smt_smart_feeds_tax_rate"); ?>" placeholder="Example: 76">
            </div>

            <!-- Import Rule -->
            <div class="form-field">
                <div class="label-container">
                    <label for="round_cents">Don't import product if the stock is less than:</label>
                </div>
                <input type="text" name="import_stock" value="<?php echo get_option("smt_smart_feeds_import_stock"); ?>">
            </div>


            <!-- Save buttons  -->
            <div id="button-container">
                <button type="submit" name="save" id="settings-save-btn">Save Settings</button>
                <button type="submit" name="refresh">Sync Now</button>
            </div>

            </form>
            <section id="dynamic-rule-section">
                <h2>Dynamic Margin</h2>
                <!-- Show the dynamic rules -->
                <div id="dynamic-rules-display">
                </div>
                <div id="ruleset-save-container">
                    <button type="button" id="ruleset-save-btn">Save Rules</button>
                </div>
        </section>
    </div>
    
</main>