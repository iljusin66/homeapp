<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;

require_once 'autoload.php';

new config();




class odecet extends meridla{

    private static $initialized = false;
    private $aUser = [];
    public $aOdecty = [];
    public $prumernaSpotreba = [];

    function __construct($aUser = []) {
        $this->aUser = $aUser;
        if (!self::$initialized) {
            parent::__construct();
            self::$initialized = true;
        }
        
        
    }

    private function nactiOdecty() {
        $q = 'SELECT o.*, z.nazev, r.role, mj.jednotka, ui.username AS userZadal, uu.username AS userOpravil FROM odecet_meridla AS o
            JOIN meridla AS z ON z.id = o.idmeridla AND z.id = ?
            JOIN meridla2users AS zu ON z.id = zu.idmeridla AND zu.iduser = ?
            JOIN role AS r ON r.id = zu.idrole
            JOIN cis_merne_jednotky AS mj ON mj.id = z.idjednotky
            JOIN users AS ui ON ui.id = o.zadal
            LEFT JOIN users AS uu ON uu.id = o.opravil
            ORDER BY o.casodpoctu ASC';
            $rows = db::fa($q, [$this->aMeridla['id'], $this->aUser['id']]);
            foreach ($rows as $row) :
                $this->aOdecty[] = $row;
            endforeach;
        $this->spocitejPrumernouSpotrebu();
    }

    public function spocitejPrumernouSpotrebu($rok = 0) {
        if ($rok == 0) $rok = date('Y');
        if ($this->aOdecty == []) : return 0; endif;

        $minHours = time() / 3600;
        $maxHours = 0;
        $minOdecet = 0;
        $maxOdecet = 0;
        $predchoziOdecet = 0;
        $prechoziHours = 0;
        foreach ($this->aOdecty as $key => $aOdecet) :
            if (date('Y', strtotime($aOdecet['casodpoctu'])) != $rok) :
                continue;
            endif; 
            $unixHours = strtotime($aOdecet['casodpoctu']) / 3600;
            $minHours = min($minHours, $unixHours);
            $maxHours = max($maxHours, $unixHours);
            $minOdecet = ($minOdecet==0) ? $aOdecet['odecet'] : min($minOdecet, $aOdecet['odecet']);
            $maxOdecet = max($maxOdecet, $aOdecet['odecet']);
            if ($maxOdecet == $minOdecet) :
                $this->aOdecty[$key]['spotrebaHod'] = $this->aOdecty[$key]['spotrebaDen'] = 0;
                $predchoziOdecet = $aOdecet['odecet'];
                $prechoziHours = $unixHours;
                continue;
            endif;
            //debug(['maxOdecet' => $maxOdecet, 'minOdecet' => $minOdecet, 'maxHours' => $maxHours, 'minHours' => $minHours]);
            $this->aOdecty[$key]['spotrebaHod'] = ($maxOdecet - $predchoziOdecet) / ($maxHours - $prechoziHours);
            $this->aOdecty[$key]['spotrebaDen'] = ($maxOdecet - $predchoziOdecet) / (($maxHours - $prechoziHours) / 24);
            $predchoziOdecet = $aOdecet['odecet'];
            $prechoziHours = $unixHours;
        endforeach;
        $spotreba = $maxOdecet - $minOdecet;
        $pocetHodin = $maxHours - $minHours;
        if ($pocetHodin == 0) return 0; 
        $this->prumernaSpotreba['hod'] = $spotreba / $pocetHodin;
        $this->prumernaSpotreba['den'] = $spotreba / ($pocetHodin / 24);
        $this->aOdecty = array_reverse($this->aOdecty);
    }

    public function nactiSeznamOdectu() {
        $this->nactiOdecty();
        if (empty($this->aOdecty)) return false;
        return $this->aOdecty;
    }
}
