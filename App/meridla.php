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
    public $rokOdectu = 0;
    private $jenAktivniMeridla = 1; // 1 - jen aktivní, 0 - všechny
    private $jenAktivniUzivatele = 1; // 1 - jen aktivní, 0 - všichni
    
    public $aMeridla = [];
    public $aMeridlo = []; // Seznam měřidel uživatele
    private $aUser = [];

    function __construct($aUser = []) {
        $this->aUser = $aUser;
        $this->rokOdectu = date("Y");
        if (!self::$initialized) {
            $this->nactiSeznamMeridelUzivatele();
            $this->nactiMeridlo();
            self::$initialized = true;
        }

    }
    
    /**
     * Načte měřidlo podle ID z requestu
     * @return void 
     */
    private function nactiMeridlo() {
        $idm = max(0, request::int("idm", "REQUEST"));
        $this->aMeridlo = $this->aMeridla[$idm] ?? [];
        if (empty($this->aMeridlo)) :
            $this->aMeridlo["id"] = 0;
            return;
        elseif ($this->jenAktivniMeridla && $this->aMeridlo["aktivni"] < 1) :
            $this->aMeridlo["id"] = 0;
            return;
        endif;
    }

    private function nactiSeznamMeridelUzivatele() : array {
        $q = "SELECT m.*, r.role, mj.jednotka FROM meridla AS m
            JOIN meridla2users AS mu ON m.id = mu.idmeridla AND m.aktivni >= ?
            JOIN cis_merne_jednotky AS mj ON mj.id = m.idjednotky
            JOIN role AS r ON r.id = mu.idrole
            WHERE mu.iduser = ? ORDER BY id";
        $rows = db::fa($q, $this->jenAktivniUzivatele, $this->aUser["id"]);
        
        foreach ($rows as $row) :
            $this->aMeridla[$row["id"]] = $row;
        endforeach;
        if (empty($this->aMeridla)) {
            $this->aMeridla = [];
        }
        
        return $this->aMeridla;
    }
    
}
