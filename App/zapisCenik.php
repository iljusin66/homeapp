<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;

require_once 'ceniky.php';

class zapisCenik extends ceniky {

    public $errors = [];
    private $aUser = [];

    function __construct($aUser) {
        $this->aUser = $aUser;
        parent::__construct($this->aUser);
        $this->nactiCenik();

        if (c_RequestPost) :
            $this->ulozCenik();
        endif;
    }

    private function nactiCenik() {
        if (c_RequestPost) :
            $this->nactiCenikPost();
        else:
            $this->nactiCenikGet();
        endif;
    }

    private function nactiCenikPost() {
        $this->aCenik["id"] = request::int('idc', 'POST');
        $this->aCenik["dodavatel"] = request::string('dodavatel', 'POST');
        $this->aCenik["poznamka"] = request::string('poznamka', 'POST');
        $this->aCenik["cenazajednotku"] = request::float('cenazajednotku', 'POST');
        $this->aCenik["odhadcenyzajednotku"] = request::float('odhadcenyzajednotku', 'POST');
        $this->aCenik["platnyod"] = request::string('platnyod', 'POST');
        $this->aCenik["platnydo"] = request::string('platnydo', 'POST');
    }

    private function nactiCenikGet() {
        $this->aCenik["id"] = request::int('idc', 'GET');
        if ($this->aCenik["id"] > 0) :
            $q = "SELECT * FROM ceniky WHERE id = ? AND idmeridla = ?";
            $this->aCenik = db::f($q, $this->aCenik["id"], $this->aMeridlo["id"]);
        endif;
    }

    private function validuj(): bool {
        if (trim($this->aCenik["dodavatel"]) === '') {
            $this->errors[] = __('Dodavatel je povinný.');
        }
        if (empty(utils::formatDbDate($this->aCenik["platnyod"]))) {
            $this->errors[] = __('Neplatné počáteční datum platnosti.');
        }
        if (!empty($this->aCenik["platnydo"]) && empty(utils::formatDbDate($this->aCenik["platnydo"]))) {
            $this->errors[] = __('Neplatné koncové datum platnosti.');
        }

        // Ověřit, že koncové datum je po počátečním
        $datOd = strtotime(utils::formatDbDate($this->aCenik["platnyod"]));
        $datDo = (!empty($this->aCenik["platnydo"])) ? strtotime(utils::formatDbDate($this->aCenik["platnydo"])) : PHP_INT_MAX;
        if ($datDo < $datOd) {
            $this->errors[] = __('Koncové datum musí být po počátečním.');
        }

        return empty($this->errors);
    }

    private function kontrolaOpravneni(): bool {
        if (empty($this->aUser) || $this->aUser['id'] == 0) :
            return false;
        endif;
        
        $role = $this->aUser['meridlaRole'][$this->aMeridlo["id"]] ?? 0;
        if (!in_array($role, ca_RoleGroup['editor'])) :
            $this->errors[] = __('Nemáte oprávnění měnit ceníky pro toto měřidlo.');
            return false;
        endif;
        return true;
    }

    private function ulozCenik(): void {
        if (!$this->kontrolaOpravneni()) { return; }
        if (!$this->validuj()) { return; }

        // Ošetřit mazání
        $idcDel = request::int('del', 'GET');
        if ($idcDel > 0) :
            $this->smazCenik();
            return;
        endif;

        if ($this->aCenik["id"] == 0) :
            $this->vytvorCenik();
        else:
            $this->opravCenik();
        endif;
    }

    private function vytvorCenik(): void {
        // Uzavřít předchozí ceník, který nemá konce
        $this->uzavriPredchoziCenik(utils::formatDbDate($this->aCenik["platnyod"]));

        $q = "INSERT INTO ceniky (idmeridla, dodavatel, poznamka, cenazajednotku, odhadcenyzajednotku, platnyod, platnydo) VALUES (?, ?, ?, ?, ?, ?, ?)";
        db::q($q, [
            $this->aMeridlo["id"],
            $this->aCenik["dodavatel"],
            $this->aCenik["poznamka"],
            $this->aCenik["cenazajednotku"],
            $this->aCenik["odhadcenyzajednotku"],
            utils::formatDbDate($this->aCenik["platnyod"]),
            null // Nový ceník nemá konce
        ]);

        $this->aCenik["id"] = db::ii();
        if ($this->aCenik["id"] == 0) {
            $this->errors[] = __('Chyba při ukládání ceníku.');
            return;
        }

        header("Location: " . c_MainUrl . "zapisMeridlo.php?idm=" . $this->aMeridlo["id"] . "&status=success");
        exit;
    }

