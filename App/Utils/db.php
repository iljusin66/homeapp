<?php
namespace Latecka\Utils;

use PDO;
use Config\config;
/**
 * Main Class
 * PDO wrapper functions
 * @author ivan la.
 */
class db{
    private static $pdo;
    public static $numRows = 0;
    
    private static $dbHost;
    private static $dbUser;
    private static $dbPass;
    private static $dbName;
    
    /**
     * 
     * @param string $dbHost
     * @param string $dbUser
     * @param string $dbPass
     * @param string $dbName
     */
    function __construct ($dbHost = '', $dbUser = '', $dbPass = '', $dbName = '') {
        self::$dbHost = ($dbHost=='') ? config::$dbHost : $dbHost;
        self::$dbUser = ($dbUser=='') ? config::$dbUser : $dbUser;
        self::$dbPass = ($dbPass=='') ? config::$dbPass : $dbPass;
        self::$dbName = ($dbName=='') ? config::$dbName : $dbName;

        
        $this->open();
    }
    
    /**
     * Provede SQL (prepared) příkaz a v případě SELECTu vrátí počet vrácených řádků
     * @param string $q
     * @param type $params
     * @return int|null
     */
    public static function q(string $q, ...$params) : ?int {
        if (!is_array($params)) { $params = []; }
        if (count($params) == 1 && is_array($params[0])) { $params = array_values($params[0]); }
        $pdo = self::prepExec($q, $params, 'q');
        $rc = $pdo->rowCount();
        self::setRowCount($rc);
        return $rc;
    }
    
    /**
     * Provede SQL (prepared) SELECT příkaz a vrátí všechny záznamy v poli
     * @param string $q
     * @param type $params
     * @return array
     */
    public static function fa(string $q, ...$params) {
        if (!is_array($params)) { $params = []; }
        if (count($params) == 1 && is_array($params[0])) { $params = $params[0]; }
        $pdo = self::prepExec($q, $params, 'fa');
        $return = $pdo->fetchAll();
        if (empty($return)) { $return = []; }
        return $return;
    }
    
    

    /**
     * rovede SQL (prepared) SELECT příkaz a vrátí z prvního záznamu hodnotu sloupce s indexem $indexColumn
     * @param int $indexColumn index sloupce, který chci vrátit
     * @param string $q SQL dotaz
     * @param string:array $params parametry SQL dotazu
     * @return string
     */
    public static function r(int $indexColumn, string $q, ...$params) {
        utils::fixIntRef($indexColumn);
        if (!is_array($params)) { $params = []; }
        if (count($params) == 1 && is_array($params[0])) { $params = $params[0]; }
        $pdo = self::prepExec($q, $params, 'r');
        return $pdo->fetchColumn($indexColumn);
    }
    
    /**
     * Provede SQL (prepared) SELECT příkaz a vrátí jeden záznam jako pole
     * @param string $q
     * @param type $params
     * @return array|null
     */
    public static function f(string $q, ...$params) : ?array {
        if (!is_array($params)) { $params = []; }
        if (count($params) == 1 && is_array($params[0])) { $params = $params[0]; }
        $pdo = self::prepExec($q, $params, 'f');
        $return = $pdo->fetch();
        if (empty($return)) { $return = []; }
        return $return;
    }
    
    public static function ii() {
        return self::$pdo->lastInsertId();
    }
    
    private static function prepExec(string $q, $params, string $fn = '') {
        self::setRowCount(0);
        try {
            $pdo = self::$pdo->prepare($q);
            $pdo->execute($params);
            return $pdo;
        } catch (\PDOException $e) {
            self::catchErr($e, 'DB Error ('.$fn.')');
        } catch (\Exception $e) {
            self::catchErr($e, 'General Error ('.$fn.')');
        }        
    }
    
    private function checkIfDbExists(array $options = []) : bool {
        try {
            $pdo = new PDO("mysql:host=".self::$dbHost.";dbname=INFORMATION_SCHEMA", self::$dbUser, self::$dbPass, $options);
        } catch (\PDOException $e) {
            self::catchErr($e, 'fn checkIfDbExists: DB Error (open)');
        } catch (\Exception $e) {
            self::catchErr($e, 'fn checkIfDbExists: General Error (open)');
        }

        $stmt = $pdo->query("SELECT * FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '". self::$dbName ."'");
        
        return (bool) $stmt->fetchColumn();
    }
    
