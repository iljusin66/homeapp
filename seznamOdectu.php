<?php
USE Latecka\Utils\utils;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'autoload.php';

$oUser = new user();
$oOdecet = new odecet($oUser->aUser);
$oOdecet->nactiSeznamOdectu();
?><!DOCTYPE html>
<html lang="cs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl; ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- https://icons.getbootstrap.com/ -->
    <link href="<?= c_MainUrl; ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title><?= c_AppName . ' / ' . $oOdecet->aMeridlo['nazev'] ?>: <?= ($oOdecet->aOdecty["id"]==0) ? __('vložit odečet') : __('oprava odečtu') ?></title>
    <script src="<?= c_MainUrl; ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl; ?>inc/jquery-3.6.4.min.js"></script>
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
                        <h1 class="fs-3 ps-0"><?= $oOdecet->aMeridlo['nazev'] ?> - <?= __('rok') ?> <?= $oOdecet->rokOdectu ?></h1>
                    </div>
                </div>
                <div class="row pt-0">
                    <?php include_once 'inc/leveMenu.php'; ?>
                    <div class="col" style="min-height: 85vh">
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <p class="card-text">
                                            <strong><?= __('Celková spotřeba') ?></strong>: <?= round($oOdecet->celkovaSpotreba, 3) ?> <?= $oOdecet->aMeridlo["jednotka"] ?><br> 
                                            <strong><?= __('Celkové náklady') ?></strong>: <?= round($oOdecet->celkoveNaklady, 3) ?> <?= c_Mena ?><br>
                                            <strong><?= __('Průměrná denní spotřeba') ?></strong>: <?= round($oOdecet->prumernaSpotrebaDen, 3) ?> <?= $oOdecet->aMeridlo["jednotka"] ?><br>
                                            <strong><?= __('Průměrná hodinová spotřeba') ?></strong>: <?= round($oOdecet->prumernaSpotrebaHodina, 3) ?> <?= $oOdecet->aMeridlo["jednotka"] ?>
                                        </p> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="dataContainer" class="row">
                            <?php
                            //debug($oOdecet->aOdecty);
                            foreach ($oOdecet->aOdecty as $aOdecet) : ?>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= utils::getLocaleDateTime($aOdecet["casodectu"]) ?></h6>
                                            <div class="card-text mb-1">
                                                <?= __('Zadal') ?>: <?= $aOdecet["userZadal"] ?><br>
                                                <?= __('Opravil') .": " . (($aOdecet["userOpravil"]=='') ? '---' : $aOdecet["userOpravil"]) ?><br>
                                                <i><?= (($aOdecet["poznamka"]=='') ? '&nbsp;' : $aOdecet["poznamka"]) ?></i>
                                            </div>
                                            <div class="card-text mb-1">
                                            <?= __('Odečet') ?>: <?= round(utils::fixFloat($aOdecet["odecet"]), 3) ?> <?= $aOdecet["jednotka"] ?><br>
                                            <?= __('Spotřeba') ?>: <?= round($aOdecet["spotreba"], 3) ?> <?= $aOdecet["jednotka"] ?><br>
                                            <?= __('Náklady') ?>: <?= round($aOdecet["naklady"], 3) ?> <?= c_Mena ?><br>
                                            <?= __('Prům. denní spotřeba') ?>: <?= round($aOdecet["prumernaSpotrebaDen"], 3) ?> <?= $aOdecet["jednotka"] ?><br>
                                            <!-- <?= __('Prům. hodinová spotřeba') ?> : <?= round($aOdecet["prumernaSpotrebaHodina"], 3) ?> <?= $aOdecet["jednotka"] ?><br> -->
                                            </div>
                                            <?php
                                            //Jen group writer muze zapisovat
                                            if (in_array($oUser->aUser["meridlaRole"][$aOdecet["idmeridla"]], ca_RoleGroup["writer"])) : ?>
                                                <a href="<?= c_MainUrl; ?>zapisOdecet.php?idm=<?= $aOdecet["idmeridla"] ?>&ido=<?= $aOdecet["idodectu"] ?>&<?= time() ?>" class="btn btn-sm btn-primary"><i class="bi-pencil-square me-1"></i> <?= __('Upravit') ?></a>
                                            <?php
                                            endif;
                                            
                                            //Jen group editor muze mazat
                                            if (in_array($oUser->aUser["meridlaRole"][$aOdecet["idmeridla"]], ca_RoleGroup["editor"])) : ?>
                                            <!-- Button trigger modal -->
                                            <a href="#" class="btn-sm btn-danger smazatOdecet"
                                                data-bs-target="#modalConfirmDelete"
                                                data-ido="<?= $aOdecet["id"] ?>"
                                                data-idm="<?= $aOdecet["idmeridla"] ?>">
                                                <i class="bi-trash me-1"></i> <?= __('Smazat') ?>
                                            </a>
                                            <?php
                                            endif;
                                            ?>
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
        <?php include_once 'inc/modalConfirmDelete.php'; ?>
        <script src="<?= c_MainUrl; ?>inc/validation-form.js"></script>
    </body>
</html>
