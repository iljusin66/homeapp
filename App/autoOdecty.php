<?php
set_time_limit(600);

use Latecka\Utils\utils;
use Latecka\Utils\db;

/**
 * Třída pro automatické vytváření počátečních odečtů při změně ceníků
 * Řeší problém výpočtu spotřeby přes několik cen
 * 
 * @author Ivan Latečka
 * @version 1.0
 */
class autoOdecty {

    private $aUser = [];
    private $aOptimizedPeriods = [];

    function __construct($aUser = []) {
        $this->aUser = $aUser;
    }

    /**
     * Zkontroluje a vytvoří chybějící počáteční odečty pro nové ceníky
     * Spouští se při prvním odečtu v novém období
     * 
     * @param int $idMeridla ID měřidla
     * @return bool true pokud byly vytvořeny nové odečty
     */
    public function kontrolaAVytvoreniAutoOdectu(int $idMeridla): bool {
        if (empty($this->aUser) || $this->aUser['id'] == 0) {
            return false;
        }

        // 1. Najít poslední odečet se zacatekobdobi = 1
        $q = "SELECT id, casodectu, odecet FROM odecty 
               WHERE idmeridla = ? AND zacatekobdobi = 1 
               ORDER BY casodectu DESC LIMIT 1";
        $posledniOdecet = db::f($q, $idMeridla);

        if (empty($posledniOdecet) || $posledniOdecet['id'] == 0) {
            // Žádný odečet se zacatekobdobi, nic neděláme
            return false;
        }

        $casPoslednihoOdectu = strtotime($posledniOdecet['casodectu']);

        // 2. Najít ceníky s platnyod po posledním počátečním odečtu
        $q = "SELECT id, platnyod FROM ceniky 
               WHERE idmeridla = ? AND platnyod > ? 
               ORDER BY platnyod ASC";
        $noveXeníky = db::fa($q, $idMeridla, date('Y-m-d', $casPoslednihoOdectu));

        if (empty($noveXeníky)) {
            return false;
        }

        $vytvorenoOdectu = false;

        foreach ($noveXeníky as $cenik) {
            $casZacatkuCeniku = strtotime($cenik['platnyod']);

            // 3. Ověřit, zda existuje počáteční odečet po platnyod ceníku
            $q = "SELECT id FROM odecty 
                   WHERE idmeridla = ? AND casodectu >= ? AND zacatekobdobi = 1";
            $existujeOdecet = db::f($q, $idMeridla, $cenik['platnyod']);

            if (!empty($existujeOdecet) && $existujeOdecet['id'] > 0) {
                // Už existuje počáteční odečet pro tento ceník, pokračujeme
                continue;
            }

            // 4. Vypočítat průměrnou denní spotřebu a vytvořit automatický odečet
            if ($this->vytvorAutoOdecet($idMeridla, $posledniOdecet, $cenik)) {
                $vytvorenoOdectu = true;
            }
        }

        return $vytvorenoOdectu;
    }

    /**
     * Vytvoří automatický odečet na začátku období (po změně ceníku)
     * 
     * @param int $idMeridla ID měřidla
     * @param array $posledniOdecet Poslední odečet se zacatekobdobi = 1
     * @param array $cenik Nový ceník s platnyod
     * @return bool true pokud byl úspěšně vytvořen
     */
    private function vytvorAutoOdecet(int $idMeridla, array $posledniOdecet, array $cenik): bool {
        // Počet dní od posledního odečtu do začátku nového ceníku
        $cas1 = strtotime($posledniOdecet['casodectu']);
        $cas2 = strtotime($cenik['platnyod']);

        $dny = ($cas2 - $cas1) / (24 * 3600);
        if ($dny <= 0) {
            return false; // Nic se neobjevilo
        }

        // Průměrná denní spotřeba (z posledního známého období)
        $prumerSpotrebaDen = $this->nactiPrumerDenníSpotrebu($idMeridla, $posledniOdecet);
        if ($prumerSpotrebaDen <= 0) {
            return false; // Bez průměru se nedá počítat
        }

        // Odhadnutý odečet na začátku nového ceníku
        $odhad = $posledniOdecet['odecet'] + ($prumerSpotrebaDen * $dny);

        // Vložení automatického odečtu
        $q = "INSERT INTO odecty (idmeridla, casodectu, odecet, poznamka, zadal, zacatekobdobi) 
               VALUES (?, ?, ?, ?, ?, 1)";
        db::q($q, [
            $idMeridla,
            $cenik['platnyod'],
            $odhad,
            __('Automaticky dopočítaný začátek období dle průměrné denní spotřeby'),
            $this->aUser['id']
        ]);

        $lastId = db::ii();
        return ($lastId > 0);
    }

    /**
     * Zjistí průměrnou denní spotřebu z poslední známé období
     * 
     * @param int $idMeridla ID měřidla
     * @param array $posledniOdecet Poslední odečet se zacatekobdobi = 1
     * @return float Průměrná denní spotřeba nebo 0
     */
    private function nactiPrumerDenníSpotrebu(int $idMeridla, array $posledniOdecet): float {
        // Najít odečet před posledním počátečním (aby se dalo spočítat období)
        $q = "SELECT casodectu, odecet FROM odecty 
               WHERE idmeridla = ? AND casodectu < ? 
               ORDER BY casodectu DESC LIMIT 1";
        $predchozi = db::f($q, $idMeridla, $posledniOdecet['casodectu']);

        if (empty($predchozi) || $predchozi['id'] == 0) {
            return 0;
        }

        $cas1 = strtotime($predchozi['casodectu']);
        $cas2 = strtotime($posledniOdecet['casodectu']);
        $dny = ($cas2 - $cas1) / (24 * 3600);

        if ($dny <= 0) {
            return 0;
        }

        $spotreba = $posledniOdecet['odecet'] - $predchozi['odecet'];
        return $spotreba / $dny;
    }
}
