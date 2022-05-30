<?php
// include_once  dirname(plugin_dir_path(__FILE__)) . "/woocommerce-api.php";
require_once "print.php";

// Use this to create the categories insteads
// wp_insert_term( 'My New Category', 'product_cat', array(
//     'description' => 'Description for category', // optional
//     'parent' => 0, // optional
//     'slug' => 'my-new-category' // optional
// ));

// This is the function that will create the categories from the main loop in rectron.php
/*
    @params
        - $categories --> from the rectron feed
        - $unique_categories --> associative array for filtering out copies
        - $existing_categories --> all the current categories in wordpress
        - $woocommerce --> woocommerce api object not used anymore

*/
function register_category($categories, &$unique_categories, $existing_categories){
    if(count($categories) <= 1) return;
    $category_id = null;

    foreach($categories as $category){
        $category = preg_replace("/-(?=-)/", "", $category);
        if(isset($existing_categories[$category])){
            $category_id = $existing_categories[$category]['term_id'];
            continue;
        }
        else if(isset($unique_categories[$category])){
            $category_id = $unique_categories[$category];
            continue;
        }
        // If the category doesn't have a parent
        if($category_id == null){
                $category_id = wp_insert_term( $category, 'product_cat', array(
                    'parent' => 0, // optional
                    'slug' => $category // optional
                ));

                if(isset($category_id['term_id'])) $category_id = $category_id['term_id'];
        } 
        else if($category_id){

                $category_id = wp_insert_term( $category, 'product_cat', array(
                    'parent' => $category_id, // optional
                    'slug' => $category // optional
                ));

                if(isset($category_id['term_id'])) $category_id = $category_id['term_id'];

        }
        $unique_categories[$category] = $category_id;
    }
}