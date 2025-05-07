<?php
USE Latecka\Utils\utils;

require_once 'autoload.php';

$oUser = new user();
$oOdecet = new zapisOdecet($oUser->aUser);
?><!DOCTYPE html>
<html lang="cs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl; ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- https://icons.getbootstrap.com/ -->
    <link href="<?= c_MainUrl; ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title>: <?= $oOdecet->aZarizeni['nazev'] ?><?= ($oOdecet->aOdecet["id"]==0) ? __('Vložit odpočet') : __('Oprava odečtu') ?></title>
    <script src="<?= c_MainUrl; ?>inc/jquery-3.6.4.min.js"></script>
    <script src="<?= c_MainUrl; ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl; ?>inc/home.js?ch=<?= md5_file('inc/home.js') ?>"></script>
    <link href="<?= c_MainUrl; ?>inc/css/home.css?ch=<?= md5_file(c_FileRoot.'inc/home.css') ?>" rel="stylesheet">
    <style>

    </style>
  </head>
  <body class="ps-3 pe-3 m-0 border-0 bg-light">
        <?php include('inc/navbar-top.php') ?>
        <div class="row">
            <div class="col p-3 bg-white m-2 rounded-3">
                <div class="row">
                    <div class="col-2 d-print-none"></div>
                    <div class="col ps-0">
                        <h1 class="fs-3 ps-0"><?= $oOdecet->aZarizeni['nazev'] ?></h1>
                    </div>
                </div>
                <div class="row pt-0">
                    <?php include_once 'inc/leveMenu.php'; ?>
                    <div class="col" style="min-height: 85vh">
                        <div id="dataContainer" class="row">
                            <form class="p-1 pt-0 me-1 col-l-10 col-12" id="frmOdecetEdit" action="<?= c_ScriptBaseName ?>.php" method="POST">
                                <input type="hidden" name="idz" value="<?= utils::fixFloat($oOdecet->aZarizeni['id']) ?>">
                                <input type="hidden" name="ido" value="<?= utils::fixFloat($oOdecet->aOdecet['id']) ?>">
                                <fieldset class="row">
                                    <legend class="form-label"><?= ($oOdecet->aOdecet["id"]==0) ? __('Vložit odpočet') : __('Oprava odečtu') ?></legend>

                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-4" for="odecet" id="label_odecet" style="font-size: .875rem;"><?= __('Hodnota') ?></label>
                                            <input type="number" pattern="[0-9]+([.][0-9]+)?" required data-kontrolaZmeny name="odecet" id="odecet" value="<?= utils::fixFloat($oOdecet->aOdecet['odecet'], false) ?>" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="input-group input-group-sm mb-1">
                                            <label class="input-group-text col-5" id="label_casodpoctu" for="casodpoctu" style="font-size: .875rem;"><?= __('Datum a čas') ?></label>
                                            <input type="datetime-local" class="form-control form-control-sm" id="casodpoctu" name="casodpoctu" value="<?= utils::safeForm($oOdecet->aOdecet['casodpoctu']) ?>" required data-kontrolaZmeny>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="input-group input-group-sm mb-1 ">
                                            <label class="input-group-text col-1" for="poznamka" id="label_poznamka" style="font-size: .875rem; min-width:8.5vw;"><?= __('Poznámka') ?></label>
                                            <input type="text" data-kontrolaZmeny name="poznamka" id="poznamka" value="<?= utils::safeForm($oOdecet->aOdecet['poznamka']) ?>" class="form-control">
                                        </div>
                                    </div>
                                                                      
                                </fieldset>
                                <input type="submit" name="ulozit" value="<?= __('Uložit') ?>" class="btn btn-primary">
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
