<?php

require_once dirname(plugin_dir_path(__FILE__)) . "/includes/print.php";
include dirname(plugin_dir_path(__FILE__)) . "/includes/link.php";
require_once dirname(plugin_dir_path(__FILE__)) . "/syntech.php";

$link = sanitize_url($getHost());


$syntechCategories = Syntech::get_categories();
$rectronCategories = Rectron::getCategories();

function displayCategories($categories, $level=0){
    if($categories == "") return;

    $keys = array_keys($categories);

    $level++;

    foreach($keys as $key){
        echo "<div class='level-" . $level . "'>" . $key . "</div>";
        displayCategories($categories[$key], $level);
    }
}




?>

<h1>Categories page</h1>

<!-- <div id="categories">
    <div id="syntech-categories">
        <h2>Syntech Category Tree</h2>
        <div id="syntech-category-container">
            <?php //displayCategories($syntechCategories) ?>
        </div>
    </div>
    <div id="rectron-categories">
        <h2>Rectron Category Tree</h2>
        <div id="rectron-category-container">

        </div>
    </div>
</div> -->

