<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;


require_once 'meridla.php';



class zapisMeridlo extends meridla {

    public $errors = [];
    private $aUser = [];

    function __construct($aUser) {
        $this->aUser = $aUser;
        parent::__construct($this->aUser);

        if (c_RequestPost) {
            $this->nactiZPost();
            $this->ulozMeridlo();
        }
    }

    private function nactiZPost(): void {
        $this->aMeridlo['id'] = request::int('idm', 'POST');
        $this->aMeridlo['nazev'] = request::string('nazev', 'POST');
        $this->aMeridlo['idjednotky'] = request::int('idjednotky', 'POST');
        $this->aMeridlo['poznamka'] = request::string('poznamka', 'POST');
    }

    private function validuj(): bool {
        if (trim($this->aMeridlo['nazev']) === '') {
            $this->errors[] = __('Název je povinný.');
        }
        if ($this->aMeridlo['idjednotky'] <= 0) {
            $this->errors[] = __('Vyberte měrnou jednotku.');
        }
        return empty($this->errors);
    }

    private function kontrolaOpravneni(string $ocekavanaRole = 'editor', int $idMeridla = 0): bool {
        if ($idMeridla === 0) {
            // nový záznam – stačí, že je uživatel přihlášen
            return ($this->aUser['id'] ?? 0) > 0;
        }
        $role = $this->aUser['meridlaRole'][$idMeridla] ?? 0;
        if (!in_array($role, ca_RoleGroup[$ocekavanaRole])) {
            $this->errors[] = __('Nemáte oprávnění měnit toto měřidlo.');
            return false;
        }
        return true;
    }

    private function ulozMeridlo(): void {
        if (!$this->validuj()) { return; }

        if ($this->aMeridlo['id'] > 0) {
            $this->opravMeridlo();
        } else {
            $this->vytvorMeridlo();
        }
    }

    private function vytvorMeridlo(): void {
        if (!$this->kontrolaOpravneni('writer', 0)) { return; }

        $q = "INSERT INTO meridla (idadmin, nazev, idjednotky, poznamka, aktivni) VALUES (?, ?, ?, ?, 1)";
        db::q($q, $this->aUser['id'], $this->aMeridlo['nazev'], $this->aMeridlo['idjednotky'], $this->aMeridlo['poznamka']);
        $this->aMeridlo['id'] = db::ii();
        if ($this->aMeridlo['id'] == 0) {
            $this->errors[] = __('Chyba při ukládání měřidla.');
            return;
        }

        // Přidělit zakladatele jako admina měřidla
        $q = "INSERT INTO meridla2users (iduser, idmeridla, idrole) VALUES (?, ?, ?)";
        db::q($q, $this->aUser['id'], $this->aMeridlo['id'], ca_Role['admin']);

        header('Location: ' . c_MainUrl . 'dashboard.php?status=success');
        exit;
    }

    private function opravMeridlo(): void {
        if (!$this->kontrolaOpravneni('editor', $this->aMeridlo['id'])) { return; }

        $q = "UPDATE meridla SET nazev = ?, idjednotky = ?, poznamka = ? WHERE id = ?";
        db::q($q, $this->aMeridlo['nazev'], $this->aMeridlo['idjednotky'], $this->aMeridlo['poznamka'], $this->aMeridlo['id']);

        header('Location: ' . c_MainUrl . 'zapisMeridlo.php?idm=' . $this->aMeridlo['id'] . '&status=success');
        exit;
    }
}
