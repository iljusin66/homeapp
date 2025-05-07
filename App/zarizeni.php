<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;


require_once 'autoload.php';

class zarizeni {

    private static $initialized = false;

    public $aZarizeni = [];  

    function __construct() {
        if (!self::$initialized) {
            $this->nactiZarizeni();
            self::$initialized = true;
        }        
    }
    
    private function nactiZarizeni() {

        $this->aZarizeni["id"] = max(0, request::int("idz", "REQUEST"));
        if ($this->aZarizeni["id"] == 0) :
            return;
        endif;
        $q = "SELECT * FROM zarizeni WHERE id = ?";
        $this->aZarizeni = db::f($q, $this->aZarizeni["id"]);
        if (empty($this->aZarizeni)) :
            $this->aZarizeni = [];
            $this->aZarizeni["id"] = 0;
           return;
        endif;
    }    
}
