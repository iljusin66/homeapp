<?php
USE Latecka\Utils\utils;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'autoload.php';
$oUser = new user();
$oOdecet = new zapisOdecet($oUser->aUser);
$response = $oOdecet->smazOdecet();
if ($response) :
    $response = [
        'status' => 'success',
        'message' => __('Záznam byl úspěšně smazán.'),
    ];
else :
    $response = [
        'status' => 'error',
        'message' => __('Záznam se nepodařilo smazat.'),
        'errors' => $oOdecet->errors,
    ];
endif;
die(json_encode($response));
?>