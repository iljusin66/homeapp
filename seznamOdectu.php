<?php
USE Latecka\Utils\utils;

require_once 'autoload.php';

$oUser = new user();
$oOdecet = new odecet($oUser->aUser);
$oOdecet->nactiSeznamOdectu();
//debug(5666);
?><!DOCTYPE html>
<html lang="cs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl; ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- https://icons.getbootstrap.com/ -->
    <link href="<?= c_MainUrl; ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title><?= $oOdecet->aZarizeni['nazev'] ?>: <?= ($oOdecet->aOdecty["id"]==0) ? __('vložit odečet') : __('oprava odečtu') ?></title>
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
                        <h1 class="fs-3 ps-0"><?= $oOdecet->aZarizeni['nazev'] ?></h1>
                    </div>
                </div>
                <div class="row pt-0">
                    <?php include_once 'inc/leveMenu.php'; ?>
                    <div class="col" style="min-height: 85vh">
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= __('Průměrná spotřeba') ?></h5>
                                        <p class="card-text"><?= round($oOdecet->spocitejPrumernouSpotrebu(), 3) ?> <?= $oOdecet->aZarizeni["jednotka"] ?></p> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="dataContainer" class="row">
                            <?php foreach ($oOdecet->aOdecty as $aOdecet) : ?>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= utils::getLocaleDateTime($aOdecet["casodpoctu"]) ?></h5>
                                            <p class="card-text"><?= round(utils::fixFloat($aOdecet["odecet"]), 3) ?> <?= $aOdecet["jednotka"] ?></p>
                                            <a href="<?= c_MainUrl; ?>zapisOdecet.php?idz=<?= $oOdecet->aZarizeni["id"] ?>&ido=<?= $aOdecet["id"] ?>" class="btn btn-sm btn-primary"><i class="bi-pencil-square me-1"></i> <?= __('Upravit') ?></a>
                                            <!-- <a href="<?= c_MainUrl; ?>zapisOdecet.php?idz=<?= $oOdecet->aZarizeni["id"] ?>&ido=<?= $aOdecet["id"] ?>&delete=1" class="btn-sm btn-danger"><i class="bi-trash me-1"></i> <?= __('Smazat') ?></a> -->
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php if (empty($oOdecet->aOdecty)) : ?>
                                <div class="alert alert-danger" role="alert">
                                    <?= __('Zatím není zapsán žádný odečet') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <?php include_once 'inc/modalInfo.php'; ?>
        <script src="<?= c_MainUrl; ?>inc/validation-form.js"></script>
    </body>
</html>
