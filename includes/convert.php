<?php

function WCConvert ($products){
    return array_column($products, null, 'sku');
};