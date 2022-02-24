<?php
require __DIR__ . "/woocommerce-api.php";

// https://www.syntech.co.za/feeds/feedhandler.php?key=
class Syntech {
    private $feed;

    function registerFeed($feed){
        if($this->verifyFeed($feed)){
            $this->feed = $feed;
            $this->getData();
        }
    }

    function verifyFeed($feed){
        $urlPaternRegEx = "/^https:\/\/www\.syntech\.co\.za\/feeds\/feedhandler\.php\?key=[0-9A-Z-]+&feed=syntech-xml-full$/ig";
        if(preg_match($urlPaternRegEx, $feed)){
            return true;
        } 
        else {
            return false;
        }
    }

    function getData(){
        

       
        if($this->feed){
            $options = array(
                'http' => array(
                    'timeout' => 20
                )
            );
            $context = stream_context_create($options);
            $data = file_get_contents($this->feed, false, $context);


            $this->xml = simplexml_load_string($data);
        }
        

    }

    function displayData(){
        echo "<pre>";
        print_r($this->xml);
        echo "</pre>";
    }
}



?>