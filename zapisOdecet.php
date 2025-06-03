<?php
USE Latecka\Utils\utils;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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
    <title><?= $oOdecet->aMeridlo['nazev'] ?>: <?= ($oOdecet->aOdecet["id"]==0) ? __('vložit odečet') : __('oprava odečtu') ?></title>
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
                    <div class="col-2 d-print-none d-none d-md-block"></div>
                    <div class="col ps-0">
                        <h1 class="fs-3 ps-0"><?= $oOdecet->aMeridlo['nazev'] ?></h1>
                    </div>
                </div>
                <div class="row pt-0">
                    <?php include_once 'inc/leveMenu.php'; ?>
                    <div class="col" style="min-height: 85vh">
                        <div id="dataContainer" class="row">
                            <form class="p-1 pt-0 me-1 col-l-10 col-12" id="frmOdecetEdit" action="<?= c_ScriptBaseName ?>.php" method="POST">
                                <input type="hidden" name="idm" value="<?= utils::fixFloat($oOdecet->aMeridlo['id']) ?>">
                                <input type="hidden" name="ido" value="<?= utils::fixFloat($oOdecet->aOdecet['id']) ?>">
                                <fieldset class="row">
                                    <legend class="form-label"><?= ($oOdecet->aOdecet["id"]==0) ? __('Vložit odečet') : __('Oprava odečtu') ?></legend>
                                    <div class="row">
                                    <div class="d-flex flex-wrap">
                                        <!-- První pole -->
                                        <div class="me-3 mb-2" style="max-width: 400px; width: 100%;">
                                            <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                                <label for="odecet" class="me-sm-2 mb-1 mb-sm-0" style="width: 100px; flex-shrink: 0;">
                                                    <?= __('Hodnota') ?>
                                                </label>
                                                <input type="number" placeholder="0.000" step=".001" class="form-control" id="odecet" name="odecet" value="<?= utils::fixFloat(round($oOdecet->aOdecet["odecet"], 3), false) ?>" required>
                                            </div>
                                        </div>

                                        <!-- Druhé pole -->
                                        <div class="me-3 mb-2" style="max-width: 400px; width: 100%;">
                                            <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                                <label for="casodectu" class="me-sm-2 mb-1 mb-sm-0" style="width: 100px; flex-shrink: 0;">
                                                    <?= __('Datum a čas') ?>
                                                </label>
                                                <input type="datetime-local" class="form-control" id="casodectu" value="<?= utils::safeForm($oOdecet->aOdecet["casodectu"]) ?>" name="casodectu" required>
                                            </div>
                                        </div>

                                        <!-- Třetí pole -->
                                        <div class="me-3 mb-2" style="max-width: 400px; width: 100%;">
                                            <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                                <label class="me-sm-2 mb-1 mb-sm-0" style="width: 100px; flex-shrink: 0;"></label>
                                                <div class="form-check m-0 pt-sm-1">
                                                    <input type="checkbox" class="form-check-input me-2" id="zacatekobdobi" value="1" name="zacatekobdobi" <?= ($oOdecet->aOdecet["zacatekobdobi"] == 1) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="zacatekobdobi"><?= __('Jedná se o začátek měřeného období?') ?></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Čtvrté pole přes celou šířku -->
                                    <div class="mb-2">
                                        <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                            <label for="poznamka" class="me-sm-2 mb-1 mb-sm-0" style="width: 100px; flex-shrink: 0;"><?= __('Poznámka') ?></label>
                                            <input type="text" class="form-control" id="poznamka" value="<?= utils::safeForm($oOdecet->aOdecet["poznamka"]) ?>" name="poznamka">
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
