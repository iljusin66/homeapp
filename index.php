<?php
require_once 'Config/config.php';
require_once 'App/user.php';
$oUser = new user();
$oUser->checkLogin();

if (cb_Login===true) :
    require 'dashboard.php';
else:
    require 'login.php';
endif;