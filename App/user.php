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
        elseif($bCheckLogin):
            $this->checkLogin();
        endif;
        
    }
    
    private function login() {
        $this->aUser["login"] = $login = request::string('login', 'POST');
        $heslo = request::string('password', 'POST');
        
        if ($login == '' || $heslo == '') : $this->setErrorLogin(1); return; endif;

        $q = "SELECT u.id, u.username AS name FROM users AS u WHERE u.login = ? AND u.heslo = ?";
        $this->aUser = db::f($q, $login, utils::getHashHeslo($heslo, self::SaltMd5));
        $this->aUser["login"] = $login;
        if ($this->aUser["id"]==0) : $this->setErrorLogin(2); return; endif;
        utils::setCookie('username', $this->aUser["name"], $this->cookieTime);
        utils::setCookie('iduser', $this->aUser["id"], $this->cookieTime);
        utils::setCookie('hash', utils::getHash([$this->aUser["name"], $this->aUser["id"]], self::SaltMd5), $this->cookieTime);
        header('Location: /');
        die();
    }
    
    
   
    private function setErrorLogin($numErr = 1) {
        utils::fixIntRef($numErr);
        if ($numErr === 1) :
            $err = 'Login a heslo jsou povinné údaje.';
        elseif ($numErr === 2) :
            $err = 'Neplatné přihlašovací údaje.';
        endif;
            
        $this->aErr[] = $err;
    }
    
    public function checkLogin($bRedirect = true) {
        

        $this->aUser["name"] = request::string('username', 'COOKIE');
        $this->aUser["id"] = request::int('iduser', 'COOKIE');

        if ($this->aUser["name"]=='') : 
            if (c_ScriptBaseName != 'index') :
                header('Location: /');
                die();
            endif;
            define('cb_Login', false);
            return;
        endif;
                
        if (utils::getHash([$this->aUser["name"], $this->aUser["id"]], self::SaltMd5) != request::string('hash', 'COOKIE')) : 
            $this->logout('badHash', $bRedirect);
        endif;
        
        if (!defined('cb_Login')) : define('cb_Login', true); endif;
        utils::refreshCookies();
        
        $this->doplnVsechnaUserData();
    }

    
    
    
    public function logout($err = '', $bRedirect = true) {
        if (!$bRedirect) : die('Chyba #21 : Neopravneny pristup'); endif;
        
        utils::clearCookiesArray(['username', 'iduser', 'hash']);
        header('Location: /?e=' . $err);
        die();
    }

    private function doplnVsechnaUserData() {
        $q = "SELECT u.id, u.username AS name, u.login, u.heslo, u.email FROM users AS u WHERE u.id = ?";
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
