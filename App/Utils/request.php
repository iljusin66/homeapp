<?php
namespace Latecka\HomeApp\Utils;

use Latecka\HomeApp\Utils\l;
/**
 * Trida na cteni a validaci hodnot z globalnich promennych
 */
class request {
    /** @var array Obsahuje pripadne chyby validace hodnot */
    public static $aErrors = [];
    
    /** @var array pole s vyctem povolenych typu globalnich promennych, ze kterych tato trida cte a validuja data. Navic je hodnota VAR, ktera nacte primo hodnotu lokalni promenne */
    private static $aReqTypes = ['GET', 'POST', 'COOKIE', 'SESSION', 'REQUEST', 'SERVER', 'VAR']; 
    
    /** @var string Pomocny promenna, do ktere prubezne zapisuju osetrene hodnoty */
    static private $var = "";
    
    /** @var string Pomocna promenna do ktere si ukladam nazev vstupniho parametru $attrname. Pouziji
     * ji pak v pripade chyby validace jako key v poli chyb $aErrors  */
    private static $varname = "";
    
    /** @var string Jaky typ globalni promenne chci cist (vycet povolenych je v poli $aReqTypes) */
    public static $requestType = "GET";
    
    /** @var string Jaky typ globalni promenne chci cist (vycet povolenych je v poli $aReqTypes) */
    public static $isPost = false;
    
    /** @var string Regexpy pro pouziti ve funkci validate (jako parametr pro FILTER_VALIDATE_REGEXP) */
    public static $regexes = [
        'phone' => "^[\+]?[0-9]{9,14}\$",
        'psc' => "^[1-9][0-9]{4}\$",
    ];
    
        
    /**
     * Sanitizace/osetreni obsahu promennych
     * @param string $type Jakeho typu ma byt promenna (phone, psc, url, int, float, email, string)
     */
    private static function sanitize($type, $debug = false) {
        self::$var = trim(self::$var);
        self::$isPost = ($_SERVER['REQUEST_METHOD']==='POST');
        $filter = "";
        $flags = NULL;
        if ($type == 'phone' || $type == 'psc') {
            self::$var = str_replace([' ', '-','/'], ['','', ''], self::$var);
        }elseif ($type == 'url') { 
            $filter = FILTER_SANITIZE_URL;
        }elseif ($type == 'int') {
            self::$var = utils::fixInt(self::$var, true, $debug);
        }elseif ($type == 'float') {
            self::$var = utils::fixFloat(self::$var);
        }elseif ($type == 'email') {
            $filter = FILTER_SANITIZE_EMAIL;
        }elseif ($type == 'string') {
            $filter = FILTER_SANITIZE_STRING;
            $flags = FILTER_FLAG_NO_ENCODE_QUOTES;
        }
        if (!empty($filter)) {
            self::$var = (filter_var(self::$var, $filter, $flags));
        }
        
    }
    
    
    /**
     * Napni promennou, kterou pak budu sanitizovat a validovat
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $attr_requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     */
    private static function setVar($attrname, $attr_requestType = "") {
        $attr_requestType = strtoupper($attr_requestType);
        $requestType = strtoupper(in_array($attr_requestType, self::$aReqTypes)) ? $attr_requestType :  self::$requestType;
        self::$varname = $attrname;
        if ($requestType==='SESSION') {
           self::$var = $_SESSION[$attrname];
        }elseif ($requestType === 'POST') {
            self::$var = $_POST[$attrname];
        }elseif ($requestType === 'GET') {
            self::$var = $_GET[$attrname];
        }elseif ($requestType === 'COOKIE') {
            self::$var = $_COOKIE[$attrname];
        }elseif ($requestType === 'SERVER'){
            self::$var = $_SERVER[$attrname];
        }elseif ($requestType === 'REQUEST'){
            self::$var = $_REQUEST[$attrname];
        }elseif ($requestType === 'VAR'){
            self::$var = $$attrname;
        }else{
            self::$var = "";
        }
    }
    
    
    /**
     * Validace obsahu promenne. Je volana z funkce sanitize, V pripade, ze promenna neprojde validaci, funkce vlozi do pole $aErrors[$varname] hodnotu true
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $type Ocekavany typ ctene promenne 
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return mixed Obsah promenne po sanitizaci a validaci
     */
    private static function validate($attrname, $type, $requestType = '') {
        self::setVar($attrname, $requestType);
        $debug = ($attrname=="cu");
        
        $error = true;
        if (empty($type)) { return self::$var; }
        if (empty(self::$var) && ($type == 'float' || $type == 'int')) { self::$var = 0; return self::$var; }
        self::sanitize($type, $debug);
        if($type=='string') {
            return self::$var;
        }elseif($type=='html') {
            return self::$var;
        }elseif ($type == 'phone') {
            $error = l::t('Neplatné telefonní číslo');
            $return = filter_var(self::$var, FILTER_VALIDATE_REGEXP, ["options"=> ["regexp"=>'!'.self::$regexes[$type].'!i']]);
        }elseif($type=='psc') {
            $error = l::t('Neplatné PSČ');
            $return = filter_var(self::$var, FILTER_VALIDATE_REGEXP, ["options"=> ["regexp"=>'!'.self::$regexes[$type].'!i']]);
        }elseif ($type == 'email') {
            $error = l::t('Neplatný e-mail');
            $filter = FILTER_VALIDATE_EMAIL;
        }elseif ($type == 'float') { 
            $filter = FILTER_VALIDATE_FLOAT;
        }elseif ($type == 'int') { 
            $filter = FILTER_VALIDATE_INT;
        }elseif ($type == 'boolean') { 
            $filter = FILTER_VALIDATE_BOOLEAN;
        }elseif ($type == 'ip') { 
            $error = l::t('Neplatná IP adresa');
            $filter = FILTER_VALIDATE_IP;
        }elseif ($type == 'url') {
            $error = l::t('Neplatná URL adresa');
            $filter = FILTER_VALIDATE_URL;
        }elseif ($type == 'datum_dmY') {
            $error = l::t('Neplatné datum');
            list($d,$m,$y) = explode('.', self::$var);
            return date('d.m.Y', strtotime($y.'-'.$m.'-'.$d));
        }elseif ($type == 'datum_Ymd') {
            $error = l::t('Neplatné datum');
            list($y,$m,$d) = explode('.', self::$var);
            return date('Y.m.d', strtotime($y.'-'.$m.'-'.$d));
        }
        
        if(!empty($filter)) {
            $return = filter_var(self::$var, $filter);
        }

        //Kdyz validace probehne OK, vracim zvalidovanou hodnotu
        if ($return !== false) { return $return; }
        
        //Validace nebyla uspesna, vratim chybu i puvodni hodnotu
        self::$aErrors[self::$varname] = $error;
        return self::$var;
    }
    
