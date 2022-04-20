<?php
    include_once  dirname(plugin_dir_path(__FILE__)) . "/woocommerce-api.php";
    include_once  dirname(plugin_dir_path(__FILE__)) . "/syntech.php";
    include_once  dirname(plugin_dir_path(__FILE__)) . "/rectron.php";

?>
<main>
    <h1>Edit Products</h1>
    <?php if(!(get_option("smt_smart_feeds_consumer_key") && get_option("smt_smart_feeds_consumer_secret"))): ?>
        <h2>Please enter the WooCommerce API keys to continue</h2>
    <?php endif ?>
    <section id="smt-products-filter">
        <?php // Create a dropdown with the categories ?>
        
    </section>
</main>