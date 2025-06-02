<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;


require_once 'autoload.php';

new config();


class user {

    const SaltMd5 = 'hhžžQH|*k855';
    
    public $aErr = [];
    public $aUser;
    private $cookieTime;

    function __construct($bCheckLogin = true) {
        $this->cookieTime = time() + (60*60*6); //6 hodin
        if (request::string('action', 'POST')=='login') :
            $this->login();
        elseif (c_ScriptBaseName=='registrace') :
            //Registrace uzivatele
            $this->registrace();            
        elseif($bCheckLogin):
            $this->checkLogin();
        endif;
        
    }

    private function registrace() {
        if (!request::string('action', 'POST')=='registrace') :
            return;
        endif;


        //$email = request::string('email', 'POST');
        $heslo = request::string('password', 'POST');
        $email = request::mail('email', 'POST');
        
        //debug("Registrace uzivatele: $heslo, $email");
        
        if (utils::fixInt(db::r(0, "SELECT id FROM users WHERE email = ?", $email)) > 0) :
            $this->setErrorRegistrace(1); return false; //Uzivatel s timto e-mailem uz existuje
        endif;
        
//;

        //Registrace uzivatele
        $q = "INSERT INTO users (uuid, heslo, email, ip) VALUES (UNHEX(REPLACE(UUID(), '-', '')), ?, ?, ?, ?)";
        db::q($q, utils::getHashHeslo($heslo, self::SaltMd5), $email, c_UserIP);
        
        //Nacteni noveho uzivatele
        $this->aUser["id"] = db::ii();
        if ($this->aUser["id"]==0) : $this->setErrorRegistrace(2); return; endif;
        
        utils::setCookie('email', $this->aUser["email"], $this->cookieTime);
        utils::setCookie('iduser', $this->aUser["id"], $this->cookieTime);
        utils::setCookie('hash', utils::getHash([$this->aUser["email"], $this->aUser["id"]], self::SaltMd5), $this->cookieTime);
        
        header('Location: /?s=regOk');
        die();
    }
    
    private function jeLoginBlokovan(): bool {
        $casTed = time();

        //Defaultni doba blokace ja na minutu
        //Pouzije se i pro pokusy, ktere jeste uzivatele neblokuji, ale hodnota
        //se pro neblokovane uzivatele nevyhodnocuje. slouzi k promazani db pri jakemkoli volani teto funkce (viz Smazat staré záznamy)
        $blokaceDoDB = date('Y-m-d H:i:s', strtotime('+1 minute'));

        // Smazat staré záznamy
        $q = "DELETE FROM login_pokusy WHERE blokacedo < ?";
        db::q($q, date('Y-m-d H:i:s', $casTed));

        // Načíst záznam pro dané UID
        $q = "SELECT pocet, blokacedo FROM login_pokusy WHERE `uid` = ?";
        $row = db::f($q, c_BrowserUID);

        $pocet = 0;
        $jeBlokovan = false;
        if ($row) {
            $pocet = (int)$row['pocet'];
            $blokaceDoUnix = strtotime($row['blokacedo']);
        }


        // Nastavení nové blokace dle počtu pokusů
        if ($pocet >= (c_MaxLoginPokusu * 5)) {
            $blokaceDoDB = date('Y-m-d H:i:s', strtotime('+1 day'));
            return true; //Pri petinasobku povolenych pokusu o prihlaseni je blokace na den a nedelam update db
        } elseif ($pocet >= c_MaxLoginPokusu) {    
            $jeBlokovan = true;
        }
        // Zápis nebo update pokusu
        $q = "INSERT INTO login_pokusy (`uid`, ip, pocet, blokacedo) 
            VALUES (?, ?, 1, ?) 
            ON DUPLICATE KEY UPDATE pocet = pocet + 1, blokacedo = ?";
        db::q($q, c_BrowserUID, c_UserIP, $blokaceDoDB, $blokaceDoDB);

        return $jeBlokovan;
    }
    
