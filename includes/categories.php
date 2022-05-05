<?php
include_once  dirname(plugin_dir_path(__FILE__)) . "/woocommerce-api.php";
require_once "print.php";

function register_category($categories, &$unique_categories, $existing_categories, $woocommerce){
    $category_id = null;

    foreach($categories as $category){
        if(isset($unique_categories[$category]) || isset($existing_categories[$category])) continue;
        // else if(!$category_id) $category_id = json_decode(smt_smart_feeds_createCategory(array('name' => $category), $woocommerce), true)['id'];
        // else if($category_id){
        //     $category_id = json_decode(smt_smart_feeds_createCategory(
        //         array(
        //             'name' => $category, 
        //             'parent' => $category_id
        //         )
        //         , $woocommerce), true)['id'];
        // }
        // format($category);
        $unique_categories[$category] = $category;
    }
}