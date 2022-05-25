<?php

function WCConvert ($products){
    return array_column($products, null, 'sku');
};

function convert_existing_categories($categories){
    return array_column($categories, null, 'name');
}

function calcCostPrice($price_incl, $tax, $profit){
    // Profit and tax is in the form 1.1 1.2 1.45
    $price_excl = $price_incl * 100 / ($tax * 100);
    $cost = $price_excl * 100 / ($profit * 100);

    return $cost;
}

function calcProfit($price_incl, $tax, $profit){
    $price_excl = $price_incl * 100 / ($tax * 100);
    $profit_amount = $price_excl * ($profit * 100 - 100) / ($profit * 100);
    return $profit_amount;
}