<?php
$fileRoot = $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $fileRoot. 'Config/config.php';
new \Config\config();
require_once $fileRoot. 'App/Utils/utils.php';
new \App\Utils\utils();
require_once $fileRoot. 'App/Utils/helper.php';
require_once $fileRoot. 'App/Utils/db.php';
require_once $fileRoot. 'App/Utils/request.php';

