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
    private $aUser = [];

    function __construct($aUser = []) {
        $this->aUser = $aUser;
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

    public function nactiSeznamZarizeniUzivatele() : array {
        $q = "SELECT z.*, r.role, mj.jednotka FROM zarizeni AS z
            JOIN zarizeni2users AS zu ON z.id = zu.idzarizeni
            JOIN cis_merne_jednotky AS mj ON mj.id = z.idjednotky
            JOIN role AS r ON r.id = zu.idrole
            WHERE zu.iduser = ? ORDER BY id";
        $rows = db::fa($q, $this->aUser["id"]);
        
        foreach ($rows as $row) :
            $this->aZarizeni[$row["id"]] = $row;
        endforeach;
        if (empty($this->aZarizeni)) {
            $this->aZarizeni = [];
        }
        
        return $this->aZarizeni;
    }
    
}
