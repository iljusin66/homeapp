<?php
$menuMeridlaItems = [
    [
        'script' => 'zapisOdecet',
        'url' => c_MainUrl . 'zapisOdecet.php?idm=' . $oOdecet->aMeridla["id"] . '&t=' . time(),
        'label' => __('Nový odečet')
    ],
    [
        'script' => 'seznamOdectu',
        'url' => c_MainUrl . 'seznamOdectu.php?idm=' . $oOdecet->aMeridla["id"] . '&t=' . time(),
        'label' => __('Zadané odečty')
    ],
    [
        'script' => 'zapisMeridlo',
        'url' => c_MainUrl . 'zapisMeridlo.php?idm=' . $oOdecet->aMeridla["id"] . '&t=' . time(),
        'label' => __('Nastavení')
    ],

];
$liMeridla = "";
foreach ($menuMeridlaItems as $item) {
    $liMeridla .= '<li class="nav-item"><a class="nav-link '. ((c_ScriptBaseName == $item['script']) ? 'active' : '') .'" href="'.$item['url'].'">'.$item['label'].'</a></li>';
}

?>
<div class="col-2 d-none d-md-block" id="sidebar"> <!-- Skryté na mobilu, viditelné na širších obrazovkách -->
    <?php if ( $oOdecet->aMeridla["id"] > 0) : ?>
        <h6><?= $oOdecet->aMeridla["nazev"] ?></h6>
        <ul class="nav flex-column mb-1">
            <?= $liMeridla ?>
        </ul>
        <?php endif; ?>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'logout') ? 'active' : ''?>" href="<?= c_MainUrl; ?>logout.php"><?= __('Odhlásit') ?></a></li>
        </ul>
</div>

<!-- Offcanvas menu pro mobilní zobrazení -->
<div class="offcanvas offcanvas-start d-md-none" id="leve-menu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><?= $oOdecet->aMeridla["nazev"] ?></h5>
        <button title="Close" type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <?php if ( $oOdecet->aMeridla["id"] > 0) : ?>
        <ul class="nav flex-column mb-1">
            <?= $liMeridla ?>
        </ul>
        <?php endif; ?>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'logout') ? 'active' : ''?>" href="<?= c_MainUrl; ?>logout.php"><?= __('Odhlásit') ?></a></li>
        </ul>
    </div>
</div>
