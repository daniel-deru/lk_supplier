<?php
    include  dirname(plugin_dir_path(__FILE__)) . "/woocommerce-api.php";
    // include dirname(plugin_dir_path(__FILE__)) . "/syntech.php";
    include  dirname(plugin_dir_path(__FILE__)) . "/rectron.php";
    include  dirname(plugin_dir_path(__FILE__)) . "/includes/print.php";


// $categoryList = json_decode($smt_smart_feeds_listCategories(), true);
// $productList = json_decode($smt_smart_feeds_listProducts(), true);

$feedData = new Rectron(get_option("smt_smart_feeds_rectron_feed_onhand"));

// format($feedData->get_data());
$rectron_products = $feedData->get_data();

?>
<main id="smt_smart_feeds_products">
    <h1>Edit Products</h1>
    <?php if(!(get_option("smt_smart_feeds_consumer_key") && get_option("smt_smart_feeds_consumer_secret"))): ?>
        <h2>Please enter the WooCommerce API keys to continue</h2>
    <?php endif ?>
    <?php // This is the filter for the table?>
    <section id="smt-products-filter">
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
    </section>

    <?php // Build the table ?>
    <section>
        <table id="products-table">
            <tr id="smt-head">
                <th id="name">Name</th>
                <th id="cost-price">Cost Price (excl.)</th> <?php // display only?>
                <th id="other-cost">Other Cost</th>
                <th id="cost-of-goods">Cost of goods (excl.)</th> <?php // display only?>
                <th id="markup-type">Markup Type</th>
                <th id="markup">Markup</th>
                <th id="price">Price (incl.)</th> <?php // display only?>
                <th id="stock">Stock</th>  <?php // display only?>
                <th id="profit">Profit (excl.)</th> <?php // display only?>
            </tr>
            <?php foreach($rectron_products as $i => $product): ?>
                <tr class="smt-body">
                    <!-- Name -->
                    <td class="name">
                        <div><?php echo esc_html($product['Title'])?></div>
                        <div>SKU: <?php echo esc_html($product['Code'])?></div>
                    </td> 
                    <!-- Cost Price -->
                    <td class="cost-price-container">
                        R   <span class="cost-price" id="cost-price<?php echo esc_attr($i) ?>" >
                                <?php echo esc_html(floatval($product['SellingPrice'])) ?>
                            </span>
                    </td> 
                    <!-- Other Cost -->
                    <td class="other-cost"><input type="text" placeholder="Other Cost" data-index="<?php echo esc_html($i) ?>"></td> 
                    <!-- Cost Price + Other Cost -->
                    <td class="cost-of-goods-container">
                        R <span class="cost-of-goods" id="cost-of-goods<?php echo esc_attr($i) ?>"></span>
                    </td>
                    <!-- Markup Type -->
                    <td class="markup-type">
                        <select name="markup-type" id="markup-type<?php echo esc_attr($i) ?>">
                            <option value="percent">Percent</option>
                            <option value="fixed">Fixed Value</option>
                        </select>
                    </td>
                    <!-- Markup -->
                    <td class="markup">
                        <input type="text" placeholder="Markup" data-index="<?php echo esc_attr($i) ?>" id="markup<?php echo esc_html($i) ?>">
                    </td>
                    <!-- Price -->
                    <td class="final-price">R <span class="price" id="price<?php echo esc_html($i) ?>"></span></td>
                    <!-- Stock Quantity -->
                    <td><?php echo esc_html($product['OnHand'])?></td> 
                    <!-- Profit -->
                    <td class="profit-container">
                        R <span class="profit" id="profit<?php echo esc_attr($i) ?>"></span>
                    </td>

                </tr>
            <?php endforeach ?>
            
        </table>
    </section>
</main>