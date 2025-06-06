<?php
use Latecka\Utils\utils;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'autoload.php';

$oUser = new user();
$oMeridla = new meridla($oUser->aUser);
//$oMeridla->nactiSeznamMeridelUzivatele();
?><!DOCTYPE html>
<html lang="cs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl; ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- https://icons.getbootstrap.com/ -->
    <link href="<?= c_MainUrl; ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title><?= c_AppName . ' / ' . __('Dashboard') ?></title>
    <script src="<?= c_MainUrl; ?>inc/jquery-3.6.4.min.js"></script>
    <script src="<?= c_MainUrl; ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl; ?>inc/home.js?ch=<?= md5_file('inc/home.js') ?>"></script>
    <link href="inc/css/home.css?ch=<?= md5_file('inc/css/home.css') ?>" rel="stylesheet">
    <style>
        .tile { background-color: #d2f4ea; border:white solid 0.15em; border-radius: 0.5em; padding:1em; }
        .tile:hover { background-color: #a6e9d5; }
        h2 { font-size:1.1em; }
        p { min-height:3em; display: inline-block; }
    </style>
  </head>
    <body class="ps-3 pe-3 m-0 border-0 bg-light">
        <?php include('inc/navbar-top.php') ?>
        <div class="row">
            <div class="col p-3 bg-white m-2 rounded-3">
                <h1><?= __('Měřidla') ?></h1>
                <div class="row rounded-3">
                    <?php
                    if (empty($oMeridla->aMeridla)) : ?>
                        <div class="alert alert-danger" role="alert">
                            <?= __('Nemáte přidáno žádné měřidlo') ?>
                        </div> 
                    <?php else :
                    foreach ($oMeridla->aMeridla as $aMeridlo) :
                        if (!is_array($aMeridlo)) continue;
                    ?>
                        <div class="tile col-12 col-sm-2">
                            <h2 class="mb-1"><?= $aMeridlo["nazev"] ?></h2>
                            <div class="text-center"><a href="<?= c_MainUrl; ?>zapisOdecet.php?idm=<?= $aMeridlo["id"] ?>&<?= time() ?>" class="btn btn-primary my-2 col-12"><i class="bi-plus-circle me-1"></i> <?= __('Nový odečet') ?></a></div>
                            <div class="text-center"><a href="<?= c_MainUrl; ?>seznamOdectu.php?idm=<?= $aMeridlo["id"] ?>&<?= time() ?>" class="btn btn-primary my-2 col-12"><i class="bi-list-ul me-1"></i> <?= __('Zadané odečty') ?></a></div>
                            <div class="text-center"><a href="<?= c_MainUrl; ?>zapisMeridlo.php?idm=<?= $aMeridlo["id"] ?>&<?= time() ?>" class="btn btn-primary my-2 col-12"><i class="bi-gear me-1"></i> <?= __('Nastavení') ?></a></div>
                        </div>
                    <?php endforeach;
                    endif;
                    ?>
                </div>
            </div>
        </div>
        
        <?php include_once 'inc/modalInfo.php'; ?>
    </body>
</html>
