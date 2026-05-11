<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;
use Latecka\Utils\request;
use Latecka\Utils\db;

require_once 'meridla.php';

new config();

class statistiky extends meridla {

    private $aUser = [];
    private const MIN_DNI = 30;
    private array $aCeniky = [];

    public bool $dostupne = false;
    public array $aktualniObdobi = [];
    public array $predchoziObdobi = [];     // stejný počet dní jako aktuální (pro průběžné srovnání)
    public array $predchoziObdobiCele = []; // celé předchozí období (pro srovnání s projekcí)
    public array $projekce = [];
    public array $chartData = [];
    public string $chyba = '';

    function __construct($aUser = []) {
        $this->aUser = $aUser;
        parent::__construct($this->aUser);
        $this->nactiStatistiky();
    }

    public function nactiStatistiky(): void {
        if (empty($this->aMeridlo) || $this->aMeridlo['id'] == 0) {
            return;
        }

        $aObdobi = db::fa(
            'SELECT casodectu FROM odecty WHERE idmeridla = ? AND zacatekobdobi = 1 ORDER BY casodectu DESC LIMIT 2',
            [$this->aMeridlo['id']]
        );

        if (count($aObdobi) < 2) {
            $this->chyba = __('Není k dispozici předchozí období pro srovnání.');
            return;
        }

        $aktualniStart  = $aObdobi[0]['casodectu'];
        $predchoziStart = $aObdobi[1]['casodectu'];
        $dnes           = date('Y-m-d H:i:s');

        $nDni = (strtotime($dnes) - strtotime($aktualniStart)) / 86400;

        if ($nDni < self::MIN_DNI) {
            $this->chyba = sprintf(
                __('Aktuální období je příliš krátké. Statistiky budou dostupné přibližně za %d dní.'),
                (int) ceil(self::MIN_DNI - $nDni)
            );
            return;
        }

        // Ceníky načteme jednou a sdílíme napříč metodami
        $this->aCeniky = db::fa(
            'SELECT cenazajednotku, odhadcenyzajednotku, platnyod, platnydo FROM ceniky WHERE idmeridla = ? ORDER BY platnyod DESC',
            [$this->aMeridlo['id']]
        );

        // Aktuální období: SpotrebaOd od začátku do dnes (= stejné hodnoty jako seznam odečtů)
        $this->aktualniObdobi = $this->spocitejObdobi($aktualniStart, $dnes, (int) round($nDni));

        // Předchozí období: stejný počet dní jako aktuální, cap na aktualniStart (bez překryvu)
        $predchoziKonecTs = min(
            strtotime($predchoziStart) + (int) round($nDni * 86400),
            strtotime($aktualniStart)
        );
        $predchoziKonec = date('Y-m-d H:i:s', $predchoziKonecTs);
        $predchoziNDni  = (int) round(($predchoziKonecTs - strtotime($predchoziStart)) / 86400);
        $this->predchoziObdobi = $this->spocitejObdobi($predchoziStart, $predchoziKonec, $predchoziNDni);

        // Celé předchozí období (predchoziStart → aktualniStart) — pro srovnání s projekcí
        $predchoziDelkaDni = (strtotime($aktualniStart) - strtotime($predchoziStart)) / 86400;
        $this->predchoziObdobiCele = $this->spocitejObdobi($predchoziStart, $aktualniStart, (int) round($predchoziDelkaDni));

        // Projekce: konec aktuálního období = začátek + délka předchozího (celého) období
        $konecAktualnihoTs  = strtotime($aktualniStart) + (int) round($predchoziDelkaDni * 86400);
        $konecAktualniho    = date('Y-m-d H:i:s', $konecAktualnihoTs);
        $dniDoKonce         = (strtotime($konecAktualniho) - strtotime($dnes)) / 86400;
        $this->projekce     = $this->spocitejProjekci($konecAktualniho, $dniDoKonce);

        $this->dostupne = true;
        $this->sestavChartData($aktualniStart, $predchoziStart, $predchoziDelkaDni, $nDni);
    }

