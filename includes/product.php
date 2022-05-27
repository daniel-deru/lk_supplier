<?php

function smt_smart_feeds_get_meta_data($key, &$product){
   $meta_data = $product->get_meta_data();

   foreach($meta_data as $meta){
       $data = $meta->get_data();
       if($data['key'] == $key) return $data['value'];
   }

   return null;
}

// Calculate the selling price of a product
function calcSellingPrice($cost,  $profit, $tax){

}

// calculate the profit that needs to be applied
function getProfit($cost){
    $dynamic_margins = json_decode(get_option("smt_smart_feeds_dynamic_rules"));
    $base_margin = floatval(get_option("smt_smart_feeds_base_margin"));

    if(isset($base_margin)) $base_margin = ($base_margin + 100) / 100;
    else $base_margin = 1;

    $margin = $base_margin;

    if($dynamic_margins){
        foreach($dynamic_margins as $dynamic_margin){
            $from = intval($dynamic_margin->more_than);
            $to = intval($dynamic_margin->less_than);

            // Check if the cost is between the range of the dynamic rule
            if($cost > $from && $cost < $to){
                $margin = (intval($dynamic_margin->margin) + 100) / 100;

            } 
        }
    }else {
        $margin = $base_margin;
    }
    // margin is in the form 1.1 1.2 1.3
    return $margin;
}