<?php
use Latecka\Utils\utils;

require_once 'autoload.php';

$oUser = new user();
$oUser->checkLogin();
?><!DOCTYPE html>
<html lang="cs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl; ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- https://icons.getbootstrap.com/ -->
    <link href="<?= c_MainUrl; ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title><?= __('Home Dashboard') ?></title>
    <script src="<?= c_MainUrl; ?>inc/jquery-3.6.4.min.js"></script>
    <script src="<?= c_MainUrl; ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl; ?>inc/home.js?ch=<?= md5_file('inc/home.js') ?>"></script>
    <link href="inc/css/home.css?ch=<?= md5_file('inc/css/home.css') ?>" rel="stylesheet">
    <style>
        .tile { background-color: #d2f4ea; border:white solid 0.15em; border-radius: 0.5em; padding:1em; }
        .tile:hover { background-color: #a6e9d5; }
        h2 { font-size:1.1em; min-height:3.6em; }
        p { min-height:3em; display: inline-block; }
    </style>
  </head>
    <body class="ps-3 pe-3 m-0 border-0 bg-light">
        <?php include('inc/navbar-top.php') ?>
        <div class="row">
            <div class="col p-3 bg-white m-2 rounded-3">
                <h1><?= __('Statistiky') ?></h1>
                <div class="row rounded-3">
                    <div class="tile col-12 col-sm-2">
                        <h2><?= __('Odečty') ?></h2>
                        <p>Vodoměr teplá</p>
						<div class="text-center"><a href="<?= c_MainUrl; ?>zapisOdecet.php?idz=1" class="btn btn-primary my-2 col-12"><i class="bi-eye me-1"></i> <?= __('Nový odečet') ?></a></div>
						<div class="text-center"><a href="<?= c_MainUrl; ?>nastaveniZarizeni.php?idz=1" class="btn btn-primary my-2 col-12"><i class="bi-eye me-1"></i> <?= __('Nastavení') ?></a></div>
                        <!--<div class="text-center"><button class="btn btn-primary my-2 col-12" id="btnImportDat"><i class="bi-box-arrow-in-down me-1"></i> <?= __('Spusť import dat') ?></button><br> <i>(<?= __('dat je hodně, import může trvat i pár minutek') ?>)</i></div>-->
                    </div>
                    
                </div>
            </div>
        </div>
        
        <?php include_once 'inc/modalInfo.php'; ?>
    </body>
</html>