    /**
     * Nacteni, sanitizace a validace promenne typu INT
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return int
     */
    public static function int($attrname, $requestType = '') {
        return self::validate($attrname, "int", $requestType);
    }
    
    /**
     * Nacteni, sanitizace a validace promenne typu FLOAT
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return float
     */
    public static function float($attrname, $requestType = '') {
        return self::validate($attrname, "float", $requestType);
    }
    
    /**
     * Nacteni, sanitizace a validace promenne typu PHONE (telefon)
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return int|string
     */
    public static function phone($attrname, $requestType = '') {
        return self::validate($attrname, "phone", $requestType);
    }
    
    /**
     * Nacteni, sanitizace a validace promenne typu E-mail
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return string
     */
    public static function mail($attrname, $requestType = '') {
        return self::validate($attrname, "email", $requestType);
    }
    
    /**
     * Alias funkce mail
     */
    public static function email($attrname, $requestType = '') {
        return self::mail($attrname, $requestType);
    }
    
    /**
     * Nacteni, sanitizace a validace promenne typu URL
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return string
     */
    public static function url($attrname, $requestType = '') {
        return self::validate($attrname, "url", $requestType);
    }
    
    /**
     * Nacteni, sanitizace a validace promenne typu PSC
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return string|int
     */
    public static function psc($attrname, $requestType = '') {
        return self::validate($attrname, "psc", $requestType);
    }
    
    /**
     * Nacteni, sanitizace a validace promenne typu IP adresy
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return string
     */
    public static function ip($attrname, $requestType = '') {
        return self::validate($attrname, "ip", $requestType);
    }
    
    /**
     * Nacteni, sanitizace a validace promenne typu Boolean
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return boolean
     */
    public static function boolean($attrname, $requestType = '') {
        return self::validate($attrname, "boollean", $requestType);
    }
    
    /**
     * Nacteni, sanitizace a validace promenne typu String
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return string
     */
    public static function string($attrname, $requestType = '') {
        return self::validate($attrname, "string", $requestType);
    }
    
    /**
     * Nacteni, sanitizace a validace promenne typu String
     * @param string $attrname Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return string
     */
    public static function html($attrname, $requestType = '') {
        return self::validate($attrname, "html", $requestType);
    }    

    /**
     * Nacteni, sanitizace a validace hodnot v poli typu String
     * @param string $attrname Obsahuje nazev pole 
     * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
     * @return array
     */
    public static function arrayString($attrname, $requestType = '') {
        return self::arraySanitize($attrname, $requestType, 'string');
    }
    
    /**
    * Nacteni, sanitizace a validace hodnot v poli typu Int
    * @param string $attrname Obsahuje nazev pole 
    * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
    * @return array
    */
    public static function arrayInt($attrname, $requestType = '') {
        return self::arraySanitize($attrname, $requestType, 'int');
    }
    
    /**
    * Nacteni, sanitizace a validace hodnot v poli typu Float
    * @param string $attrname Obsahuje nazev pole 
    * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
    * @return array
    */
    public static function arrayFloat($attrname, $requestType = '') {
        return self::arraySanitize($attrname, $requestType, 'float');
    }
    
    
    /**
    * Nacteni, sanitizace hodnot v poli typu Decimal
    * @param string $attrname Obsahuje nazev pole 
    * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
    * @return array
    */
    public static function arrayDecimal($attrname, $requestType = '') {
        return self::arraySanitize($attrname, $requestType, 'decimal');
    }
    
    /**
    * Nacteni, sanitizace hodnot v poli
    * @param string $attrname Obsahuje nazev pole 
    * @param typ sanitace
    * @param string $requestType Vynuceny typ globalni promenne ze ktere budu obsah cist (mimo nastaveni v instanci tridy)
    * @return array
    */
    private static function arraySanitize($attrname, $requestType = "", $typ = "") {
        self::setVar($attrname, $requestType);

        if (!is_array(self::$var)) { return []; }
        $arr = [];
        foreach (self::$var AS $key => $val) {
            if ($typ === 'int') {
                $arr[$key] = utils::fixInt($val);
            }elseif  ($typ === 'float') {
                $arr[$key] = utils::fixFloat($val);
            }elseif  ($typ === 'decimal') {
                $arr[$key] = utils::fixDecimal($val);
            }elseif  ($typ === 'string') {
                $arr[$key] = filter_var(trim($val), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            }
        }
        return $arr; 
    }
    
}
