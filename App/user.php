<?php
set_time_limit(600);

use Config\config;
use Latecka\Utils\utils;

use Latecka\Utils\request;
use Latecka\Utils\db;


require_once 'autoload.php';
require_once 'vendor/autoload.php';

new config();


class user {

    const SaltMd5 = 'hhžžQH|*k855';
    
    public $aErr = [];
    public $user;
    private $cookieTime;

    function __construct() {
        $this->cookieTime = time() + (60*60*6); //6 hodin
        if (request::string('action', 'POST')=='login') :
            $this->login();
        else:
            $this->checkLogin();
        endif;
        
    }
    
    private function login() {
        $this->user["login"] = $login = request::string('login', 'POST');
        $heslo = request::string('password', 'POST');
        
        if ($login == '' || $heslo == '') : $this->setErrorLogin(1); return; endif;
        //debug([$this->user["name"], $heslo, utils::getHash($heslo, self::SaltMd5)]);
        $q = "SELECT u.id, u.username AS name FROM users AS u WHERE u.login = ? AND u.heslo = ?";
        $this->user = db::f($q, $login, utils::getHashHeslo($heslo, self::SaltMd5));
        $this->user["login"] = $login;
        if ($this->user["id"]==0) : $this->setErrorLogin(2); return; endif;
        utils::setCookie('username', $this->user["name"], $this->cookieTime);
        utils::setCookie('iduser', $this->user["id"], $this->cookieTime);
        utils::setCookie('hash', utils::getHash([$this->user["name"], $this->user["id"]], self::SaltMd5), $this->cookieTime);
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
        
        $this->user["name"] = request::string('username', 'COOKIE');
        $this->user["id"] = request::int('iduser', 'COOKIE');

        if ($this->user["name"]=='') : 
            if (c_ScriptBaseName != 'index') :
                header('Location: /');
                die();
            endif;
            define('cb_Login', false);
            return;
        endif;
        

        
        if (utils::getHash([$this->user["name"], $this->user["id"]], self::SaltMd5) != request::string('hash', 'COOKIE')) : 
            $this->logout('badHash', $bRedirect);
        endif;
        
        if (!defined('cb_Login')) : define('cb_Login', true); endif;
        utils::refreshCookies();
        
    }
    
    
    public function logout($err = '', $bRedirect = true) {
        if (!$bRedirect) : die('Chyba #21 : Neopravneny pristup'); endif;
        
        utils::clearCookiesArray(['username', 'iduser', 'hash']);
        header('Location: /?e=' . $err);
        die();
    }
        
}
