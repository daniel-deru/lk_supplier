<?php

$getHost = function (){
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $protocol = "https";
    else $protocol = "http";

    $protocol .= "://" . $_SERVER['HTTP_HOST'];
    return $protocol;
};