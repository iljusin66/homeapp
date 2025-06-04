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
            $this->smazOdecet();
            $this->nactiOdecetGet();
        endif;
        

    }

    private function nactiOdecetPost() {
        $this->aOdecet["id"] = request::int('ido', 'POST');

    }

    private function nactiOdecetGet() {
        $this->aOdecet["id"] = request::int('ido', 'GET');
        $q = "SELECT * FROM odecty WHERE id = ? AND idmeridla = ?";
        $this->aOdecet = db::f($q, $this->aOdecet["id"], $this->aMeridlo["id"]);
    }

    private function ulozOdecet() {
        $this->aOdecet["casodectu"] = request::string('casodectu', 'POST');
        $this->aOdecet["odecet"] = request::float('odecet', 'POST');
        $this->aOdecet["poznamka"] = request::string('poznamka', 'POST');
        $this->aOdecet["zacatekobdobi"] = (request::int('zacatekobdobi', 'POST')!=1) ? 0 : 1;

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

    /**
     * Kontrola oprávnění uživatele k zápisu/úpravě odečtu
     * @param string $ocekavanaRole Role, kterou uživatel minimálně musí mít (writer/editor)
     * @return bool
     */
    private function kontrolaOpravneni($ocekavanaRole = 'writer') {
        if (!in_array($this->aUser["meridlaRole"][$this->aMeridlo["id"]], ca_RoleGroup[$ocekavanaRole])) :
            if ($ocekavanaRole == 'writer') :
                $this->errors[] = "Nemáte oprávnění provádět zápis odečtu pro toto měřidlo!";
            elseif ($ocekavanaRole == 'editor') :
                $this->errors[] = "Nemáte oprávnění provádět opravu odečtu pro toto měřidlo!";
            else:
                $this->errors[] = "Nemáte oprávnění k tomuto měřidlu!";
            endif;
            return false;
        endif;
        return true;
    }

    /**
     * Zapíše nový odečet do databáze
     * @return void|false
     */
    private function zapisNovyOdecet() {
        //Kontrola, jestli uzivatel ma opravneni zapisovat odpocty
        //Pokud nema opravneni, tak vracime false a vypiseme chybu
        if (!$this->kontrolaOpravneni('writer')) : return false; endif;


        $q = "INSERT INTO odecty (idmeridla, casodectu, odecet, poznamka, zadal, zacatekobdobi) VALUES (?, ?, ?, ?, ?, ?)";
        db::q($q, [$this->aMeridlo["id"]
                    , utils::formatDbDateTime($this->aOdecet["casodectu"])
                    , $this->aOdecet["odecet"], $this->aOdecet["poznamka"]
                    , $this->aUser["id"]
                    , $this->aOdecet["zacatekobdobi"]
                ]);
    
        $this->aOdecet["id"] = db::ii();
        if ($this->aOdecet["id"] == 0) {
            $this->errors[] = "Chyba při ukládání odpočtu!";
            return false;
        }

        header("Location: " . c_MainUrl . "seznamOdectu.php?idm=" . $this->aMeridlo["id"]."&status=success");
        exit;        
    }

    /**
     * Opraví odečet v databázi
     * @return void|bool
     */
    private function opravOdecet() {
        //Kontrola, jestli uzivatel ma opravneni upravovat odpocty
        //Pokud nema opravneni, tak vracime false a vypiseme chybu
        if (!$this->kontrolaOpravneni('editor')) : return false; endif;
        
        $q = "UPDATE odecty SET casodectu = ?, odecet = ?, poznamka = ?, opravil = ?, zacatekobdobi = ? WHERE id = ? AND idmeridla = ?";
        db::q($q, [
            utils::formatDbDateTime($this->aOdecet["casodectu"])
            , $this->aOdecet["odecet"]
            , $this->aOdecet["poznamka"]
            , $this->aUser["id"]
            , $this->aOdecet["zacatekobdobi"]
            , $this->aOdecet["id"]
            , $this->aMeridlo["id"]]);
        //header("Location: " . c_MainUrl . "zapisOdecet.php?u=1&ido=" . $this->aOdecet["id"] . "&idm=" . $this->aMeridlo["id"]."&status=success");
        header("Location: " . c_MainUrl . "seznamOdectu.php?idm=" . $this->aMeridlo["id"]."&status=success");
        exit;
    }

    /**
     * Smaže odečet z databáze
     * @return void|bool 
     */
    public function smazOdecet() {
        $ido = request::int('del', 'GET');        
        $this->aMeridlo["id"] = request::int('idm', 'GET');
        
        //Kdyz neni pozadavek na mazani, tak nic nedelame
        if ($ido==0) : return; endif;
        $this->aOdecet["id"] = $ido;

        //Kontrola, jestli uzivatel ma opravneni mazat odpocty
        //Pokud nema opravneni, tak vracime false a vypiseme chybu
        if (!$this->kontrolaOpravneni('editor')) : return false; endif;

        
        //Smazani odpoctu
        $q = "DELETE FROM odecty WHERE id = ? AND idmeridla = ?";
        $this->errors[] = $q;
        try {
            db::q($q, $ido, $this->aMeridlo["id"]);
            if (db::nr() == 0) :
                $this->errors[] = "Chyba při mazání odpočtu! Existuje záznam s tímto ID?";
                return false;
            endif;
            header("Location: " . c_MainUrl . "seznamOdectu.php?idm=" . $this->aMeridlo["id"]."&status=success&akce=delete");
            exit;
        } catch (Exception $e) {
            $this->errors[] = "Chyba db při mazání odpočtu!";
            return false;
        }
    }
    
    private function validujOdecet() {

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
