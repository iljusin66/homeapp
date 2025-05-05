<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;


require_once 'autoload.php';

class zarizeni {

    public $zarizeni = [];   

    function __construct() {
        $this->nactiZarizeni();
        
    }
    
    private function nactiZarizeni() {
        
        $this->zarizeni["id"] = max(0, request::int("idz"));
        
        if ($this->zarizeni["id"] == 0) :
            return;
        endif;
        $q = "SELECT * FROM zarizeni WHERE id = ?";
        $this->zarizeni = db::f($q, $this->zarizeni["id"]);
        if (empty($this->zarizeni)) :
            $this->zarizeni = [];
            $this->zarizeni["id"] = 0;
            return;
        endif;

    }    
}
