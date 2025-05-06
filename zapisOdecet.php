<?php
USE Latecka\Utils\utils;

require_once 'autoload.php';

$oUser = new user();
$oZarizeni = new zarizeni();
$oOdecet = new zapisOdecet();
?><!DOCTYPE html>
<html lang="cs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl; ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- https://icons.getbootstrap.com/ -->
    <link href="<?= c_MainUrl; ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title><?= __('Nový odpočet') ?></title>
    <script src="<?= c_MainUrl; ?>inc/jquery-3.6.4.min.js"></script>
    <script src="<?= c_MainUrl; ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl; ?>inc/home.js?ch=<?= md5_file('inc/home.js') ?>"></script>
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
                <div class="row">
                    <div class="col-2 d-print-none"></div>
                    <div class="col ps-0">
                        <h1 class="fs-3 ps-0"><?= $oZarizeni->zarizeni['nazev'] ?></h1>
                    </div>
                </div>
                <div class="row pt-0">
                    <?php include_once 'inc/leveMenu.php'; ?>
                    <div class="col" style="min-height: 85vh">
                        <div id="dataContainer" class="row">
                            <form class="p-1 pt-0 me-1 col-l-10 col-12" id="frmZamestnanecEdit" action="zamestnanecEdit.php" method="POST" novalidate>
                                <input type="hidden" name="idz" value="<?= $oZarizeni->zarizeni['id'] ?>">
                                <input type="hidden" name="ulozZamestnance" value="1">
                                <fieldset class="row">
                                    <legend class="form-label"><?= __('Vložit odpočet') ?></legend>

                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" for="odecet" id="label_odecet" style="font-size: .875rem;"><?= __('Hodnota') ?></label>
                                            <input type="text" data-kontrolaZmeny name="odecet" id="odecet" value="<?= utils::fixFloat($oOdecet) ?>" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1">
                                            <label class="input-group-text col-5" id="label_platnost_heslo_sukl_do" for="platnost_heslo_sukl_do" style="font-size: .875rem;">Heslo SUKL platné do</label>
                                            <input type="datetime-locale" class="form-control form-control-sm" id="platnost_heslo_sukl_do" name="platnost_heslo_sukl_do" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]["platnost_heslo_sukl_do"]) ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-1" for="poznamka" id="label_poznamka" style="font-size: .875rem; min-width:8.5vw;">Poznámka</label>
                                            <input type="text" data-kontrolaZmeny name="poznamka" id="poznamka" value="<?= utils::safeForm($oZam->aZamestnanci[$oZam->idzamestnance]['poznamka']) ?>" class="form-control">
                                        </div>
                                    </div>
                                                                      
                                </fieldset>
                                <input type="submit" name="ulozit" value="Uložit" class="btn btn-primary">
                            </form>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <?php include_once 'inc/modalInfo.php'; ?>
        <script src="<?= c_MainUrl; ?>inc/validation-form.js"></script>
    </body>
</html>
