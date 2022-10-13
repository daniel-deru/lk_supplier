<?php
require_once "includes/print.php";
require_once "config.php";

class Syntech {
    public function __construct() {
        $this->feed_url = get_option(SYNTECH_URL);
        $this->products = Syntech::get_data($this->feed_url);
    }

    public static function get_data($url){
        if(!$url) return;

        $data = curl_get_file_contents($url);
        $dirty_data = simplexml_load_string($data);
        return $dirty_data;
    }

    public static function get_categories(){
        $url = get_option(SYNTECH_URL);
        
        if(!$url) return;

        $products = Syntech::get_data($url);

        $categories = array();

        foreach($products->stock->product as $product){

            $dataObj = json_encode($product->categorytreealt);
            $dataArr = json_decode($dataObj, true);

            $categoryTreeArray = explode("/", $dataArr[0]);

            Syntech::createCategoryTree($categoryTreeArray, $categories);
        }

        return $categories;
    }

    private static function createCategoryTree($categoryTree, &$tree) {
        if(count($categoryTree) < 1) return;

        $first_element = array_shift($categoryTree);

        if(!isset($tree[$first_element])){
            if(count($categoryTree) < 1) $tree[$first_element] = "";
            else $tree[$first_element] = [];
        }

        Syntech::createCategoryTree($categoryTree, $tree[$first_element]);
    }
}