<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;

require_once 'autoload.php';

new config();

/**
 * Třída pro práci s ceníky měřidel
 * @author Ivan Latečka
 * @version 1.0
 */
class ceniky extends meridla {

    public $aCeniky = []; // Seznam ceníků měřidla
    public $aCenik = []; // Ceník podle ID z requestu
    private $aUser = [];

    function __construct($aUser = []) {
        $this->aUser = $aUser;
        parent::__construct($this->aUser);
        $this->nactiCeniky();
        $this->nactiCenik();
    }

    /**
     * Načte ceník podle ID z requestu
     * @return void 
     */
    private function nactiCenik() {
        $idc = max(0, request::int("idc", "REQUEST"));

        $this->aCenik = $this->aCeniky[$idc] ?? [];
        if (empty($this->aCenik)) :
            $this->aCenik["id"] = 0;
            return;
        endif;
    }

    /**
     * Načte všechny ceníky pro měřidlo
     * @return array
     */
    private function nactiCeniky(): array {
        if (empty($this->aMeridlo) || $this->aMeridlo['id'] == 0) {
            $this->aCeniky = [];
            return [];
        }

        $q = "SELECT * FROM ceniky WHERE idmeridla = ? ORDER BY platnyod DESC";
        $rows = db::fa($q, $this->aMeridlo["id"]);
        
        foreach ($rows as $row) :
            $this->aCeniky[$row["id"]] = $row;
        endforeach;
        
        if (empty($this->aCeniky)) {
            $this->aCeniky = [];
        }

        return $this->aCeniky;
    }

    /**
     * Vrátí seznam ceníků seřazený podle aktuálnosti
     * @return array
     */
    public function getCeniky(): array {
        return $this->aCeniky;
    }
}
