<?php
set_time_limit(600);

use Config\config;
use Latecka\HomeApp\Utils\utils;

use Latecka\HomeApp\Utils\request;
use Latecka\HomeApp\Utils\db;


require_once 'zarizeni.php';



class vlozitOdpocet extends zarizeni {

 

    function __construct() {
        parent::__construct();
    }
    
    function vlozOdpocet($id, $odpo) {
        
    }
    
}
