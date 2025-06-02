<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;

require_once 'autoload.php';

new config();

/*
 * Třída pro práci s odečty měřidel
 * 
 * @author Ivan La.
 * @version 1.0
 * @package App
 */
class odecet extends meridla{

    private static $initialized = false;
    private $aUser = [];
    public $aOdecty = [];
    public $celkovaSpotreba = 0;
    public $celkoveNaklady = 0;
    public $prumernaSpotrebaHodina = 0;
    public $prumernaSpotrebaDen = 0;

    function __construct($aUser = []) {
        
        $this->aUser = $aUser;
        
        if (!self::$initialized) {
            parent::__construct($this->aUser);
            self::$initialized = true;
        }
    }

    private function nactiOdectyRok() {
        $q = 'SELECT s.* FROM v_spotrebascenami AS s
            JOIN meridla2users AS mu ON mu.idmeridla = s.idmeridla AND mu.iduser = ?
            JOIN role AS r ON r.id = mu.idrole
            WHERE s.idmeridla = ? AND YEAR(s.casodectu) = ? 
            ORDER BY s.casodectu DESC';
            $rows = db::fa($q, [$this->aUser['id'], $this->aMeridlo['id'], $this->rokOdectu]);
            foreach ($rows as $row) :
                $this->aOdecty[] = $row;
            endforeach;
        $this->spocitejPrumernouSpotrebu();
    }

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

    public function nactiSeznamOdectu() {
        $this->nactiOdectyRok();
        if (empty($this->aOdecty)) return false;
        return $this->aOdecty;
    }
}
