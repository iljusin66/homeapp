<?php
USE Latecka\Utils\utils;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'autoload.php';
$oUser = new user();
$oMeridlo = new meridla($oUser->aUser);

?><!DOCTYPE html>
<html lang="cs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl; ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- https://icons.getbootstrap.com/ -->
    <link href="<?= c_MainUrl; ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title><?= __('Nové zařízení') ?></title>
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
                    <div class="col-2 d-print-none d-none d-md-block"></div>
                    <div class="col ps-0">
                        <h1 class="fs-3 ps-0"><?= $oMeridlo->aMeridlo['nazev'] ?></h1>
                    </div>
                </div>
                <div class="row pt-0">
                    <?php include_once 'inc/leveMenu.php'; ?>
                    <div class="col" style="min-height: 85vh">
                        <div id="dataContainer" class="row">
                            <form class="p-1 pt-0 me-1 col-l-10 col-12" id="frmOdecetEdit" action="<?= c_ScriptBaseName ?>.php" method="POST">
                                <input type="hidden" name="idm" value="<?= utils::fixFloat($oMeridlo->aMeridlo['id']) ?>">
                                <fieldset class="row">
                                    <legend class="form-label"><?= ($oMeridlo->aMeridlo["id"]==0) ? __('Vložit měřidlo') : __('Oprava měřidla') ?></legend>
                                    <div class="row">
                                    <div class="d-flex flex-wrap">
                                        <!-- První pole -->
                                        <div class="me-3 mb-2" style="max-width: 400px; width: 100%;">
                                            <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                                <label for="nazev" class="me-sm-2 mb-1 mb-sm-0" style="width: 150px; flex-shrink: 0;">
                                                    <?= __('Název měřidla') ?>
                                                </label>
                                                <input type="text" class="form-control" id="nazev" name="nazev" value="<?= utils::safeForm($oMeridlo->aMeridlo["nazev"]) ?>" required>
                                            </div>
                                        </div>

                                        <!-- Druhé pole -->
                                        <div class="me-3 mb-2" style="max-width: 400px; width: 100%;">
                                            <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                                <label for="idjednotky" class="me-sm-2 mb-1 mb-sm-0" style="width: 150px; flex-shrink: 0;">
                                                    <?= __('Jednotka měření') ?>
                                                </label>
                                                <select class="form-select" id="idjednotky" name="idjednotky" required>
                                                    <?php if ($oMeridlo->aMeridlo['idjednotky']==0) : ?><option value="0"><?= __('Vyberte měrnou jednotku') ?></option><?php endif; ?>
                                                    <?php foreach ($oMeridlo->aJednotkyMeridel as $id => $jednotka): ?>
                                                        <option value="<?= utils::fixInt($id) ?>" <?= ($oMeridlo->aMeridlo['idjednotky'] == $id) ? 'selected' : '' ?>>
                                                            <?= utils::safeForm($jednotka['jednotka']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                
                                            </div>
                                        </div>

                                        <!-- Třetí pole -->
                                        <div class="me-3 mb-2" style="max-width: 400px; width: 100%;">
                                            
                                        </div>
                                    </div>

                                    <!-- Čtvrté pole přes celou šířku -->
                                    <div class="mb-2">
                                        <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                            <label for="poznamka" class="me-sm-2 mb-1 mb-sm-0" style="width: 100px; flex-shrink: 0;"><?= __('Poznámka') ?></label>
                                            <input type="text" class="form-control" id="poznamka" value="<?= utils::safeForm($oMeridlo->aMeridlo["poznamka"]) ?>" name="poznamka">
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