    private function opravCenik(): void {
        // Získat starý platnyod pro porovnání
        $q = "SELECT platnyod FROM ceniky WHERE id = ?";
        $stary = db::f($q, $this->aCenik["id"]);
        $staryPlatnyOd = $stary['platnyod'] ?? '';
        $novyPlatnyOd = utils::formatDbDate($this->aCenik["platnyod"]);

        // Pokud se změnil platnyod, ošetřit předchozí ceník
        if ($staryPlatnyOd !== $novyPlatnyOd) :
            // Otevřít ceník, který měl platnydo = (starý platnyod - 1 den)
            $datumPredStaryho = date('Y-m-d', strtotime($staryPlatnyOd . ' -1 day'));
            db::q("UPDATE ceniky SET platnydo = NULL WHERE idmeridla = ? AND platnydo = ?", 
                $this->aMeridlo["id"], $datumPredStaryho);

            // Uzavřít ceník, který by měl být před tímto na novém datu
            $this->uzavriPredchoziCenik($novyPlatnyOd);
        endif;

        $q = "UPDATE ceniky SET dodavatel = ?, poznamka = ?, cenazajednotku = ?, odhadcenyzajednotku = ?, platnyod = ? WHERE id = ? AND idmeridla = ?";
        db::q($q, [
            $this->aCenik["dodavatel"],
            $this->aCenik["poznamka"],
            $this->aCenik["cenazajednotku"],
            $this->aCenik["odhadcenyzajednotku"],
            $novyPlatnyOd,
            $this->aCenik["id"],
            $this->aMeridlo["id"]
        ]);

        header("Location: " . c_MainUrl . "zapisMeridlo.php?idm=" . $this->aMeridlo["id"] . "&status=success");
        exit;
    }

    /**
     * Uzavře ceník, který neměl konce a měl být nahrazen novým
     * Nastaví mu platnydo na den před novým ceníkem
     */
    private function uzavriPredchoziCenik(string $novyPlatnyOd): void {
        if (empty($novyPlatnyOd)) {
            return;
        }

        $q = "SELECT id FROM ceniky WHERE idmeridla = ? AND platnyod < ? AND platnydo IS NULL ORDER BY platnyod DESC LIMIT 1";
        $predchozi = db::f($q, $this->aMeridlo["id"], $novyPlatnyOd);

        if (!empty($predchozi) && $predchozi['id'] > 0) :
            $datumPred = date('Y-m-d', strtotime($novyPlatnyOd . ' -1 day'));
            db::q("UPDATE ceniky SET platnydo = ? WHERE id = ?", $datumPred, $predchozi['id']);
        endif;
    }

    /**
     * Smaže ceník a otevře jeho předchůdce (vrátí mu platnydo na NULL)
     */
    private function smazCenik(): void {
        $idc = request::int('del', 'GET');
        
        $q = "SELECT platnyod FROM ceniky WHERE id = ? AND idmeridla = ?";
        $cenik = db::f($q, $idc, $this->aMeridlo["id"]);

        if (empty($cenik) || $cenik['id'] == 0) :
            $this->errors[] = __('Ceník nenalezen.');
            return;
        endif;

        // Smazat ceník
        db::q("DELETE FROM ceniky WHERE id = ? AND idmeridla = ?", $idc, $this->aMeridlo["id"]);

        // Otevřít předchozí ceník (vrátit mu platnydo na NULL)
        $datumPred = date('Y-m-d', strtotime($cenik['platnyod'] . ' -1 day'));
        db::q("UPDATE ceniky SET platnydo = NULL WHERE idmeridla = ? AND platnydo = ?", 
            $this->aMeridlo["id"], $datumPred);

        header("Location: " . c_MainUrl . "zapisMeridlo.php?idm=" . $this->aMeridlo["id"] . "&status=success");
        exit;
    }
}