    /**
     * Spočítá statistiky pro časové okno přes SpotrebaOd — stejná logika jako odecet.php.
     */
    private function spocitejObdobi(string $od, string $do, int $nDni): array {
        $aRows = db::fa('CALL SpotrebaOd(?, ?, ?, ?);',
            [$this->aUser['id'], $this->aMeridlo['id'], $od, $do]);

        if (empty($aRows)) {
            return $this->prazdneObdobi($od, $do, $nDni);
        }

        // Přepočet nákladů — kopie logiky z odecet.php
        foreach ($aRows as &$row) {
            if (empty($row['cenazajednotku']) || $row['cenazajednotku'] == 0) {
                $rowDate = substr($row['casodectu'], 0, 10);
                foreach ($this->aCeniky as $cenik) {
                    $cOd = $cenik['platnyod'];
                    $cDo = !empty($cenik['platnydo']) ? $cenik['platnydo'] : '9999-12-31';
                    if ($rowDate >= $cOd && $rowDate <= $cDo) {
                        if ($cenik['odhadcenyzajednotku'] > 0) {
                            $row['naklady'] = ($row['spotreba'] ?? 0) * $cenik['odhadcenyzajednotku'];
                        }
                        break;
                    }
                }
            }
        }
        unset($row);

        // Agregace — identická s odecet.php::spocitejPrumernouSpotrebu()
        $celkovaSpotreba = array_sum(array_column($aRows, 'spotreba'));
        $celkoveNaklady  = array_sum(array_column($aRows, 'naklady'));

        $minCas     = min(array_column($aRows, 'casodectu'));
        $maxCas     = max(array_column($aRows, 'casodectu'));
        $pocetHodin = (strtotime($maxCas) - strtotime($minCas)) / 3600;

        $prumernaSpotrebaDen = ($celkovaSpotreba > 0 && $pocetHodin > 0)
            ? $celkovaSpotreba / ($pocetHodin / 24)
            : 0.0;

        // Odhad posledního segmentu: od posledního odečtu do konce okna
        $dniOdhad      = (strtotime($do) - strtotime($maxCas)) / 86400;
        $odhadSpotreba = 0.0;
        $odhadNaklady  = 0.0;

        if ($dniOdhad > 0.01 && $prumernaSpotrebaDen > 0) {
            $odhadSpotreba = $prumernaSpotrebaDen * $dniOdhad;
            $odhadNaklady  = $odhadSpotreba * $this->najdiCenu(substr($do, 0, 10));
        }

        return [
            'od'                  => $od,
            'do'                  => $do,
            'nDni'                => $nDni,
            'celkovaSpotreba'     => $celkovaSpotreba + $odhadSpotreba,
            'celkoveNaklady'      => $celkoveNaklady  + $odhadNaklady,
            'prumernaSpotrebaDen' => $prumernaSpotrebaDen,
            'odhadSpotreba'       => $odhadSpotreba,
            'odhadNaklady'        => $odhadNaklady,
            'dniOdhad'            => round($dniOdhad, 1),
            'rows'                => $aRows,
        ];
    }

    /**
     * Projekce hodnot na předpokládaný konec aktuálního období.
     * Vychází z průměrné denní spotřeby aktuálního období.
     */
    private function spocitejProjekci(string $konecObdobi, float $dniDoKonce): array {
        $prumerDen = $this->aktualniObdobi['prumernaSpotrebaDen'] ?? 0.0;

        $dodatecnaSpotreba = ($dniDoKonce > 0 && $prumerDen > 0) ? $prumerDen * $dniDoKonce : 0.0;
        $dodatecneNaklady  = $dodatecnaSpotreba * $this->najdiCenu(substr($konecObdobi, 0, 10));

        return [
            'konecObdobi'         => $konecObdobi,
            'dniDoKonce'          => round($dniDoKonce, 1),
            'celkovaSpotreba'     => ($this->aktualniObdobi['celkovaSpotreba'] ?? 0.0) + $dodatecnaSpotreba,
            'celkoveNaklady'      => ($this->aktualniObdobi['celkoveNaklady']  ?? 0.0) + $dodatecneNaklady,
            'prumernaSpotrebaDen' => $prumerDen,
        ];
    }

    /**
     * Najde efektivní cenu (cenazajednotku › 0, jinak odhadcenyzajednotku) pro daný den.
     */
    private function najdiCenu(string $den): float {
        foreach ($this->aCeniky as $cenik) {
            $cOd = $cenik['platnyod'];
            $cDo = !empty($cenik['platnydo']) ? $cenik['platnydo'] : '9999-12-31';
            if ($den >= $cOd && $den <= $cDo) {
                return ($cenik['cenazajednotku'] > 0)
                    ? (float) $cenik['cenazajednotku']
                    : (float) $cenik['odhadcenyzajednotku'];
            }
        }
        return 0.0;
    }

