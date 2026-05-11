<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;

require_once 'autoload.php';
require_once 'autoOdecty.php';

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
    public $souhrnPredchoziObdobi = [];

    
    function __construct($aUser = []) {
        
        $this->aUser = $aUser;
        
        if (!self::$initialized) {
            parent::__construct($this->aUser);
            // Automatické vytvoření počátečních odečtů při změně ceníků
            $oAuto = new autoOdecty($this->aUser);
            $oAuto->kontrolaAVytvoreniAutoOdectu($this->aMeridlo['id']);
            self::$initialized = true;
        }
        $this->konecObdobiOdectu = date('Y-m-d H:i:s');
        $this->souhrnPredchoziObdobi = [
            'dostupne' => false,
            'od' => '',
            'do' => '',
            'celkovaSpotreba' => 0,
            'celkoveNaklady' => 0,
            'celkoveNakiadyOdhad' => 0,
            'prumernaSpotrebaDen' => 0,
            'cenazajednotku' => 0,
            'odhadcenyzajednotku' => 0,
        ];
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
            
            // Načtení ceníků pro přepočet nákladů s odhadovanou cenou
            $qCeniky = 'SELECT cenazajednotku, odhadcenyzajednotku, platnyod, platnydo FROM ceniky WHERE idmeridla = ? ORDER BY platnyod DESC';
            $aCeniky = db::fa($qCeniky, [$this->aMeridlo['id']]);
            
            foreach ($rows as $row) :
                $this->aOdecty[] = $row;
                
                // Přepočet nákladů: pokud není skutečná cena, použij odhad
                if (empty($this->aOdecty[count($this->aOdecty) - 1]['cenazajednotku']) || $this->aOdecty[count($this->aOdecty) - 1]['cenazajednotku'] == 0) {
                    $rowDate = substr($row['casodectu'], 0, 10);
                    $odhadPouze = 0;
                    
                    // Najdeme správný ceník pro tento odečet
                    foreach ($aCeniky as $cenik) {
                        $cOd = $cenik['platnyod'];
                        $cDo = !empty($cenik['platnydo']) ? $cenik['platnydo'] : '9999-12-31';
                        if ($rowDate >= $cOd && $rowDate <= $cDo) {
                            $odhadPouze = $cenik['odhadcenyzajednotku'];
                            break;
                        }
                    }
                    
                    // Přepočítej náklady s odhadovanou cenou
                    if ($odhadPouze > 0) {
                        $this->aOdecty[count($this->aOdecty) - 1]['naklady'] = $row['spotreba'] * $odhadPouze;
                    }
                }
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
        if (empty($this->aOdecty)) {
            $this->celkovaSpotreba = 0;
            $this->celkoveNaklady = 0;
            $this->prumernaSpotrebaHodina = 0;
            $this->prumernaSpotrebaDen = 0;
            return;
        }

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
    * Načte hranice předchozího období dle platnosti ceníku
    * @return array
    */
    private function vratPredchoziCenikoveObdobi() {
        if (empty($this->aMeridlo) || utils::fixInt($this->aMeridlo['id']) == 0) {
            return [];
        }

        $q = 'SELECT id, platnyod, platnydo
            FROM ceniky
            WHERE idmeridla = ?
              AND platnyod <= CURDATE()
              AND (platnydo IS NULL OR platnydo >= CURDATE())
            ORDER BY platnyod DESC
            LIMIT 0, 1';
        $aAktualniCenik = db::f($q, [$this->aMeridlo['id']]);

        if (empty($aAktualniCenik)) {
            $q = 'SELECT id, platnyod, platnydo
                FROM ceniky
                WHERE idmeridla = ?
                ORDER BY platnyod DESC
                LIMIT 0, 1';
            $aAktualniCenik = db::f($q, [$this->aMeridlo['id']]);
        }

        if (empty($aAktualniCenik)) {
            return [];
        }

        $q = 'SELECT id, platnyod, platnydo
            FROM ceniky
            WHERE idmeridla = ?
              AND platnyod < ?
            ORDER BY platnyod DESC
            LIMIT 0, 1';
        $aPredchoziCenik = db::f($q, [$this->aMeridlo['id'], $aAktualniCenik['platnyod']]);

        if (empty($aPredchoziCenik)) {
            return [];
        }

        $datOd = $aPredchoziCenik['platnyod'].' 00:00:00';
        if (!empty($aPredchoziCenik['platnydo'])) {
            $datDo = date('Y-m-d 00:00:00', strtotime($aPredchoziCenik['platnydo'].' +1 day'));
        } else {
            $datDo = $aAktualniCenik['platnyod'].' 00:00:00';
        }

        if (strtotime($datDo) <= strtotime($datOd)) {
            return [];
        }

        return [
            'od' => $datOd,
            'do' => $datDo,
            'odDatum' => $aPredchoziCenik['platnyod'],
            'doDatum' => $aPredchoziCenik['platnydo'],
        ];
    }

    /*
    * Načte souhrn minulého ceníkového období s oběma cenami (skutečnou a odhadovanou)
    * @return void
    */
    private function nactiSouhrnPredchozihoObdobi() {
        $aObdobi = $this->vratPredchoziCenikoveObdobi();
        if (empty($aObdobi)) {
            return;
        }

        $q = 'CALL SpotrebaOd(?, ?, ?, ?);';
        $aRows = db::fa($q, [$this->aUser['id'], $this->aMeridlo['id'], $aObdobi['od'], $aObdobi['do']]);

        $celkovaSpotreba = array_sum(array_column($aRows, 'spotreba'));
        $celkoveNaklady = array_sum(array_column($aRows, 'naklady'));
        $celkoveNakiadyOdhad = 0;
        $prumernaSpotrebaDen = 0;
        $cenazajednotku = 0;
        $odhadcenyzajednotku = 0;

        if (!empty($aRows)) {
            $minCas = min(array_column($aRows, 'casodectu'));
            $maxCas = max(array_column($aRows, 'casodectu'));
            $pocetHodin = (strtotime($maxCas) - strtotime($minCas)) / 3600;
            if ($celkovaSpotreba > 0 && $pocetHodin > 0) {
                $prumernaSpotrebaDen = $celkovaSpotreba / ($pocetHodin / 24);
            }

            // Načtení ceníků za dané období pro výpočet odhadovaných nákladů
            $qCeniky = 'SELECT id, cenazajednotku, odhadcenyzajednotku, platnyod, platnydo
                FROM ceniky
                WHERE idmeridla = ?
                  AND platnyod <= ?
                  AND (platnydo IS NULL OR platnydo >= ?)
                ORDER BY platnyod DESC';
            $aCeniky = db::fa($qCeniky, [$this->aMeridlo['id'], $aObdobi['do'], $aObdobi['od']]);

            // Mapování datumů na ceny
            $aCenByDate = [];
            foreach ($aCeniky as $cenik) {
                $aCenByDate[] = [
                    'cenazajednotku' => $cenik['cenazajednotku'],
                    'odhadcenyzajednotku' => $cenik['odhadcenyzajednotku'],
                    'platnyod' => $cenik['platnyod'],
                    'platnydo' => $cenik['platnydo'],
                ];
            }

            // Výpočet nákladů s oběma cenami
            foreach ($aRows as $row) {
                $rowDate = substr($row['casodectu'], 0, 10);
                $cenaPouze = 0;
                $odhadPouze = 0;

                // Najdeme správný ceník pro tento odečet
                foreach ($aCenByDate as $cen) {
                    $cOd = $cen['platnyod'];
                    $cDo = !empty($cen['platnydo']) ? $cen['platnydo'] : '9999-12-31';
                    if ($rowDate >= $cOd && $rowDate <= $cDo) {
                        $cenaPouze = $cen['cenazajednotku'];
                        $odhadPouze = $cen['odhadcenyzajednotku'];
                        break;
                    }
                }

                // Odhadované náklady: pokud je cena 0, používáme odhad; jinak skutečnou cenu
                if ($cenaPouze > 0) {
                    $celkoveNakiadyOdhad += $row['spotreba'] * $odhadPouze;
                } else {
                    $celkoveNakiadyOdhad += $row['spotreba'] * $odhadPouze;
                }
            }

            // Získání ceny z ceníku, který byl platný na začátku tohoto období
            // Seřazení je DESC, takže hledáme první (nejnovější) ceník s platnyod <= od
            if (!empty($aCenByDate)) {
                $cenikNaZacatku = null;
                foreach ($aCenByDate as $cen) {
                    if ($cen['platnyod'] <= $aObdobi['od']) {
                        $cenikNaZacatku = $cen;
                        break; // Přestáváme, protože jsme v DESC a toto je nejnovější <= od
                    }
                }
                if (!empty($cenikNaZacatku)) {
                    $cenazajednotku = $cenikNaZacatku['cenazajednotku'];
                    $odhadcenyzajednotku = $cenikNaZacatku['odhadcenyzajednotku'];
                }
            }
        }

        $this->souhrnPredchoziObdobi = [
            'dostupne' => true,
            'od' => $aObdobi['odDatum'],
            'do' => $aObdobi['doDatum'],
            'celkovaSpotreba' => $celkovaSpotreba,
            'celkoveNaklady' => $celkoveNaklady,
            'celkoveNakiadyOdhad' => $celkoveNakiadyOdhad,
            'prumernaSpotrebaDen' => $prumernaSpotrebaDen,
            'cenazajednotku' => $cenazajednotku,
            'odhadcenyzajednotku' => $odhadcenyzajednotku,
        ];
    }

    /*
    * Načte seznam odečtů pro dané období
    * @return array|false
    */
    public function nactiSeznamOdectu() {
        $this->nactiOdectyObdobi();
        $this->nactiSouhrnPredchozihoObdobi();
        if (empty($this->aOdecty)) return false;
        return $this->aOdecty;
    }
}
