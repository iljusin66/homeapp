<?php
USE Latecka\Utils\utils;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'autoload.php';
$oUser = new user();
$oCenik = new zapisCenik($oUser->aUser);
?><!DOCTYPE html>
<html lang="cs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl; ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- https://icons.getbootstrap.com/ -->
    <link href="<?= c_MainUrl; ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title><?= $oCenik->aMeridlo['nazev'] ?>: <?= ($oCenik->aCenik["id"]==0) ? __('Nový ceník') : __('Úprava ceníku') ?></title>
    <script src="<?= c_MainUrl; ?>inc/jquery-3.6.4.min.js"></script>
    <script src="<?= c_MainUrl; ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl; ?>inc/home.js?ch=<?= md5_file('inc/home.js') ?>"></script>
    <link href="<?= c_MainUrl; ?>inc/css/home.css?ch=<?= md5_file(c_FileRoot.'inc/home.css') ?>" rel="stylesheet">
  </head>
  <body class="ps-3 pe-3 m-0 border-0 bg-light">
    
        <?php include('inc/navbar-top.php') ?>
        <div class="row">
            <div class="col p-3 bg-white m-2 rounded-3">
                <div class="row">
                    <div class="col-2 d-print-none d-none d-md-block"></div>
                    <div class="col ps-0">
                        <h1 class="fs-3 ps-0"><?= $oCenik->aMeridlo['nazev'] ?></h1>
                    </div>
                </div>
                <div class="row pt-0">
                    <?php include_once 'inc/leveMenu.php'; ?>
                    <div class="col" style="min-height: 85vh">
                        <div id="dataContainer" class="row">
                            <form class="p-1 pt-0 me-1 col-l-10 col-12" id="frmCenikEdit" action="<?= c_ScriptBaseName ?>.php" method="POST">
                                <input type="hidden" name="idm" value="<?= utils::fixInt($oCenik->aMeridlo['id']) ?>">
                                <input type="hidden" name="idc" value="<?= utils::fixInt($oCenik->aCenik['id']) ?>">
                                <fieldset class="row">
                                    <legend class="form-label"><?= ($oCenik->aCenik["id"]==0) ? __('Vytvořit nový ceník') : __('Upravit ceník') ?></legend>
                                    <?php if (!empty($oCenik->errors)) : ?>
                                        <div class="alert alert-danger">
                                            <?= implode('<br>', array_map('htmlspecialchars', $oCenik->errors)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="row">
                                    <div class="d-flex flex-wrap">
                                        <!-- Dodavatel -->
                                        <div class="me-3 mb-2" style="max-width: 400px; width: 100%;">
                                            <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                                <label for="dodavatel" class="me-sm-2 mb-1 mb-sm-0" style="width: 120px; flex-shrink: 0;">
                                                    <?= __('Dodavatel') ?>
                                                </label>
                                                <input type="text" class="form-control" id="dodavatel" name="dodavatel" value="<?= utils::safeForm($oCenik->aCenik["dodavatel"]) ?>" required>
                                            </div>
                                        </div>

                                        <!-- Cena za jednotku -->
                                        <div class="me-3 mb-2" style="max-width: 400px; width: 100%;">
                                            <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                                <label for="cenazajednotku" class="me-sm-2 mb-1 mb-sm-0" style="width: 120px; flex-shrink: 0;">
                                                    <?= __('Cena za jednotku') ?>
                                                </label>
                                                <input type="number" placeholder="0.00000" step=".00001" class="form-control" id="cenazajednotku" name="cenazajednotku" value="<?= utils::fixFloat(round($oCenik->aCenik["cenazajednotku"], 5), false) ?>" required>
                                            </div>
                                        </div>

                                        <!-- Platný od - do -->
                                        <div class="me-3 mb-2 d-flex gap-3" style="max-width: 700px; width: 100%;">
                                            <div class="d-flex flex-column flex-sm-row align-items-sm-center" style="flex: 1;">
                                                <label for="platnyod" class="me-sm-2 mb-1 mb-sm-0" style="width: 100px; flex-shrink: 0;">
                                                    <?= __('Od') ?>
                                                </label>
                                                <input type="date" class="form-control" id="platnyod" name="platnyod" value="<?= (empty($oCenik->aCenik["platnyod"])) ? date('Y-m-d') : substr($oCenik->aCenik["platnyod"], 0, 10) ?>" required>
                                            </div>
                                            <div class="d-flex flex-column flex-sm-row align-items-sm-center" style="flex: 1;">
                                                <label for="platnydo" class="me-sm-2 mb-1 mb-sm-0" style="width: 100px; flex-shrink: 0;">
                                                    <?= __('Do') ?>
                                                </label>
                                                <input type="date" class="form-control" id="platnydo" name="platnydo" value="<?= (!empty($oCenik->aCenik["platnydo"])) ? substr($oCenik->aCenik["platnydo"], 0, 10) : '' ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Poznámka přes celou šířku -->
                                    <div class="mb-2">
                                        <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                            <label for="poznamka" class="me-sm-2 mb-1 mb-sm-0" style="width: 120px; flex-shrink: 0;"><?= __('Poznámka') ?></label>
                                            <input type="text" class="form-control" id="poznamka" value="<?= utils::safeForm($oCenik->aCenik["poznamka"]) ?>" name="poznamka">
                                    </div>
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <a href="zapisMeridlo.php?idm=<?= $oCenik->aMeridlo['id'] ?>" class="btn btn-sm btn-secondary me-2"><?= __('Zpět') ?></a>
                                    <input type="submit" name="ulozit" value="<?= __('Uložit') ?>" class="btn btn-success px-5">
                                </fieldset>
                            </form>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </body>
</html>
