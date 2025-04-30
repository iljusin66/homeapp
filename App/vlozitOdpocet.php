<?php
set_time_limit(600);

use Config\config;
use Latecka\HomeApp\Utils\utils;

use Latecka\HomeApp\Utils\request;
use Latecka\HomeApp\Utils\db;


require_once 'odpocet.php';



class vlozitOdpocet extends odpocet {

 

    function __construct() {
        parent::__construct();
        debug($this);
    }
    
    
}
