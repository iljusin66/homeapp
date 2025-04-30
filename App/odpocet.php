<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;

require_once 'autoload.php';
new config();

require_once 'vendor/autoload.php';
require_once 'Utils/utils.php';
require_once 'Utils/helper.php';
require_once 'Utils/request.php';


class odpocet extends zarizeni{

    function __construct() {
        parent::__construct();
        
    }

            
}