    private function login() {
        //Kontrola bruteforce pokusu o prihlaseni
        if ($this->jeLoginBlokovan()) :
            $this->setErrorLogin(3); return;
        endif;

        $this->aUser["email"] = $email = request::string('email', 'POST');
        $heslo = request::string('password', 'POST');
        
        if ($email == '' || $heslo == '') : $this->setErrorLogin(1); return; endif;

        $q = "SELECT u.id, u.username AS name, u.email FROM users AS u WHERE u.email = ? AND u.heslo = ? AND aktivni > 0";
        $this->aUser = db::f($q, $email, utils::getHashHeslo($heslo, self::SaltMd5));
        //debug($this->aUser);
        //$this->aUser["email"] = $email;
        if ($this->aUser["id"]==0) : $this->setErrorLogin(2); return; endif;
        utils::setCookie('email', $this->aUser["email"], $this->cookieTime);
        utils::setCookie('iduser', $this->aUser["id"], $this->cookieTime);
        utils::setCookie('hash', utils::getHash([$this->aUser["email"], $this->aUser["id"]], self::SaltMd5), $this->cookieTime);
        header('Location: /');
        die();
    }
    
    
   
    private function setErrorLogin($numErr = 1) {
        utils::fixIntRef($numErr);
        if ($numErr === 1) :
            $err = __('E-mail a heslo jsou povinné údaje.');
        elseif ($numErr === 2) :
            $err = __('Neplatné přihlašovací údaje.');
        elseif ($numErr === 3) :
            $err = __('Příliš mnoho pokusů o přihlášení. Zkuste to později');
        endif;
            
        $this->aErr[] = $err;
    }
    
    private function setErrorRegistrace($numErr = 1) {
        utils::fixIntRef($numErr);
        if ($numErr === 1) :
            $err = __('Uživatel s tímto e-mailem již existuje.');
        elseif ($numErr === 2) :
            $err = __('Chyba při registraci uživatele. Zkuste to prosím znovu.');
        elseif ($numErr === 3) :
            $err = '';
        endif;
            
        $this->aErr[] = $err;
    }

    public function checkLogin($bRedirect = true) {
        

        $this->aUser["email"] = request::string('email', 'COOKIE');
        $this->aUser["id"] = request::int('iduser', 'COOKIE');

        if ($this->aUser["email"]=='') : 
            if (c_ScriptBaseName != 'index') :
                header('Location: /');
                die();
            endif;
            define('cb_Login', false);
            return;
        endif;
                
        if (utils::getHash([$this->aUser["email"], $this->aUser["id"]], self::SaltMd5) != request::string('hash', 'COOKIE')) : 
            $this->logout('badHash', $bRedirect);
        endif;
        
        if (!defined('cb_Login')) : define('cb_Login', true); endif;
        utils::refreshCookies();
        
        $this->doplnVsechnaUserData();
    }

    
    
    
    public function logout($err = '', $bRedirect = true) {
        if (!$bRedirect) : die('Chyba #21 : Neopravneny pristup'); endif;
        
        utils::clearCookiesArray(['email', 'iduser', 'hash']);
        header('Location: /?e=' . $err);
        die();
    }

    private function doplnVsechnaUserData() {
        $q = "SELECT u.id, u.username AS name, u.heslo, u.email FROM users AS u WHERE u.id = ?";
        $this->aUser = db::f($q, $this->aUser["id"]);
        if ($this->aUser["id"]==0) : $this->setErrorLogin(2); return; endif;
        $this->doplnUser2Meridla();
    }

    private function doplnUser2Meridla() {
        $this->aUser["meridlaRole"] = [];
        $q = "SELECT idmeridla, idrole FROM meridla2users WHERE iduser = ?";
        $r = db::fa($q, $this->aUser["id"]);
        
        foreach ($r as $row) {
            $this->aUser["meridlaRole"][$row["idmeridla"]] = $row["idrole"];
        }
        
    }
        
}
