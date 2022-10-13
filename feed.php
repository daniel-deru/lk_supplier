<?php

require_once "syntech.php";
require_once "rectron.php";


class Feed {
    public function __construct($test = "") {
        if($test == "syntech") new Syntech();
        else if($test == "rectron") new Rectron();
        else {
            new Syntech();
            new Rectron();
        }
    }
}