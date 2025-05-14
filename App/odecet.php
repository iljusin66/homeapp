<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;

require_once 'autoload.php';

new config();




class odecet extends zarizeni{

    private static $initialized = false;
    private $aUser = [];
    public $aOdecty = [];

    function __construct($aUser = []) {
        $this->aUser = $aUser;
        if (!self::$initialized) {
            parent::__construct();
            self::$initialized = true;
        }
        
    }

    private function nactiOdecty() {
        $q = 'SELECT o.*, z.nazev, r.role, mj.jednotka, ui.username AS userZadal, uu.username AS userOpravil FROM odecet_zarizeni AS o
            JOIN zarizeni AS z ON z.id = o.idzarizeni AND z.id = ?
            JOIN zarizeni2users AS zu ON z.id = zu.idzarizeni AND zu.iduser = ?
            JOIN role AS r ON r.id = zu.idrole
            JOIN cis_merne_jednotky AS mj ON mj.id = z.idjednotky
            JOIN users AS ui ON ui.id = o.zadal
            LEFT JOIN users AS uu ON uu.id = o.opravil
            ORDER BY o.casodpoctu DESC';
            $rows = db::fa($q, [$this->aZarizeni['id'], $this->aUser['id']]);
            foreach ($rows as $row) :
                $this->aOdecty[] = $row;
            endforeach;
    }

    public function spocitejPrumernouSpotrebu($rok = 0) {
        if ($rok == 0) $rok = date('Y');
        if ($this->aOdecty == []) : return 0; endif;

        $minHours = time() / 3600;
        $maxHours = 0;
        $minOdecet = 0;
        $maxOdecet = 0;
        foreach ($this->aOdecty as $aOdecet) :
            if (date('Y', strtotime($aOdecet['casodpoctu'])) != $rok) :
                continue;
            endif; 
            $unixHours = strtotime($aOdecet['casodpoctu']) / 3600;
            $minHours = min($minHours, $unixHours);
            $maxHours = max($maxHours, $unixHours);
            $minOdecet = ($minOdecet=0) ? $aOdecet['odecet'] : min($minOdecet, $aOdecet['odecet']);
            $maxOdecet = max($maxOdecet, $aOdecet['odecet']);
        endforeach;
        $spotreba = $maxOdecet - $minOdecet;
        $pocetHodin = $maxHours - $minHours;
        if ($pocetHodin == 0) return 0; 
        $prumernaSpotreba = $spotreba / $pocetHodin;
        return $prumernaSpotreba;
    }

    public function nactiSeznamOdectu() {
        $this->nactiOdecty();
        if (empty($this->aOdecty)) return false;
        return $this->aOdecty;
    }
}
