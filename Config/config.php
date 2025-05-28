<?php
namespace Config;
define('c_FileRoot', dirname(dirname(__FILE__)).'\\');
define('c_SubfolderURL', '/');

list($scriptBaseName) = explode('.', trim($_SERVER['SCRIPT_NAME'], '/'));
define('c_ScriptBaseName', $scriptBaseName);
define('c_MainUrl', (stripos($_SERVER['REQUEST_SCHEME'],'https') === 0 ? 'https://' : 'http://'). $_SERVER["SERVER_NAME"] . c_SubfolderURL);
define('c_bWork', ($_SERVER["SERVER_NAME"]=='homeapp'));
define('c_Mena', 'Kč');

define('c_DefaultLang', 'cs');
define('c_bNoTranslate', true); //Pokud je true, tak se preklady neprovadi, ale jen se presmerovavaji retezce na output a sanitizeHTMLm funkce
define('c_RequestPost', $_SERVER['REQUEST_METHOD'] == 'POST');
define('c_RequestGet', $_SERVER['REQUEST_METHOD'] == 'GET');

//Maximalni pololeny pocet pokusu o prihlaseni, nez dojde k blokaci
define('c_MaxLoginPokusu', 10);

/*
 * Role
 * <p>Definuje role uzivatelu a jejich prava</p>
 * <p>ID rolí odpovídají tomu co je v d v tabulce role</p>
 * <p>reader - Smi cist<br>
 * writer - Smi cist a zapisovat<br
 * editor - Smi cist, zapisovat a editovat<br
 * admin - Smi vsechno</p>
 * <p>Vsechny role jsou vzdy vetsi nez ta predchozi, tj. pokud je uzivatel reader, tak je i writer, editor a admin</p>
 * <p>Pokud je uzivatel admin, tak je i writer, editor a reader</p>
 * @author ivan la.
 */
define('ca_Role', [
    'reader' => 1, //Smi jen cist
    'writer' => 2, //Smi cist a zapisovat
    'editor' => 3, //Smi cist, zapisovat a editovat
    'admin' => 4 //Smi vsechno
]);

/*
 * RoleGroup
 * <p>Definuje skupiny uzivatelu a jejich prava</p>
 * <p>reader - Smi cist<br>
 * writer - Smi cist a zapisovat<br
 * editor - Smi cist, zapisovat a editovat<br
 * admin - Smi vsechno</p>
 * <p>Vsechny role jsou vzdy vetsi nez ta predchozi, tj. pokud je uzivatel reader, tak je i writer, editor a admin</p>
 * <p>Pokud je uzivatel admin, tak je i writer, editor a reader</p>
 * @author ivan la.
 */
define('ca_RoleGroup', [
    'reader' => [ca_Role['reader'], ca_Role['writer'], ca_Role['editor'], ca_Role['admin']], //Smi cist
    'writer' => [ca_Role['writer'], ca_Role['editor'], ca_Role['admin']], //Smi cist a zapisovat
    'editor' => [ca_Role['editor'], ca_Role['admin']], //Smi cist, zapisovat a editovat
    'admin' => [ca_Role['admin']] //Smi vsechno
]);

//Unikatni razitko prohlizece. Pouzije se pro identifikaci uzivatele a jeho prohlizece
//Prohlizec je identifikovan podle IP adresy, User-Agent, Accept-Language, Accept-Encoding, HTTP_VIA a X-Forwarded-For
$browserUID = md5(
    ($_SERVER['REMOTE_ADDR'] ?? '') . '|' .
    ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|' .
    ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '') . '|' .
    ($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '') . '|' .
    ($_SERVER['HTTP_VIA'] ?? '') . '|' .
    ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')
);
define('c_BrowserUID', $browserUID);

// IP adresa uzivatele. Pouzije se pro identifikaci uzivatele
$userIP = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_VIA'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
define('c_UserIP', $userIP);

/**
 * Config
 * <p>Hlavni konfiguracni trida aplikace</p>
 * <p>Obsahuje nastaveni pro pripojeni k databazi, jazyk, slozku pro maily a dalsi konfiguracni hodnoty</p>
 * @package Config
 * @author ivan la.
 */
class config{
    public static $dbHost;
    public static $dbUser;
    public static $dbPass;
    public static $dbName;
    public static $mailsFolder;
    public static $lang;
    public static $appLangs = ['cs', 'en', 'de'];
    
    //Konstruktor
    function __construct () {
        $this->init(); 
        self::$lang = c_DefaultLang; //Default v Config.php
    }
    
    /**
     * Nastavi celou konfiguraci aplikace
     * @return void
     */
    private static function init() : void {
        
        if (c_bWork) :
            //Nastaveni pro pracovni, lokalnim server
            $dbHost = "localhost";
            $dbUser = "root";
            $dbPass = "php895admin";
            $dbName = "home_app";
            error_reporting (E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
        else:

            //Nastaveni pro ostry, verejny server
            
            $dbHost = "md413.wedos.net";

            //Uzivatel s plnymi opravnenimi
            //$dbUser = "a376140_homeapp";
            //$dbPass = "2rBBLuj9";


            //Uzivatel s omezenymi opravnenimi
            $dbUser = "w376140_homeapp";
            $dbPass = "GfUqr6J3";

            $dbName = "d376140_homeapp";
            
            ini_set('display_errors', 1);
            error_reporting (E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);    
        endif;

        self::$dbHost = $dbHost;
        self::$dbUser = $dbUser;
        self::$dbPass = $dbPass;
        self::$dbName = $dbName;
        
        
        self::$mailsFolder = c_FileRoot . 'Mails/';

    }
    
    
}