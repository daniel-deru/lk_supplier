<?php

class TaxClass {
    function __construct($tax_rate){
        $this->tax_rate = $tax_rate;
        $this->createClass();
    }

    function createClass(){
        $tax_classes = WC_Tax::get_tax_classes();
        if(in_array('Feed Tax', $tax_classes)) return $this->updateTax($this->tax_rate);

        $tax_class = WC_Tax::create_tax_class("Feed Tax", "feed-tax");

        $tax_rate_data = array(
            'tax_rate_country' => '*',
            'tax_rate_state' => '*',
            'tax_rate' => $this->tax_rate,
            'tax_rate_name' => 'Feed Tax',
            'tax_rate_priority' => 1,
            'tax_rate_compound' => 0,
            'tax_rate_shipping' => 1,
            'tax_rate_order' => 0,
            'tax_rate_class' => "feed-tax"
        );

        $tax_rate_id = WC_Tax::_insert_tax_rate($tax_rate_data);
    }

    function updateTax($amount){

        $rates = WC_Tax::get_rates_for_tax_class("Feed Tax");
        $tax_id = null;

        foreach($rates as $rate){
            if($rate->tax_rate_name === "Feed Tax") $tax_id = $rate->tax_rate_id;
        }
        if($tax_id){
            WC_Tax::_update_tax_rate($tax_id, ['tax_rate' => $amount]);
        }
        // See info about the tax class
        // $tax_rate = WC_Tax::_get_tax_rate($tax_id);
        // format($tax_rate);

    }

    static function getTaxRate(){
        $rates = WC_Tax::get_rates_for_tax_class("Feed Tax");
        $tax_id = null;

        foreach($rates as $rate){
            if($rate->tax_rate_name === "Feed Tax") $tax_id = $rate->tax_rate_id;
        }
        // See info about the tax class
        $tax_rate = WC_Tax::_get_tax_rate($tax_id);
        return $tax_rate['tax_rate'];
    }
}