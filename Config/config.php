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

/**
 * Main Class
 * Config
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
        
        if (c_bWork){
            //Nastaveni pro pracovni, lokalnim server
            $dbHost = "localhost";
            $dbUser = "root";
            $dbPass = "php895admin";
            $dbName = "home_app";
            error_reporting (E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
        }else{

            //Nastaveni pro ostry, verejny server
            /*
            $dbHost = "localhost";
            $dbUser = "";
            $dbPass = "";
            $dbName = "";
            
            ini_set('display_errors', 1);
            error_reporting (E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);    
             * 
             */
        }

        self::$dbHost = $dbHost;
        self::$dbUser = $dbUser;
        self::$dbPass = $dbPass;
        self::$dbName = $dbName;
        
        
        self::$mailsFolder = c_FileRoot . 'Mails/';

    }
    
    
}