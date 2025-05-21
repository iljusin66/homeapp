<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;

require_once 'autoload.php';

new config();

/*
 * Třída pro práci s měřidly
 * 
 * @author Ivan La.
 * @version 1.0
 * @package App
 */
class meridla {

    private static $initialized = false;

    public $aMeridla = [];
    private $aUser = [];

    function __construct($aUser = []) {
        $this->aUser = $aUser;
        if (!self::$initialized) {
            $this->nactiMeridla();
            self::$initialized = true;
        }        
    }
    
    private function nactiMeridla() {

        $this->aMeridla["id"] = max(0, request::int("idz", "REQUEST"));
        if ($this->aMeridla["id"] == 0) :
            return;
        endif;
        $q = "SELECT * FROM meridla WHERE id = ?";
        $this->aMeridla = db::f($q, $this->aMeridla["id"]);
        if (empty($this->aMeridla)) :
            $this->aMeridla = [];
            $this->aMeridla["id"] = 0;
           return;
        endif;
    }

    public function nactiSeznamMeridelUzivatele() : array {
        $q = "SELECT z.*, r.role, mj.jednotka FROM meridla AS z
            JOIN meridla2users AS zu ON z.id = zu.idmeridla
            JOIN cis_merne_jednotky AS mj ON mj.id = z.idjednotky
            JOIN role AS r ON r.id = zu.idrole
            WHERE zu.iduser = ? ORDER BY id";
        $rows = db::fa($q, $this->aUser["id"]);
        
        foreach ($rows as $row) :
            $this->aMeridla[$row["id"]] = $row;
        endforeach;
        if (empty($this->aMeridla)) {
            $this->aMeridla = [];
        }
        
        return $this->aMeridla;
    }
    
}
