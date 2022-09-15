<?php
require_once(dirname(__FILE__) . '/print.php');


function run_with_lock($run_task){
    $lock_file = dirname(__FILE__) . "/../lock.txt";
    $lock_file_stream = fopen($lock_file, "w+");

    if(!flock($lock_file_stream,  LOCK_EX | LOCK_NB)){
        echo "<h4>Cron Job skipped because the previous job didn't finish yet</h4>";
        return;
    }

    $run_task();

    flock($lock_file_stream, LOCK_UN);
    fclose($lock_file_stream);
}