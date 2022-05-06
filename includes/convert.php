<?php

function WCConvert ($products){
    return array_column($products, null, 'sku');
};

function convert_existing_categories($categories){
    format($categories);
    return array_column($categories, null, 'name');
}