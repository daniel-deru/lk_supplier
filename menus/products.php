<?php
    include  dirname(plugin_dir_path(__FILE__)) . "/woocommerce-api.php";
    // include dirname(plugin_dir_path(__FILE__)) . "/syntech.php";
    include  dirname(plugin_dir_path(__FILE__)) . "/rectron.php";
    include  dirname(plugin_dir_path(__FILE__)) . "/includes/print.php";


// $categoryList = json_decode($smt_smart_feeds_listCategories(), true);
// $productList = json_decode($smt_smart_feeds_listProducts(), true);

$feedData = new Rectron(get_option("smt_smart_feeds_rectron_feed_onhand"));

format($feedData->get_data());

?>
<main>
    <h1>Edit Products</h1>
    <?php if(!(get_option("smt_smart_feeds_consumer_key") && get_option("smt_smart_feeds_consumer_secret"))): ?>
        <h2>Please enter the WooCommerce API keys to continue</h2>
    <?php endif ?>
    <?php // This is the filter for the table?>
    <section id="smt-products-filter">
        <div>
            <select name="category-filter" id="category-filter">
                <option value="" select disabled>Select Category</option>
                <?php //foreach($categoryList['data'] as $category): ?>
                    <!-- <option value="<?php //echo esc_attr($category['id'])?>"><?php //echo esc_html($category['name'])?></option> -->
                <?php //endforeach ?>
            </select>
        </div>
        <div>
            <select name="price-filter" id="price-filter">
                <option value="more_than">More Than</option>
                <option value="less_than">Less Than</option>
            </select>
            <input type="text" placeholder="Enter price">
        </div>
        <div>
            <input type="text" placeholder="Enter name or SKU">
        </div>
        <div>
            <button type="button">Filter</button>
        </div>
    </section>

    <?php // Build the table ?>
    <section>
        <table>
            <tr>
                <th>Name</th>
                <th>Cost Price</th>
                <th>Other Cost</th>
                <th>Cost of goods</th> <?php // display only?>
                <th>Markup</th>
                <th>Price</th> <?php // display only?>
                <th>Stock</th>
                <th>Profit</th> <?php // display only?>
            </tr>
            <?php //foreach($productList['data'] as $product): ?>
                <!-- <tr>
                    <td><?php //echo esc_url($product['name'])?></td> <?php // Name ?>
                    <td><?php //echo esc_url($product['name'])?></td> <?php // Cost Price ?>
                    <td><?php //echo esc_url($product['name'])?></td> <?php // Other Cost ?>
                    <td><?php //echo esc_url($product['name'])?></td> <?php // Cost of goods ?>
                    <td><?php //echo esc_url($product['name'])?></td> <?php // Markup ?>
                    <td><?php //echo esc_url($product['price'])?></td> <?php // Price ?>
                    <td><?php //echo esc_url($product['stock_quantity'])?></td> <?php // Stock ?>
                    <td><?php //echo esc_url($product['name'])?></td> <?php // Profit ?>

                </tr> -->
            <?php //endforeach ?>
            
        </table>
    </section>
</main>