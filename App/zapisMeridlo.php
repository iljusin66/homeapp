<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;


require_once 'meridla.php';



class zapisMeridlo extends meridla {

    private $aUser = [];
    public $aMeridla = [];

    function __construct() {
        parent::__construct();
    }
    
    
}
