<div class="col-2 d-none d-md-block" id="sidebar"> <!-- Skryté na mobilu, viditelné na širších obrazovkách -->
    <!-- <h6>Levemenu</h6> -->
        <?php if ( $oOdecet->aZarizeni["id"] > 0) : ?>
        <ul class="nav flex-column mb-1">
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'zapisOdecet') ? 'active' : ''?>" href="<?= c_MainUrl; ?>zapisOdecet.php?idz=<?= $oOdecet->aZarizeni["id"]?>"><?= __('Nový odečet') ?></a></li>
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'zapisZarizeni') ? 'active' : ''?>" href="<?= c_MainUrl; ?>zapisZarizeni.php?idz=<?= $oOdecet->aZarizeni["id"]?>"><?= __('Nastavení') ?></a></li>
        </ul>
        <?php endif; ?>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'logout') ? 'active' : ''?>" href="<?= c_MainUrl; ?>logout.php"><?= __('Odhlásit') ?></a></li>
        </ul>
</div>

<!-- Offcanvas menu pro mobilní zobrazení -->
<div class="offcanvas offcanvas-start d-md-none" id="leve-menu">
    <div class="offcanvas-header">
        <!-- <h5 class="offcanvas-title">Levemenu</h5> -->
        <button title="Close" type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <?php if ( $oOdecet->aZarizeni["id"] > 0) : ?>
        <ul class="nav flex-column mb-1">
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'zapisOdecet') ? 'active' : ''?>" href="<?= c_MainUrl; ?>zapisOdecet.php?idz=<?= $oOdecet->aZarizeni["id"]?>"><?= __('Nový odečet') ?></a></li>
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'zapisZarizeni') ? 'active' : ''?>" href="<?= c_MainUrl; ?>zapisZarizeni.php?idz=<?= $oOdecet->aZarizeni["id"]?>"><?= __('Nastavení') ?></a></li>
        </ul>
        <?php endif; ?>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'logout') ? 'active' : ''?>" href="<?= c_MainUrl; ?>logout.php"><?= __('Odhlásit') ?></a></li>
        </ul>
    </div>
</div>
