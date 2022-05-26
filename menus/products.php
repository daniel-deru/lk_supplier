<?php
    // include  dirname(plugin_dir_path(__FILE__)) . "/woocommerce-api.php";
    include_once  dirname(plugin_dir_path(__FILE__)) . "/rectron.php";
    include_once  dirname(plugin_dir_path(__FILE__)) . "/includes/print.php";
    include_once dirname(plugin_dir_path(__FILE__)) . "/includes/convert.php";


// $categoryList = json_decode($smt_smart_feeds_listCategories(), true);
// $productList = json_decode($smt_smart_feeds_listProducts(), true);
$tax = (intval(get_option("smt_smart_feeds_tax_rate")) + 100) / 100;
$rectron = new Rectron();
// Things that I need to display the products on screen
/*
Name
SKU
Stock
price NB The product is already created
Margin

(also the dynamic margin object list)


*/
$wp_products = $rectron->getProducts();
$products = [];
$count = 1;
foreach($wp_products as $product){

    // Get the metadata
    $meta_data = $product->get_meta_data();
    $custom_data = null;
    foreach($meta_data as $meta){
        $data = $meta->get_data();
        if($data['key'] === 'custom') $custom_data = $data['value'];
        if($data['key'] === 'original') $custom_data['cost'] = $data['value']['cost'];
    }
    
    format($count . ": \n");
    // format($meta_data);
    format($custom_data);
    $count++;

    $product_array = array(
        'name' => $product->get_name(),
        'sku' => $product->get_sku(),
        'stock_quantity' => $product->get_stock_quantity(),
        'price' => $product->get_price(),
        'margin' => $rectron->getProductMargin($product->get_price()),
        // 'attributes' => explode(" | ", $product->get_attribute('custom')),
        'status' => $product->get_status(),
        'custom_data' => $custom_data
    );
    // array_push($products, $product_array);
    $products[$product->get_sku()] = $product_array;
}

// format($products);

// $rectron_products = $feedData->get_data();

wp_localize_script("smt_smart_feeds_products_script", "rectron_products", array(
    'products' => $products,
    'ajax_url' => admin_url('admin-ajax.php')
));

$tableIndex = 0;

?>
<main id="smt_smart_feeds_products">
    <h1>Edit Products</h1>

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
            <?php foreach($products as $i => $product): 
                $tableIndex++;
                ?>
                <tr class="smt-body">
                    <td class="index"><?php echo esc_html($tableIndex) ?></td>
                    <!-- Name -->
                    <td class="name">
                        <!-- Fix Title -->
                        <div><?php echo esc_html($product['name'])?></div>
                        <div>SKU: <?php echo esc_html($product['sku'])?></div>
                    </td> 
                    <!-- Don't import product -->
                    <td class="no-import">
                        <input 
                            type="checkbox" 
                            name="import" 
                            class="import"
                            <?php echo $product['custom_data']['skip'] == '0' ? '' : 'checked'; ?>
                            id="import<?php echo esc_html($tableIndex) ?>" 
                            data-index="<?php echo esc_html($tableIndex) ?>" 
                            data-sku="<?php echo esc_html($product['sku']) ?>"
                        >
                    </td>
                    <!-- Cost Price -->
                    <td class="cost-price-container">
                        R   <span class="cost-price" id="cost-price<?php echo esc_attr($tableIndex) ?>" >
                                <?php echo esc_html(number_format(round(floatval($product['custom_data']['cost']), 2), 2)) ?>
                            </span>
                    </td> 
                    <!-- Other Cost -->
                    <td class="other-cost">
                        <input type="text" value="<?php echo esc_attr($product['custom_data']['other_cost']) ?>" placeholder="Other Cost" data-index="<?php echo esc_html($tableIndex) ?>" data-sku="<?php echo esc_html($product['sku']) ?>">
                    </td> 
                    <!-- Cost Price + Other Cost -->
                    <td class="cost-of-goods-container">
                        R   <span class="cost-of-goods" id="cost-of-goods<?php echo esc_attr($tableIndex) ?>" data-sku="<?php echo esc_attr($i) ?>">
                                <?php echo esc_html(number_format(round(floatval(calcCostPrice($product['price'], $tax, $product['margin'])), 2), 2)) ?>
                            </span>
                    </td>
                    <!-- Markup Type -->
                    <td class="markup-type">
                        <select 
                            name="markup-type" 
                            id="markup-type<?php echo esc_attr($tableIndex) ?>" 
                            data-sku="<?php echo esc_html($product['sku']) ?>"
                            value="<?php echo esc_attr($product['custom_data']['margin_type']); ?>"
                            >
                            <option value="percent">Percent</option>
                            <option value="fixed">Fixed Value</option>
                        </select>
                    </td>
                    <!-- Markup -->
                    <td class="markup">
                        <input type="text" placeholder="Markup" value="<?php echo esc_html(floatval($product['margin']) * 100 - 100)?>" data-index="<?php echo esc_attr($tableIndex) ?>" id="markup<?php echo esc_html($tableIndex) ?>" data-sku="<?php echo esc_html($product['sku']) ?>">
                    </td>
                    <!-- Price -->
                    <td class="final-price">
                        R   <span class="price" id="price<?php echo esc_html($tableIndex) ?>">
                                <?php echo esc_html(number_format(round(floatval($product['price']), 2), 2)) ?>
                            </span>
                    </td>
                    <!-- Stock Quantity -->
                    <td class="stock"><?php echo esc_html($product['stock_quantity'])?></td> 
                    <!-- Profit -->
                    <td class="profit-container">
                        R <span class="profit" id="profit<?php echo esc_attr($tableIndex) ?>"><?php echo esc_html(number_format(round(floatval(calcProfit($product['price'], $tax, $product['margin'])), 2), 2));?></span>
                    </td>

                </tr>
            <?php endforeach ?>
            
        </table>
    </section>
</main>

