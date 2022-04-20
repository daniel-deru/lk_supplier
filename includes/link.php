<?php

function getHost(){
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    $host = "https";
    else $host = "http";

    $host .= "://" . $_SERVER['HTTP_HOST'];
    return $host;
}