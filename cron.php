<?php
require(dirname(__FILE__) . '/../../../wp-config.php');
require_once "rectron.php";
$rectron = new Rectron;
$rectron->feed_loop();

?>