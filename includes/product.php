<?php

function smt_smart_feeds_get_meta_data($key, &$product){
   $meta_data = $product->get_meta_data();

   foreach($meta_data as $meta){
       $data = $meta->get_data();
       if($data['key'] == $key) return $data['value'];
   }

   return null;
}