    private function open(){
        if (defined('c_bDbOpened')) {
            //die('Chyba, duplicitni pripojeni k DB');
        }
        //Pripojeni na db
        $options=array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );
        
        if (!$this->checkIfDbExists($options)) {
            echo '<p>'. __('Chyba: databáze neexistuje!').'</p>';
            return false;
        }
        
        try {
            self::$pdo = new PDO("mysql:host=".self::$dbHost.";dbname=".self::$dbName, self::$dbUser, self::$dbPass, $options);
        } catch (\PDOException $e) {
            self::catchErr($e, 'DB Error (open)');
        } catch (\Exception $e) {
            self::catchErr($e, 'General Error (open)');
        }
        // Pro verzi MySql < 5.5.6 zapnu atribut PDO::ATTR_EMULATE_PREPARES, jinak mi bude db vracet chyby
        $emulate_prepares_below_version = '5.5.6';
        $serverversion = self::$pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        $emulate_prepares = (version_compare($serverversion, $emulate_prepares_below_version, '<'));
        //die('$emulate_prepares: '.print_r($serverversion, true));
        self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $emulate_prepares);
        
        
        define('c_bDbOpened', true);
    }
    
    public static function nr() {
        return (self::$numRows > 0) ? self::$numRows : 0;
    }
    
    
    private static function setRowCount($n) {
        self::$numRows = $n;
    }
    
    /**
     * Odchyceni chyby sql dotazu
     * @param object $e <p>Objekt odchycene chyby</p>
     *  Kdyz je false a jsem na ostrem serveru v klientske casti, zobrazi se univerzalni stranka s omluvou a posle mail s podrobnostmi<br>
     *  Jsem-li v administraci, script se zastavi (die()) a zobrazi se chybova hlaska</p>
     * @return mixed
     */
    private static function catchErr($e, $str = '') {
        //Kvuli odeslani chybove hlasky prilinkuju phpmailer
        //include_once c_FileRoot.'inc/PHPMailer/phpmailer.php';
        
        $eMessage = '<pre>'.print_r([
                "eMessage" => $e->getMessage(),
                "eLine" => $e->getLine(),
                "eFile" => $e->getFile(),
                "eTrace" => $e->getTrace(),
                "REQUEST_URI" => $_SERVER["REQUEST_URI"],
                "REQUEST_METHOD" => $_SERVER["REQUEST_METHOD"],
                "zak_iduser" => $_COOKIE["zak_iduser"],
                "zak_velko" => $_COOKIE["zak_velko"],
            ], true).'</pre>';
        
        
        if (c_bWork) {
            die($eMessage);
        }else{
            $bSendMail = true;
            $touchFile = c_FileRoot."inc/dberror.txt";
            if (file_exists($touchFile)) {
                $bSendMail = (filemtime($touchFile) < (time() - 60));
            }
            touch($touchFile, time());
            //if ($bSendMail) { sendPHPMail("Hlídač DB UNCS", "info@uncs.eu", "Ivan", "latecka@uncs.eu", "Chyba databáze na UNCS.eu!", $eMessage); }
            die('Ajvaj, něco se nepovedlo :(<p class="error">DB error: '.$e->getMessage().'</p>');
            
            
        }
    }
    
    public static function vratNazvyPoli($arr, $bReturnArray = false) {
        foreach ($arr AS $key => $val) :
            $a[] = utils::toSeoUrl ($key);
        endforeach;
        if ($bReturnArray) {
            return $a;
        }else{
            return '`'.implode('`, `', $a).'`'.PHP_EOL;
        }
    }
    
    public static function vratOtazniky($arr, $bNewLine = true) {
        return trim(str_repeat(',?', count($arr)), ','). (($bNewLine) ? PHP_EOL : '');
    }
    
    public static function vratDuplicateUpdateValues($arr, ...$aUnset) {
        $a = [];
        if (is_array($aUnset)) :
            foreach ($aUnset AS $val) : unset($arr[$val]); endforeach;
        endif;
        $arr = self::vratNazvyPoli($arr, true);
        foreach ($arr AS $val) :
            $a[] = $val." = VALUES(".$val.")";
        endforeach;
        return implode(', ', $a).PHP_EOL;
    }

}

new db();