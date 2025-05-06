<div class="col-2 d-print-none" id="leve-menu">
    <div class="row pe-2">
        <h6>Nadpis</h6>
        <ul class="nav flex-column mb-1">
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'zapisOdecet') ? 'active' : ''?>" href="<?= c_MainUrl; ?>vlozitOdecet.php?idz=1"><?= __('Nový odečet') ?></a></li>
        </ul>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'nastaveniZarizeni') ? 'active' : ''?>" href="<?= c_MainUrl; ?>nastaveniZarizeni.php?idz=1"><?= __('Nastavení') ?></a></li>
            <li class="nav-item"><a class="nav-link <?= (c_ScriptBaseName == 'logout') ? 'active' : ''?>" href="<?= c_MainUrl; ?>logout.php"><?= __('Odhlásit') ?></a></li>
        </ul>
    </div>

    <div class="text-center"></div>
</div>
