<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;

require_once 'autoload.php';

new config();




class odecet extends zarizeni{

    private static $initialized = false;

    function __construct() {
        if (!self::$initialized) {
            parent::__construct();
            self::$initialized = true;
        }
        
    }

            
}
