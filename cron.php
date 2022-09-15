<?php
require(dirname(__FILE__) . '/../../../wp-config.php');
require_once "rectron.php";
require_once(dirname(__FILE__) . '/includes/print.php');
require_once(dirname(__FILE__) . '/includes/lock.php');

$import_feed = function (){
    try{
        $rectron = new Rectron();
        $rectron->feed_loop();
    } 
    catch (Exception $e){
        format("Something went wrong");
    }
};

function run_cron(){
    global $import_feed;

    $time_start = microtime(true);
    $mem_start = memory_get_usage(true);
    
    $cron_file = fopen(dirname(__FILE__) . "/cron.txt", "w");
    fwrite($cron_file, "The cron job started at: " . date("F j, Y, g:i a") . "\n");
    fclose($cron_file);

    run_with_lock($import_feed);

    $mem_end = memory_get_usage(true);
    $time_end = microtime(true);

    echo 'the process took: ' . round(($time_end - $time_start) / 60) . " minutes\n";
    echo 'the process took: ' . (($mem_end - $mem_start) / (1024 * 1024)) . " MB of memory";

    $cron_file = fopen(dirname(__FILE__) . "/cron.txt", "a");
    fwrite($cron_file, "The cron job finished at: " . date("F j, Y, g:i a") . "\n");
    fwrite($cron_file, round(($time_end - $time_start) / 60) . " minutes\n");
    fwrite($cron_file, (($mem_end - $mem_start) / (1024 * 1024)) . " MB RAM\n");
    fclose($cron_file);
}



if(is_plugin_active("lk_supplier/smart_feeds.php" )) run_cron();