    /**
     * Sestaví data pro čárový graf (4 série, x = dny od začátku období, y = kumulativní hodnota).
     */
    private function sestavChartData(string $aktualniStart, string $predchoziStart, float $predchoziDelkaDni, float $aktualniNDni): void {
        $a   = $this->aktualniObdobi;
        $p   = $this->predchoziObdobi;
        $pc  = $this->predchoziObdobiCele;
        $pr  = $this->projekce;

        // Linka 1: Předchozí N dní (modrá) — solid = reálná data, dashed = odhad do konce N dní
        $line1 = $this->buildChartLine(
            $p['rows'], $predchoziStart,
            $p['odhadSpotreba'], $p['odhadNaklady'], $p['dniOdhad']
        );

        // Linka 2: Aktuální N dní (oranžová) — solid = reálná data, dashed = odhad na dnes
        $line2 = $this->buildChartLine(
            $a['rows'], $aktualniStart,
            $a['odhadSpotreba'], $a['odhadNaklady'], $a['dniOdhad']
        );

        // Linka 3: Celé předchozí období (zelená) — solid = reálná data, dashed = odhad do konce
        $line3 = $this->buildChartLine(
            $pc['rows'], $predchoziStart,
            $pc['odhadSpotreba'], $pc['odhadNaklady'], $pc['dniOdhad']
        );

        // Linka 4: Projekce aktuálního období (červená, zcela dashed) — 2 body: dnes → konec
        $line4Spotreba = [
            ['x' => round($aktualniNDni, 2), 'y' => round($a['celkovaSpotreba'], 3)],
            ['x' => round($predchoziDelkaDni, 2), 'y' => round($pr['celkovaSpotreba'], 3)],
        ];
        $line4Naklady = [
            ['x' => round($aktualniNDni, 2), 'y' => round($a['celkoveNaklady'], c_RoundNaklady)],
            ['x' => round($predchoziDelkaDni, 2), 'y' => round($pr['celkoveNaklady'], c_RoundNaklady)],
        ];

        $this->chartData = [
            'predchozi'       => $line1,
            'aktualni'        => $line2,
            'predchoziCele'   => $line3,
            'projekce'        => ['spotreba' => $line4Spotreba, 'naklady' => $line4Naklady, 'solidCount' => 0],
            'jednotka'        => $this->aMeridlo['jednotka'] ?? '',
        ];
    }

    /**
     * Sestaví jednu sérii pro graf z řádků SpotrebaOd.
     * Vrátí pole s klíči spotreba[], naklady[] (x = dny, y = kumulativní), solidCount.
     */
    private function buildChartLine(array $rows, string $periodStart, float $odhadSpotreba, float $odhadNaklady, float $dniOdhad): array {
        usort($rows, fn($a, $b) => strcmp($a['casodectu'], $b['casodectu']));

        $startTs = strtotime($periodStart);
        $cumSpotreba = 0.0;
        $cumNaklady  = 0.0;
        $spotreba    = [['x' => 0.0, 'y' => 0.0]];
        $naklady     = [['x' => 0.0, 'y' => 0.0]];
        $lastX       = 0.0;

        foreach ($rows as $row) {
            if ($row['spotreba'] === null) {
                continue;
            }
            $x = round((strtotime($row['casodectu']) - $startTs) / 86400, 2);
            $cumSpotreba += (float) $row['spotreba'];
            $cumNaklady  += (float) ($row['naklady'] ?? 0);
            $spotreba[]   = ['x' => $x, 'y' => round($cumSpotreba, 3)];
            $naklady[]    = ['x' => $x, 'y' => round($cumNaklady, c_RoundNaklady)];
            $lastX        = $x;
        }

        $solidCount = count($spotreba); // počet bodů před odhadem (včetně počátku)

        if ($dniOdhad > 0.01 && ($odhadSpotreba > 0 || $odhadNaklady > 0)) {
            $x = round($lastX + $dniOdhad, 2);
            $spotreba[] = ['x' => $x, 'y' => round($cumSpotreba + $odhadSpotreba, 3)];
            $naklady[]  = ['x' => $x, 'y' => round($cumNaklady + $odhadNaklady, c_RoundNaklady)];
        }

        return ['spotreba' => $spotreba, 'naklady' => $naklady, 'solidCount' => $solidCount];
    }

    private function prazdneObdobi(string $od, string $do, int $nDni): array {
        return [
            'od'                  => $od,
            'do'                  => $do,
            'nDni'                => $nDni,
            'celkovaSpotreba'     => 0.0,
            'celkoveNaklady'      => 0.0,
            'prumernaSpotrebaDen' => 0.0,
            'odhadSpotreba'       => 0.0,
            'odhadNaklady'        => 0.0,
            'dniOdhad'            => 0.0,
            'rows'                => [],
        ];
    }
}
