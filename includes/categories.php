<?php
include_once  dirname(plugin_dir_path(__FILE__)) . "/woocommerce-api.php";
require_once "print.php";

// This is the function that will create the categories from the main loop in rectron.php
function register_category($categories, &$unique_categories, $existing_categories, $woocommerce){
    $category_id = null;

    foreach($categories as $category){
        $category = preg_replace("/-(?=-)/", "", $category);
        if(isset($existing_categories[$category])){
            $category_id = $existing_categories[$category]['id'];
            continue;
        }
        else if(isset($unique_categories[$category])){
            $category_id = $unique_categories[$category];
            continue;
        }

        if($category_id == null){
            // try{
                $category_id = json_decode(smt_smart_feeds_createCategory(array('name' => $category), $woocommerce), true);
                if(isset($category_id['error'])) {
                    format($category_id['message']);
                    return array('error' => true);
                } else if(isset($category_id['id'])) $category_id = $category_id['id'];
            // } catch(Exception $e) {
            //     format("category id doesn't exist");
            //     format($e);
            // }
        } 
        else if($category_id){
            // try{
                $category_id = json_decode(smt_smart_feeds_createCategory(
                    array(
                        'name' => $category, 
                        'parent' => $category_id
                    ), $woocommerce), true);

                if(isset($category_id['error'])) {
                    format($category_id['message']);
                    return array('error' => true);
                } else if(isset($category_id['id'])) $category_id = $category_id['id'];
            // } catch(Exception $e) {
            //     format("category does exist");
            //     format($e);
            // }

        }
        // format($category);
        $unique_categories[$category] = $category_id;
    }
}