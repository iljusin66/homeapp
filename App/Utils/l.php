<?php
namespace Latecka\Utils;

require_once 'autoload.php';
use Config\config;

/**
 * Staticka trida pro nacitani prekladu retezcu, dostupna v cele webove aplikaci
 */
class l{
    public static $input;
    public static $sekce;
    public static $forcedLang;
    private static $appLang;
    private static $lang;
    private static $aS; //Pole s jiz prelozenymi retezci
    private static $output;
    
    function __construct() {
        self::init();
    }
    
    public static function init(){
        /*
        * Kdyz je c_bNoTranslate == true, nic neprekladam, jen prespmeruju input na output a sanitizeHTMLm funkce
        */
        self::$lang = self::sanitizeLang(config::$lang);
         if (c_bNoTranslate) { return; }
        if (!isset(self::$aS)) {
            self::$sekce = 'front';
            self::$appLang = c_DefaultLang; //Default v Config.php
            self::$lang = self::sanitizeLang(config::$lang); //prvotni inicializace v Config.php
            self::$aS = array();
            self::$output = "";
        }
    }
    
    /**
     * <p>Vrati prelozeny retezec</p>
     * @param string $input <p>retezec urceny k prekladu</p>
     * @param string $sekce <p>Mohu oddelit preklady pro ruzne sekce webu (napr. admin, frontend, maily, atp.)</p>
     * @param string $forcedLang <p>vynucene id jazyka, do ktereho se ma text prelozit</p>
    */
    public static function t($input, $sekce = "", $forcedLang = '') {
        /*
        * Kdyz je c_bNoTranslate == true, nic neprekladam, jen prespmeruju input na output a sanitizeHTMLm funkce
        */
        if (c_bNoTranslate) { return $input; }
        //utils::debug('teeest ve tride __(');
        self::setVars($input, $sekce, $forcedLang);
        return self::translate();
    }
    
   
    public static function setVars($input, $sekce, $forcedLang) {
               
        //Osetreni a nastaveni vstupniho retezce
        self::$input = trim($input);
        if (self::$input == "") { return; }
        
        //Nastaveni sekce
        if ($sekce != "") { self::$sekce = $sekce; }
        
        //Nastaveni jazyka
        self::sanitizeLang($forcedLang);
        if ($forcedLang != '') { self::$forcedLang = $forcedLang; }
        self::setLang();

    }
    
    private static function setLang() {
        self::sanitizeLang(self::$forcedLang);
        self::sanitizeLang(self::$lang);
        
        if (self::$forcedLang != '') {
            self::$lang = self::$forcedLang;
            return;
        }elseif (self::$lang == '') {
            self::$lang = self::$appLang;
        }
    }
    
    private static function sanitizeLang(&$kodJazyka) {

        utils::mb_strtolowerRef($kodJazyka);
        if (!in_array($kodJazyka, config::$appLangs)) { $kodJazyka = ''; }
        return $kodJazyka;
    }
    
    private static function translate() {

        
        self::setRawOutputString();  
        
        
        return self::$output;
    }
    
    private static function setRawOutputString() {
        if (isset(self::$aS[self::$lang][md5(self::$input)])) {
            self::$output = self::$aS[self::$lang][md5(self::$input)];
        }else{
            self::fillLang();
        }
    }
    
    private static function fillLang() {
        if (self::$lang == self::$appLang) {
            $rows = db::fa("SELECT string, checksum FROM langstrings WHERE lang = ? AND section = ?", self::$lang, self::$sekce);
        }else{
            $rows = db::fa("SELECT * FROM ("
                . " SELECT string, checksum, 1 AS poradi FROM langstrings WHERE lang = ? AND section = ? "
                . " UNION ALL "
                . " SELECT string, checksum, 2 AS poradi FROM langstrings WHERE lang = ? AND section = ? "
                . ") AS t order BY t.poradi ASC ", self::$lang, self::$sekce, self::$lang, self::$sekce);
        }
        
        foreach ($rows as $row) {
            if (!isset(self::$aS[self::$lang][self::$sekce][$row["checksum"]])) {
                self::$aS[self::$lang][self::$sekce][$row["checksum"]] = $row['string'];
            }
        }
        
        //Jestlize v db preklad neni, zapisu jej do db
        if (!isset(self::$aS[self::$lang][self::$sekce][md5(self::$input)])) {
            self::$output = self::$input;
            $q = "INSERT INTO langstrings (lang, checksum, string, section) VALUES (?, ?, ?, ?)";
            db::q($q, self::$lang, md5(self::$input), self::$input, self::$sekce);
        }else{
            self::$output = self::$aS[self::$lang][self::$sekce][md5(self::$input)];
        }
    }
}
