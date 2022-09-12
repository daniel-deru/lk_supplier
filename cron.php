<?php
require(dirname(__FILE__) . '/../../../wp-config.php');
require_once "rectron.php";
require_once(dirname(__FILE__) . '/includes/print.php');
require_once(dirname(__FILE__) . '/includes/lock.php');

$import_feed = function (){
    try{
        $time_start = microtime(true);
        $mem_start = memory_get_usage(true);
    
        $rectron = new Rectron();
        $rectron->feed_loop();
    
        $mem_end = memory_get_usage(true);
        $time_end = microtime(true);
    
        format('the process took: ' . ($time_end - $time_start) . " seconds");
        format('the process took: ' . (($mem_end - $mem_start) / (1024 * 1024)) . " MB of memory");
    } catch (Exception $e){
        format($e);
    }
};

function run_cron(){
    global $import_feed;
    
    $cron_file = fopen(dirname(__FILE__) . "/cron.txt", "w");
    fwrite($cron_file, "The cron job fired at: " . date("F j, Y, g:i a"));
    fclose($cron_file);

    run_with_lock($import_feed);
}



if(is_plugin_active("lk_supplier/smart_feeds.php" )) run_cron();
