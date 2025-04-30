<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;


require_once 'vendor/autoload.php';
require_once 'Utils/utils.php';
require_once 'Utils/helper.php';
require_once 'Utils/request.php';


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
        $this->zarizeni = db::fa($q, $this->zarizeni["id"]);
        debug($this->zarizeni);
        if (db::nr() == 0) :
            $this->zarizeni = [];
            $this->zarizeni["id"] = 0;
            return;
        endif;

    }    
}
