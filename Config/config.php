<?php
namespace Config;
define('c_FileRoot', dirname(dirname(__FILE__)).'\\');
define('c_SubfolderURL', '/');

list($scriptBaseName) = explode('.', trim($_SERVER['SCRIPT_NAME'], '/'));
define('c_ScriptBaseName', $scriptBaseName);
define('c_MainUrl', (stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://'). $_SERVER["SERVER_NAME"] . c_SubfolderURL);
define('c_bWork', ($_SERVER["SERVER_NAME"]=='homeapp'));
define('c_Mena', 'Kč');

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
    
    //Konstruktor
    function __construct () {
        $this->init();     
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