<?php
set_time_limit(600);

use Config\config;
use Latecka\HomeApp\Utils\utils;

use Latecka\HomeApp\Utils\request;
use Latecka\HomeApp\Utils\db;


require_once 'Config/config.php';
new config();

require_once 'vendor/autoload.php';
require_once 'Utils/utils.php';
require_once 'Utils/helper.php';
require_once 'Utils/request.php';
require_once 'zarizeni.php';


class odpocet extends zarizeni{

 

    function __construct() {
        parent::__construct();
        
    }


    
            
}
