<?php
require_once dirname(plugin_dir_path(__FILE__)) . "/includes/print.php";
require_once "config.php";

class Syntech {
    public function __construct() {
        $this->feed_url = get_option(SYNTECH_URL);
        $this->get_categories();
    }

    public function get_categories(){
        if(!$this->feed_url) return;

        $data = curl_get_file_contents($this->feed_url);
        $dirty_data = simplexml_load_string($data);
        format($dirty_data);
    }
}