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
 * @author Ivan Latečka
 * @version 1.0
 */
class meridla {

    private static $initialized = false;
    public $obdobiOdectu = '';
    private $jenAktivniMeridla = 1; // 1 - jen aktivní, 0 - všechny
    private $jenAktivniUzivatele = 1; // 1 - jen aktivní, 0 - všichni
    
    public $aMeridla = []; // Seznam měřidel uživatele
    public $aMeridlo = []; // Měřidlo podle ID z requestu
    private $aUser = [];
    public $aJednotkyMeridel = []; // Jednotky měřidel načtené z DB

    function __construct($aUser = []) {
        $this->aUser = $aUser;
        if (!self::$initialized) {
            $this->nactiJednotkyMeridel();
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

    private function nactiJednotkyMeridel() : void {
        $q = "SELECT * FROM cis_merne_jednotky ORDER BY id";
        $rows = db::fa($q);
        $this->aJednotkyMeridel = [];
        foreach ($rows as $row) :
            $this->aJednotkyMeridel[$row["id"]] = $row;
        endforeach;
    }

    
    
}
