<?php
require(dirname(__FILE__) . '/../../../wp-config.php');
require_once "rectron.php";

try{
    $rectron = new Rectron;
    $rectron->feed_loop();
} catch (Exception $e){
    $cron_file = fopen("cron.text", 'w');
    fwrite($cron_file, $e . "\n\n\n");
}


?>