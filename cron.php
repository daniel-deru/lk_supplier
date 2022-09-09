<?php
require(dirname(__FILE__) . '/../../../wp-config.php');
require_once "rectron.php";
require_once(dirname(__FILE__) . '/includes/print.php');

function run_cron(){

    $cron_file = fopen("./cron.text", "w");
    fwrite($cron_file, "The cron job fired at: " . date("F j, Y, g:i a"));
    fclose($cron_file);

    $lock_file = fopen("lock.txt", "w+");
    if(!flock($lock_file,  LOCK_EX | LOCK_NB)) return;

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

    flock($lock_file, LOCK_UN);
    fclose($lock_file);
}

if(is_plugin_active("lk_supplier/smart_feeds.php" )) run_cron();
