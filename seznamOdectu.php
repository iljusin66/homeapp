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
                        <h1 class="fs-3 ps-0"><?= $oOdecet->aMeridlo['nazev'] ?> - <?= __('od') ?> <?= utils::getLocaleDate($oOdecet->zacatekObdobiOdectu) ?></h1>
                    </div>
                </div>
                <div class="row pt-0">
                    <?php include_once 'inc/leveMenu.php'; ?>
                    <div class="col" style="min-height: 85vh">
                        <div class="row">
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <strong><?= __('Celková spotřeba') ?>:</strong>
                                            <span><?= round($oOdecet->celkovaSpotreba, 3) ?> <?= $oOdecet->aMeridlo["jednotka"] ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <strong><?= __('Celkové náklady') ?>:</strong>
                                            <span><?= round($oOdecet->celkoveNaklady, 3) ?> <?= c_Mena ?></span>
                                        </div> 
                                        <div class="d-flex justify-content-between">
                                            <strong><?= __('Průměrná spotřeba') ?>:</strong>
                                            <span><?= round($oOdecet->prumernaSpotrebaDen, 3) ?> <?= $oOdecet->aMeridlo["jednotka"] ?> / <?= __('den') ?></span>
                                        </div> 
                                        <!--<div class="d-flex justify-content-between">
                                            <strong><?= __('Průměrná hod. spotřeba') ?>:</strong>
                                            <span><?= round($oOdecet->prumernaSpotrebaHodina, 3) ?> <?= $oOdecet->aMeridlo["jednotka"] ?></span>
                                        </div>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="dataContainer" class="row">
                                <div class="col-12 col-md-6 col-lg-4 mb-3 odecty">
                                    <a href="<?= c_MainUrl; ?>zapisOdecet.php?idm=<?= $aOdecet["idmeridla"] ?>&<?= time() ?>"
                                    class="card text-decoration-none text-body mb-2 bg-lightgreen text-center d-flex align-items-center justify-content-center"
                                    style="height: 9.8rem;">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-plus-circle text-success" style="font-size: 5rem; font-weight: bold;"></i>
                                            <div class="mt-0 text-success" style="position:relative;top:-0.5rem"><?= __('Přidat nový odečet') ?></div>
                                        </div>
                                    </a>
                                </div>

                            <?php
                            //debug($oOdecet->aOdecty);
                            foreach ($oOdecet->aOdecty as $aOdecet) : ?>
                                <div class="col-12 col-md-6 col-lg-4 mb-3 odecty">
                                    <?php
                                    //Jen group editor nebo vyssi muze editovat
                                    if (in_array($oUser->aUser["meridlaRole"][$aOdecet["idmeridla"]], ca_RoleGroup["writer"])) : ?>
                                    <a href="<?= c_MainUrl; ?>zapisOdecet.php?idm=<?= $aOdecet["idmeridla"] ?>&ido=<?= $aOdecet["idodectu"] ?>&<?= time() ?>" class="card text-decoration-none text-body bg-light card-hover mb-2">
                                    <?php endif; ?>
                                        <div class="card-body p-2">
                                            
                                            <!-- Datum nahoře -->
                                            <div class="card-header-custom d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><?= utils::getLocaleDateTime($aOdecet["casodectu"]) ?></h6>
                                            <small class="text-muted">
                                                <?php
                                                if (utils::fixInt($aOdecet["rozdilDnu"]) > 0) {
                                                    echo __('Dnů') . ': +' . utils::fixInt($aOdecet["rozdilDnu"]);
                                                } elseif (utils::fixInt($aOdecet["rozdilHodin"]) > 0) {
                                                    echo __('Hodin') . ': +' . utils::fixInt($aOdecet["rozdilHodin"]);
                                                }
                                                ?>
                                            </small>
                                            </div>

                                            <!-- Dva sloupce vedle sebe -->
                                            <div class="row small">
                                            
                                                <!-- Levý sloupec -->
                                                <div class="col-6">
                                                    <div class="d-flex justify-content-between">
                                                    <span class="text-muted"><?= __('Odečet') ?>:</span>
                                                    <span><?= round(utils::fixFloat($aOdecet["odecet"]), 3) ?> <?= $aOdecet["jednotka"] ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                    <span class="text-muted"><?= __('Zadal') ?>:</span>
                                                    <span><?= $aOdecet["userZadal"] ?></span>
                                                    </div>
                                                    <?php if ($aOdecet["userOpravil"] != '') : ?>
                                                    <div class="d-flex justify-content-between">
                                                    <span class="text-muted"><?= __('Opravil') ?>:</span>
                                                    <span><?= $aOdecet["userOpravil"] ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Pravý sloupec -->
                                                <div class="col-6">
                                                    <div class="d-flex justify-content-between">
                                                    <span class="text-muted"><?= __('Spotřeba') ?></span>
                                                    <span><?= round($aOdecet["spotreba"], 3) ?> <?= $aOdecet["jednotka"] ?></span>
                                                    </div>
                                                    <?php if ($aOdecet["naklady"] > 0) : ?>
                                                    <div class="d-flex justify-content-between">
                                                    <span class="text-muted"><?= __('Náklady') ?></span>
                                                    <span><?= round($aOdecet["naklady"], 3) ?> <?= c_Mena ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="d-flex justify-content-between">
                                                    <span class="text-muted"><?= __('Denní &oslash;') ?></span>
                                                    <span><?= round($aOdecet["prumernaSpotrebaDen"], 3) ?> <?= $aOdecet["jednotka"] ?></span>
                                                    </div>
                                                    <div class="text-end">
                                                    <?php if ($aOdecet["rozdilSpotreby"] < 0) : ?>
                                                        <span class="text-success">
                                                            <i class="bi bi-arrow-down"> </i><?= round($aOdecet["rozdilSpotreby"], 3) ?>
                                                        </span>
                                                    <?php elseif ($aOdecet["rozdilSpotreby"] > 0) : ?>
                                                        <span class="text-danger">
                                                            <i class="bi bi-arrow-up"> </i><?= round($aOdecet["rozdilSpotreby"], 3) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <i class="text-muted"><?= ($aOdecet["poznamka"] == '') ? '&nbsp;' : $aOdecet["poznamka"] ?></i>
                                                </div>
                                            </div>

                                        </div>
                                        <?php
                                    //Jen group editor nebo vyssi muze editovat
                                    if (in_array($oUser->aUser["meridlaRole"][$aOdecet["idmeridla"]], ca_RoleGroup["writer"])) : ?>
                                    </a>
                                    <?php endif; ?>
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
