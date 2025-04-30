<?php

use Latecka\Utils\utils;

require_once 'autoload.php';

$oUser = new user();
$oUser->checkLogin();

if (cb_Login===true) :
    require 'dashboard.php';
else:
    require 'login.php';
endif;