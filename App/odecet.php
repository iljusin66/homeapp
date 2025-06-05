<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;

require_once 'autoload.php';

new config();

/*
* Třída pro práci s odečty energií
* @author Ivan Latečka
* @version 1.0
*/
class odecet extends meridla{

    private static $initialized = false;
    private $aUser = [];
    public $aOdecty = [];
    public $celkovaSpotreba = 0;
    public $celkoveNaklady = 0;
    public $prumernaSpotrebaHodina = 0;
    public $prumernaSpotrebaDen = 0;
    public $zacatekObdobiOdectu = '';
    public $konecObdobiOdectu = null;

    
    function __construct($aUser = []) {
        
        $this->aUser = $aUser;
        
        if (!self::$initialized) {
            parent::__construct($this->aUser);
            self::$initialized = true;
        }
        $this->konecObdobiOdectu = date('Y-m-d H:i:s');
        $this->posledniObdobiOdectu();
    }

    /*
    * Načte poslední období odečtu
    * @return void
    */
    private function posledniObdobiOdectu() {
        if (
            (empty($this->aMeridlo) || $this->aMeridlo['id'] == 0)
            || (empty($this->aUser) || $this->aUser['id'] == 0)
            ) :
            $this->aOdecty = [];
            return;
        endif;
        $q = 'SELECT s.casodectu AS posledniObdobiOdectu FROM v_spotrebascenami AS s
            JOIN meridla2users AS mu ON mu.idmeridla = s.idmeridla AND mu.iduser = ?
            WHERE s.idmeridla = ? AND zacatekobdobi = 1 ORDER BY s.casodectu DESC LIMIT 0, 1';
            $row = db::f($q, [$this->aUser['id'], $this->aMeridlo['id']]);

        //Paklize nebyl nalezen zadny rozhodny odečet, vezmeme poslední odečet
        // a pokud ani ten neexistuje, nastavíme období na aktuální čas
        if (empty($row['posledniObdobiOdectu'])) :
            $q = 'SELECT MIN(s.casodectu) AS posledniObdobiOdectu FROM v_spotrebascenami AS s
            JOIN meridla2users AS mu ON mu.idmeridla = s.idmeridla AND mu.iduser = ?
            WHERE s.idmeridla = ?';
            $row = db::f($q, [$this->aUser['id'], $this->aMeridlo['id']]);
            //Zaznam nalezen nebyl, nastavíme období na aktuální čas
            if (empty($row['posledniObdobiOdectu'])) :
                $this->zacatekObdobiOdectu = date('Y-m-d H:i:s');
            else:
               $this->zacatekObdobiOdectu = $row['posledniObdobiOdectu'];
            endif;
        else :
            $this->zacatekObdobiOdectu = $row['posledniObdobiOdectu'];
        endif;
    }

    /*
        * Načte odečty pro dané období
        * @return void
        */
    private function nactiOdectyObdobi() {
        if (
            (empty($this->aMeridlo) || $this->aMeridlo['id'] == 0)
            || (empty($this->aUser) || $this->aUser['id'] == 0)
            ) :
            $this->aOdecty = [];
            return;
        endif;
        
        // Získání odečtů pro dané období
        $q = 'CALL SpotrebaOd(?, ?, ?, ?);';            
            $rows = db::fa($q, [$this->aUser['id'], $this->aMeridlo['id'], $this->zacatekObdobiOdectu, $this->konecObdobiOdectu]);
            
            foreach ($rows as $row) :
                $this->aOdecty[] = $row;
            endforeach;
            // Přidání rozdílu spotřeby do pole
            for ($i = 0; $i < count($this->aOdecty) - 1; $i++) {
                $this->aOdecty[$i]['rozdilSpotreby'] = $this->aOdecty[$i]['prumernaSpotrebaDen'] - $this->aOdecty[$i + 1]['prumernaSpotrebaDen'];
            }
        $this->spocitejPrumernouSpotrebu();
    }

    /*
    * Vypočítá průměrnou spotřebu a náklady za dané období
    * @return void
    */
    public function spocitejPrumernouSpotrebu() {
        $this->celkovaSpotreba = array_sum(array_column($this->aOdecty, 'spotreba'));
        $this->celkoveNaklady = array_sum(array_column($this->aOdecty, 'naklady'));
        $minCas = min(array_column($this->aOdecty, 'casodectu'));
        $maxCas = max(array_column($this->aOdecty, 'casodectu'));
        $pocetHodin = (strtotime($maxCas) - strtotime($minCas)) / 3600;
        if ($this->celkovaSpotreba == 0 || $pocetHodin == 0):
            $this->prumernaSpotrebaHodina = 0;
            $this->prumernaSpotrebaDen = 0;
            return;
        endif;
        $this->prumernaSpotrebaHodina = $this->celkovaSpotreba / $pocetHodin;
        $this->prumernaSpotrebaDen = $this->celkovaSpotreba / ($pocetHodin / 24);
    }

    /*
    * Načte seznam odečtů pro dané období
    * @return array|false
    */
    public function nactiSeznamOdectu() {
        $this->nactiOdectyObdobi();
        if (empty($this->aOdecty)) return false;
        return $this->aOdecty;
    }
}
