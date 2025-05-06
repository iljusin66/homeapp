<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;


require_once 'autoload.php';


class zapisOdecet extends odecet {

    public $aOdecet = [];
    public 

    function __construct() {
        parent::__construct();
        $this->nactiOdecet();
    }

    private function nactiOdecet() {
        if (c_RequestPost) :
            $this->nactiOdecetPost();
        else:
            $this->nactiOdecetGet();
        endif;
        

    }

    private function nactiOdecetPost() {
        $this->aOdecet["id"] = request::int('ido', 'POST');

    }

    private function nactiOdecetGet() {
        $this->aOdecet["id"] = request::int('ido', 'GET');
    }
    
    
}
