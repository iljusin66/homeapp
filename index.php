<?php
require_once 'Config/config.php';
require_once 'App/login.php';
$oLogin = new login();
$oLogin->checkLogin();

if (cb_Login===true) :
    require 'dashboard.php';
else:
    require 'login.php';
endif;