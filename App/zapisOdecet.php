<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;


require_once 'autoload.php';


class zapisOdecet extends odecet {

    public $errors = [];
    public $aOdecet = [];
    public $aUser = [];

    function __construct($aUser) {
        parent::__construct($aUser);
        $this->aUser = $aUser;
        $this->nactiOdecet();
    }

    private function nactiOdecet() {
        
        if (c_RequestPost) :
            $this->nactiOdecetPost();
            $this->ulozOdecet();
        else:
            $this->nactiOdecetGet();
        endif;
        

    }

    private function nactiOdecetPost() {
        $this->aOdecet["id"] = request::int('ido', 'POST');

    }

    private function nactiOdecetGet() {
        $this->aOdecet["id"] = request::int('ido', 'GET');
        $q = "SELECT * FROM odecty WHERE id = ? AND idmeridla = ?";
        $this->aOdecet = db::f($q, $this->aOdecet["id"], $this->aMeridla["id"]);
    }

    private function ulozOdecet() {
        $this->aOdecet["casodectu"] = request::string('casodectu', 'POST');
        $this->aOdecet["odecet"] = request::float('odecet', 'POST');
        $this->aOdecet["poznamka"] = request::string('poznamka', 'POST');
        if (!$this->validujOdecet()) {
            debug(['ulozOdecet()->validujOdecet(): ' => $this->errors]);
            return;
        }
        if ($this->aOdecet["id"] == 0) :
            $this->zapisNovyOdecet();
        else:
            $this->opravOdecet();
        endif;  
    }

    private function zapisNovyOdecet() {
            $q = "INSERT INTO odecty (idmeridla, casodectu, odecet, poznamka, zadal) VALUES (?, ?, ?, ?, ?)";
            db::q($q, $this->aMeridla["id"], utils::formatDbDateTime($this->aOdecet["casodectu"]), $this->aOdecet["odecet"], $this->aOdecet["poznamka"], $this->aUser["id"]);
        
            $this->aOdecet["id"] = db::ii();
            if ($this->aOdecet["id"] == 0) {
                $this->errors[] = "Chyba při ukládání odpočtu!";
                return false;
            }
            header("Location: " . c_MainUrl . "zapisOdecet.php?i=&ido=" . $this->aOdecet["id"] . "&idm=" . $this->aMeridla["id"]."&status=success");
            exit;        
    }

    private function opravOdecet() {
        
        $q = "UPDATE odecty SET casodectu = ?, odecet = ?, poznamka = ?, opravil = ? WHERE id = ? AND idmeridla = ?";
        db::q($q, utils::formatDbDateTime($this->aOdecet["casodectu"]), $this->aOdecet["odecet"], $this->aOdecet["poznamka"], $this->aUser["id"], $this->aOdecet["id"], $this->aMeridla["id"]);
        //debug([$q, utils::formatDbDateTime($this->aOdecet["casodectu"]), $this->aOdecet["odecet"], $this->aOdecet["poznamka"], $this->aOdecet["id"], $this->aMeridla["id"], $this->aUser["id"]]);
        header("Location: " . c_MainUrl . "zapisOdecet.php?u=1&ido=" . $this->aOdecet["id"] . "&idm=" . $this->aMeridla["id"]."&status=success");
        exit;
    }

    public function smazOdecet() {
        $ido = request::int('ido', 'GET');
        $idm = request::int('idm', 'GET');
        $this->errors[] = $ido . ' / ' . $idm;
        if ($ido == 0 || $idm == 0) :
            $this->errors[] = "Chyba při mazání odpočtu! Chybí ID!";
            return false;
        endif;

        $q = "DELETE FROM odecty WHERE id = ? AND idmeridla = ?";
        $this->errors[] = $q;
        try {
            db::q($q, $ido, $idm);
            if (db::nr() == 0) :
                $this->errors[] = "Chyba při mazání odpočtu! Existuje záznam s tímto ID?";
                return false;
            endif;
            $this->errors[] = "Odpočet byl úspěšně smazán.";
            return true;
        } catch (Exception $e) {
            $this->errors[] = "Chyba db při mazání odpočtu!";
            return false;
        }
    }
    
    private function validujOdecet() {
        //debug([$this->aOdecet["casodectu"], utils::formatDbDateTime($this->aOdecet["casodectu"])]);
        if (empty(utils::formatDbDateTime($this->aOdecet["casodectu"]))) {
            $this->errors[] = "Neplatný datum a čas odpočtu!";
            return false;
        }

        if ($this->aOdecet["odecet"] <= 0) {
            $this->errors[] = "Neplatná hodnota odpočtu!";
            return false;
        }
        return true;
    }
    
}
