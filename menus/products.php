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

wp_localize_script("smt_smart_feeds_products_script", "rectron_products", array(
    'products' => $rectron_products,
    'ajax_url' => admin_url('admin-ajax.php')
))

?>
<main id="smt_smart_feeds_products">
    <h1>Edit Products</h1>
    <?php if(!(get_option("smt_smart_feeds_consumer_key") && get_option("smt_smart_feeds_consumer_secret"))): ?>
        <h2>Please enter the WooCommerce API keys to continue</h2>
    <?php endif ?>
    <?php // This is the filter for the table?>

    <section id="smt-products-filter">
        <h3>Filter Feed</h3>
        <div>
            <div>
                <select name="price-filter" id="price-filter-compare">
                    <option value="more_than">More Than</option>
                    <option value="less_than">Less Than</option>
                </select>
                <input type="text" placeholder="Enter price" id="price-filter">
            </div>

            <div>
                <input type="text" placeholder="Enter name or SKU" id="filter-description">
            </div>
            <div>
                <input type="reset" value="Reset" id="reset-filter">
            </div>
        </div>
    </section>
    <section>
        <div id="save">
            <button type="button" id="save-settings">Save</button>
        </div>
    </section>

    <?php // Build the table ?>
    <section>
        <table id="products-table">
            <tr id="smt-head">
                <th id="index">No</th>
                <th id="name">Name</th>
                <th id="no-import">Skip</th>
                <th id="cost-price">Cost Price <br> (excl.)</th> <?php // display only?>
                <th id="other-cost">Other Cost <br> (excl.)</th>
                <th id="cost-of-goods">Cost of goods <br> (excl.)</th> <?php // display only?>
                <th id="markup-type">Markup Type</th>
                <th id="markup">Markup</th>
                <th id="price">Price <br> (incl.)</th> <?php // display only?>
                <th id="stock">Stock</th>  <?php // display only?>
                <th id="profit">Profit <br> (excl.)</th> <?php // display only?>
            </tr>
            <?php foreach($rectron_products as $i => $product): ?>
                <tr class="smt-body">
                    <td class="index"><?php echo esc_html($i + 1) ?></td>
                    <!-- Name -->
                    <td class="name">
                        <div><?php echo esc_html($product['Title'])?></div>
                        <div>SKU: <?php echo esc_html($product['Code'])?></div>
                    </td> 
                    <!-- Don't import product -->
                    <td class="no-import">
                        <input 
                            type="checkbox" 
                            name="import" 
                            class="import" 
                            id="import<?php echo esc_html($i) ?>" 
                            data-index="<?php echo esc_html($i) ?>" 
                            data-sku="<?php echo esc_html($product['Code']) ?>"
                        >
                    </td>
                    <!-- Cost Price -->
                    <td class="cost-price-container">
                        R   <span class="cost-price" id="cost-price<?php echo esc_attr($i) ?>" >
                                <?php echo esc_html(number_format(round(floatval($product['SellingPrice']), 2))) ?>
                            </span>
                    </td> 
                    <!-- Other Cost -->
                    <td class="other-cost">
                        <input type="text" placeholder="Other Cost" data-index="<?php echo esc_html($i) ?>" data-sku="<?php echo esc_html($product['Code']) ?>">
                    </td> 
                    <!-- Cost Price + Other Cost -->
                    <td class="cost-of-goods-container">
                        R   <span class="cost-of-goods" id="cost-of-goods<?php echo esc_attr($i) ?>">
                                <?php echo esc_html(number_format(round(floatval($product['SellingPrice'])))) ?>
                            </span>
                    </td>
                    <!-- Markup Type -->
                    <td class="markup-type">
                        <select name="markup-type" id="markup-type<?php echo esc_attr($i) ?>" data-sku="<?php echo esc_html($product['Code']) ?>">
                            <option value="percent">Percent</option>
                            <option value="fixed">Fixed Value</option>
                        </select>
                    </td>
                    <!-- Markup -->
                    <td class="markup">
                        <input type="text" placeholder="Markup" data-index="<?php echo esc_attr($i) ?>" id="markup<?php echo esc_html($i) ?>" data-sku="<?php echo esc_html($product['Code']) ?>">
                    </td>
                    <!-- Price -->
                    <td class="final-price">
                        R   <span class="price" id="price<?php echo esc_html($i) ?>">
                                <?php echo esc_html(number_format(round(floatval($product['SellingPrice']) * 1.15, 2), 2)) ?>
                            </span>
                    </td>
                    <!-- Stock Quantity -->
                    <td class="stock"><?php echo esc_html($product['OnHand'])?></td> 
                    <!-- Profit -->
                    <td class="profit-container">
                        R <span class="profit" id="profit<?php echo esc_attr($i) ?>">0</span>
                    </td>

                </tr>
            <?php endforeach ?>
            
        </table>
    </section>
</main>